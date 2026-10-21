/**
 * One styling panel per text element of a Prairie Ash section block.
 *
 * Each panel offers the element's alignment, its text size from the design's
 * type scale and its colour from the design's palette, the presets the client
 * asked for. The choice is stored in the block's paStyle attribute, keyed by
 * element, and the server prints it on that element only. A reset button on
 * each panel returns the element to the design, and a changed panel says so in
 * its title.
 *
 * Written against the editor's globals, so it needs no build step.
 */
( function ( wp, config ) {
	if ( ! wp || ! config ) {
		return;
	}

	const el = wp.element.createElement;
	const Fragment = wp.element.Fragment;
	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { InspectorControls, useSettings } = wp.blockEditor;
	const { PanelBody, SelectControl, ColorPalette, Button, BaseControl } = wp.components;
	const ToggleGroupControl = wp.components.ToggleGroupControl || wp.components.__experimentalToggleGroupControl;
	const ToggleGroupControlOption = wp.components.ToggleGroupControlOption || wp.components.__experimentalToggleGroupControlOption;

	const ALIGN = [
		{ value: 'left', label: 'Left' },
		{ value: 'center', label: 'Centre' },
		{ value: 'right', label: 'Right' },
	];

	function ElementPanel( props ) {
		const { label, value, onChange, palette } = props;
		const changed = !! ( value.align || value.size || value.color );
		const set = ( key, next ) => {
			const copy = Object.assign( {}, value );
			if ( next ) {
				copy[ key ] = next;
			} else {
				delete copy[ key ];
			}
			onChange( copy );
		};
		const colorValue = value.color ? ( palette.find( ( c ) => c.slug === value.color ) || {} ).color : undefined;

		return el(
			PanelBody,
			{ title: changed ? label + ' (changed)' : label, initialOpen: false },
			ToggleGroupControl &&
				el(
					ToggleGroupControl,
					{
						label: 'Alignment',
						value: value.align || undefined,
						isDeselectable: true,
						isBlock: true,
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
						onChange: ( next ) => set( 'align', next ),
					},
					ALIGN.map( ( option ) =>
						el( ToggleGroupControlOption, { key: option.value, value: option.value, label: option.label } )
					)
				),
			el( SelectControl, {
				label: 'Text size',
				value: value.size || '',
				options: [ { value: '', label: 'As designed' } ].concat(
					config.sizes.map( ( size ) => ( { value: size.slug, label: size.name } ) )
				),
				__nextHasNoMarginBottom: true,
				__next40pxDefaultSize: true,
				onChange: ( next ) => set( 'size', next ),
			} ),
			el(
				BaseControl,
				{ label: 'Colour', __nextHasNoMarginBottom: true },
				el( ColorPalette, {
					colors: palette,
					value: colorValue,
					disableCustomColors: true,
					clearable: true,
					onChange: ( next ) => {
						const found = palette.find( ( c ) => c.color === next );
						set( 'color', found ? found.slug : '' );
					},
				} )
			),
			changed &&
				el(
					Button,
					{ variant: 'secondary', onClick: () => onChange( {} ) },
					'Back to the design'
				)
		);
	}

	const withElementPanels = createHigherOrderComponent( ( BlockEdit ) => {
		return function ( props ) {
			const elements = config.blocks[ props.name ];
			const [ palette ] = useSettings( 'color.palette' );
			if ( ! elements ) {
				return el( BlockEdit, props );
			}
			const styles = props.attributes.paStyle || {};
			const update = ( key, value ) => {
				const next = Object.assign( {}, styles );
				if ( Object.keys( value ).length ) {
					next[ key ] = value;
				} else {
					delete next[ key ];
				}
				props.setAttributes( { paStyle: next } );
			};

			return el(
				Fragment,
				null,
				el( BlockEdit, props ),
				el(
					InspectorControls,
					{ group: 'styles' },
					Object.keys( elements ).map( ( key ) =>
						el( ElementPanel, {
							key,
							label: elements[ key ],
							value: styles[ key ] || {},
							palette: palette || [],
							onChange: ( value ) => update( key, value ),
						} )
					)
				)
			);
		};
	}, 'withPrairieAshElementPanels' );

	addFilter( 'editor.BlockEdit', 'prairie-ash/element-panels', withElementPanels );
} )( window.wp, window.prairieAshElements );
