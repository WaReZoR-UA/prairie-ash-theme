/**
 * Redraws the search result under the Search result fields as they are typed in.
 *
 * An empty field publishes the inherited value, so the preview and the counts
 * show that value rather than nothing. Written without a build step.
 */
( function () {
	const preview = document.querySelector( '[data-search-preview]' );
	if ( ! preview ) {
		return;
	}

	const title = document.querySelector( '[data-key="field_pa_search_title"] input' );
	const description = document.querySelector( '[data-key="field_pa_search_description"] textarea' );
	const titleOut = preview.querySelector( '[data-preview-title]' );
	const descriptionOut = preview.querySelector( '[data-preview-description]' );
	const count = preview.querySelector( '[data-preview-count]' );

	const published = ( field, fallback ) => {
		const typed = field ? field.value.trim() : '';
		return typed || preview.dataset[ fallback ] || '';
	};

	const draw = () => {
		const t = published( title, 'titleFallback' );
		const d = published( description, 'descriptionFallback' );
		titleOut.textContent = t;
		descriptionOut.textContent = d;
		count.textContent = 'Published title: ' + [ ...t ].length + ' characters. Published description: ' + [ ...d ].length + ' characters.';
	};

	[ title, description ].forEach( ( field ) => field && field.addEventListener( 'input', draw ) );
} )();
