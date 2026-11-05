/**
 * Demo content screen: runs the import, the image copy repair and the count
 * of missing copies one share at a time and reports the progress in a status
 * line, so a host with a short time limit finishes each. The image copies
 * that could not be written are listed from each reply. Without this script
 * each form posts and the screen offers to carry on.
 */
( function () {
	const settings = window.prairieAshDemo;

	if ( ! settings ) {
		return;
	}

	const failureBox = document.querySelector( '[data-prairie-ash-image-failures]' );
	const summary = document.querySelector( '[data-prairie-ash-repair-summary]' );
	const format = ( text, ...values ) => {
		let index = 0;
		return text.replace( /%(\d\$)?[sd]/g, ( match, position ) => {
			const value = position ? values[ parseInt( position, 10 ) - 1 ] : values[ index++ ];
			return String( value );
		} );
	};

	const showFailures = ( failures ) => {
		if ( ! failureBox || ! Array.isArray( failures ) ) {
			return;
		}

		const count = failureBox.querySelector( '[data-prairie-ash-image-failures-count]' );
		const list = failureBox.querySelector( '[data-prairie-ash-image-failures-list]' );

		count.textContent = format( settings.text.failureCount, failures.length );
		list.replaceChildren(
			...failures.map( ( line ) => {
				const item = document.createElement( 'li' );
				item.textContent = line;
				return item;
			} )
		);
		failureBox.hidden = failures.length === 0;
	};

	/*
	 * An answer the screen has to stop on rather than ask again: admin-ajax
	 * answers -1 when the nonce has expired and 0 when nobody is signed in,
	 * both of which are valid JSON but not a reply.
	 */
	class Stop extends Error {}

	const share = async ( action, nonce, carryOn, form ) => {
		const body = new FormData();
		body.append( 'action', action );
		body.append( '_ajax_nonce', nonce );
		body.append( 'continue', carryOn ? '1' : '0' );

		if ( form.elements.confirm && form.elements.confirm.checked ) {
			body.append( 'confirm', '1' );
		}

		const response = await fetch( settings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body,
		} );
		const text = await response.text();
		let reply = null;

		try {
			reply = JSON.parse( text );
		} catch ( error ) {
			reply = null;
		}

		if ( reply && typeof reply === 'object' && typeof reply.success === 'boolean' ) {
			return reply;
		}

		if ( '-1' === text.trim() || '0' === text.trim() || 403 === response.status ) {
			throw new Stop( settings.text.expired );
		}

		// No reply at all: the share died at a limit, and the next one reports it.
		throw new Error( response.status ? String( response.status ) : 'network' );
	};

	/*
	 * A share that dies on the server, at a time or memory limit, answers with
	 * no JSON. The server records the copy it was writing, and the next share
	 * answers at once with that record before it writes anything else, so the
	 * runner asks again up to three times in a row before it gives up.
	 */
	const run = ( form, status, job ) => {
		const button = form.querySelector( 'button[type="submit"]' );

		form.addEventListener( 'submit', async ( event ) => {
			event.preventDefault();

			let carryOn = form.elements.continue.value === '1';
			let misses = 0;
			button.disabled = true;
			job.start();

			try {
				for ( ;; ) {
					let result;

					try {
						result = await share( job.action, job.nonce, carryOn, form );
						misses = 0;
					} catch ( error ) {
						if ( error instanceof Stop ) {
							status.textContent = error.message;
							break;
						}
						misses++;
						if ( misses < 3 ) {
							carryOn = true;
							continue;
						}
						status.textContent = job.network;
						break;
					}

					const data = result.data && typeof result.data === 'object' ? result.data : {};

					if ( ! result.success ) {
						status.textContent = format( job.failed, data.message || settings.text.unknown );
						break;
					}

					showFailures( data.failures );

					if ( summary && data.summary ) {
						summary.textContent = data.summary;
					}

					if ( data.done ) {
						form.elements.continue.value = '0';
						job.finish( data );
						break;
					}

					carryOn = true;
					form.elements.continue.value = '1';
					status.textContent = job.progress( data );
				}
			} catch ( error ) {
				status.textContent = format( job.failed, settings.text.unknown );
			} finally {
				button.disabled = false;
			}
		} );
	};

	const importForm = document.querySelector( '[data-prairie-ash-demo]' );
	const importStatus = document.querySelector( '[data-prairie-ash-demo-status]' );
	const view = document.querySelector( '[data-prairie-ash-demo-view]' );

	if ( importForm && importStatus ) {
		run( importForm, importStatus, {
			action: 'prairie_ash_demo_import',
			nonce: settings.nonce,
			failed: settings.text.failed,
			network: settings.text.network,
			start: () => {
				if ( view ) {
					view.hidden = true;
				}
				importStatus.textContent = format( settings.text.running, Object.values( settings.steps )[ 0 ] );
			},
			progress: ( data ) => {
				if ( 'media' === data.step || 'images' === data.step ) {
					return format( settings.text[ data.step ], data.cursor, data.media );
				}
				return format( settings.text.running, settings.steps[ data.step ] || '' );
			},
			finish: ( data ) => {
				importStatus.textContent = data.importFailures
					? format( settings.text.doneFailures, data.importFailures )
					: settings.text.done;
				if ( view ) {
					view.hidden = false;
				}
			},
		} );
	}

	const repairForm = document.querySelector( '[data-prairie-ash-repair]' );
	const repairStatus = document.querySelector( '[data-prairie-ash-repair-status]' );

	if ( repairForm && repairStatus ) {
		run( repairForm, repairStatus, {
			action: 'prairie_ash_image_repair',
			nonce: settings.repairNonce,
			failed: settings.text.repairStopped,
			network: settings.text.repairNetwork,
			start: () => {
				repairStatus.textContent = format( settings.text.repairing, 0, '...' );
			},
			progress: ( data ) => format( settings.text.repairing, data.checked, data.total ),
			finish: ( data ) => {
				repairStatus.textContent = Array.isArray( data.failures ) && data.failures.length
					? format( settings.text.repairFailed, data.failures.length )
					: settings.text.repaired;
			},
		} );
	}

	const countForm = document.querySelector( '[data-prairie-ash-count]' );
	const countStatus = document.querySelector( '[data-prairie-ash-count-status]' );

	if ( countForm && countStatus ) {
		run( countForm, countStatus, {
			action: 'prairie_ash_image_count',
			nonce: settings.countNonce,
			failed: settings.text.countStopped,
			network: settings.text.countNetwork,
			start: () => {
				countStatus.textContent = format( settings.text.counting, 0, '...' );
			},
			progress: ( data ) => format( settings.text.counting, data.checked, data.total ),
			finish: () => {
				countStatus.textContent = settings.text.counted;
			},
		} );
	}
}() );
