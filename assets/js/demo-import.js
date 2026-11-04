/**
 * Demo content screen: runs the import one share at a time and reports the
 * step in the status line, so a host with a short time limit finishes it.
 * Without this script the form posts and the screen offers to carry on.
 */
( function () {
	const settings = window.prairieAshDemo;
	const form = document.querySelector( '[data-prairie-ash-demo]' );
	const status = document.querySelector( '[data-prairie-ash-demo-status]' );
	const view = document.querySelector( '[data-prairie-ash-demo-view]' );

	if ( ! settings || ! form || ! status ) {
		return;
	}

	const button = form.querySelector( 'button[type="submit"]' );
	const format = ( text, ...values ) => {
		let index = 0;
		return text.replace( /%(\d\$)?[sd]/g, ( match, position ) => {
			const value = position ? values[ parseInt( position, 10 ) - 1 ] : values[ index++ ];
			return String( value );
		} );
	};

	const share = async ( carryOn ) => {
		const body = new FormData();
		body.append( 'action', 'prairie_ash_demo_import' );
		body.append( '_ajax_nonce', settings.nonce );
		body.append( 'continue', carryOn ? '1' : '0' );

		const response = await fetch( settings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body,
		} );

		return response.json();
	};

	form.addEventListener( 'submit', async ( event ) => {
		event.preventDefault();

		let carryOn = form.elements.continue.value === '1';
		button.disabled = true;
		if ( view ) {
			view.hidden = true;
		}
		status.textContent = format( settings.text.running, Object.values( settings.steps )[ 0 ] );

		try {
			for (;;) {
				const result = await share( carryOn );

				if ( ! result.success ) {
					status.textContent = format( settings.text.failed, result.data.message );
					break;
				}

				if ( result.data.done ) {
					status.textContent = settings.text.done;
					form.elements.continue.value = '0';
					if ( view ) {
						view.hidden = false;
					}
					break;
				}

				carryOn = true;
				form.elements.continue.value = '1';
				status.textContent = 'media' === result.data.step
					? format( settings.text.media, result.data.cursor, result.data.media )
					: format( settings.text.running, settings.steps[ result.data.step ] );
			}
		} catch ( error ) {
			status.textContent = settings.text.network;
		}

		button.disabled = false;
	} );
}() );
