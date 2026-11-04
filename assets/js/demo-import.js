/**
 * Demo content screen: runs the import and the image copy repair one share at
 * a time and reports the progress in a status line, so a host with a short
 * time limit finishes both. The image copies that could not be written are
 * listed from each reply. Without this script each form posts and the screen
 * offers to carry on.
 */
( function () {
	const settings = window.prairieAshDemo;

	if ( ! settings ) {
		return;
	}

	const failureBox = document.querySelector( '[data-prairie-ash-image-failures]' );
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

	const share = async ( action, nonce, carryOn ) => {
		const body = new FormData();
		body.append( 'action', action );
		body.append( '_ajax_nonce', nonce );
		body.append( 'continue', carryOn ? '1' : '0' );

		const response = await fetch( settings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body,
		} );

		return response.json();
	};

	/*
	 * A share that dies on the server, at a time or memory limit, answers with
	 * no JSON. The server notes the copy it was writing, so the next share
	 * reports that copy and moves on: the runner asks again up to three times
	 * in a row before it gives up.
	 */
	const run = ( form, status, job ) => {
		const button = form.querySelector( 'button[type="submit"]' );

		form.addEventListener( 'submit', async ( event ) => {
			event.preventDefault();

			let carryOn = form.elements.continue.value === '1';
			let misses = 0;
			button.disabled = true;
			job.start();

			for ( ;; ) {
				let result;

				try {
					result = await share( job.action, job.nonce, carryOn );
					misses = 0;
				} catch ( error ) {
					misses++;
					if ( misses < 3 ) {
						carryOn = true;
						continue;
					}
					status.textContent = job.network;
					break;
				}

				if ( ! result.success ) {
					status.textContent = format( job.failed, result.data.message );
					break;
				}

				showFailures( result.data.failures );

				if ( result.data.done ) {
					form.elements.continue.value = '0';
					job.finish( result.data );
					break;
				}

				carryOn = true;
				form.elements.continue.value = '1';
				status.textContent = job.progress( result.data );
			}

			button.disabled = false;
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
				return format( settings.text.running, settings.steps[ data.step ] );
			},
			finish: ( data ) => {
				importStatus.textContent = data.failures.length
					? format( settings.text.doneFailures, data.failures.length )
					: settings.text.done;
				if ( view ) {
					view.hidden = false;
				}
			},
		} );
	}

	const repairForm = document.querySelector( '[data-prairie-ash-repair]' );
	const repairStatus = document.querySelector( '[data-prairie-ash-repair-status]' );
	const summary = document.querySelector( '[data-prairie-ash-repair-summary]' );

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
				repairStatus.textContent = data.failures.length
					? format( settings.text.repairFailed, data.failures.length )
					: settings.text.repaired;
				if ( summary && ! data.failures.length ) {
					summary.textContent = settings.text.repaired;
				}
			},
		} );
	}
}() );
