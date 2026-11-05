<?php
/**
 * Section blocks: registration, the control surface every section hands over,
 * and the per-element styling the client sets in the editor.
 *
 * A section block declares the core supports it opens with serialisation
 * skipped, and this file prints the chosen values onto the section's own root
 * element, in the editor preview as well as on the page. Skipping
 * serialisation is what stops the field plugin's preview wrapper from carrying
 * a second copy of the padding and the background in the canvas.
 *
 * Text inside a section is styled per element: each heading, label, body and
 * card text takes its own alignment, text size and colour from the design's
 * presets, stored in the block's paStyle attribute and printed as an inline
 * style on that element. An element nobody touched prints no style attribute,
 * so an untouched block renders the markup byte for byte.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register every block in the theme's blocks/ directory.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_register_blocks() {
	foreach ( (array) glob( get_theme_file_path( 'blocks/*/block.json' ) ) as $manifest ) {
		register_block_type( dirname( $manifest ) );
	}
}
add_action( 'init', 'prairie_ash_register_blocks' );

/**
 * Add the theme's own category to the inserter.
 *
 * @since 1.0.0
 *
 * @param array[] $categories Block categories.
 * @return array[]
 */
function prairie_ash_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'prairie-ash',
			'title' => 'Prairie Ash sections',
			'icon'  => null,
		)
	);

	return $categories;
}
add_filter( 'block_categories_all', 'prairie_ash_block_category' );

/**
 * Offer the theme's sections, and only those, in the page editor.
 *
 * A page is a stack of the design's sections. A core paragraph or image placed
 * between two sections renders edge to edge with no container and none of the
 * design's type, which is a state the design does not contain. Recorded in
 * CONTROL-SURFACE.md.
 *
 * @since 1.0.0
 *
 * @param bool|string[]           $allowed Allowed block names, or true for all.
 * @param WP_Block_Editor_Context $context The editor context.
 * @return bool|string[]
 */
function prairie_ash_allowed_blocks( $allowed, $context ) {
	if ( empty( $context->post ) || 'page' !== $context->post->post_type ) {
		return $allowed;
	}

	$names = array();
	foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type ) {
		if ( 0 === strpos( $name, 'prairie-ash/' ) ) {
			$names[] = $name;
		}
	}

	return $names;
}
add_filter( 'allowed_block_types_all', 'prairie_ash_allowed_blocks', 10, 2 );

/**
 * The opening attributes of a section block's root element.
 *
 * On the page, the design's classes, the anchor and the styles built from the
 * block's supports are handed IN to get_block_wrapper_attributes(), which joins
 * the classes and prints exactly one id. In the editor preview that function is
 * not called, because the field plugin documents that it warns and outputs
 * nothing there, so the same attributes are written here.
 *
 * @since 1.0.0
 *
 * @param array    $block      The block array handed to the render template.
 * @param bool     $is_preview Whether this render is the editor preview.
 * @param string[] $classes    The section's own classes, in document order.
 * @param array    $extra      Further style, id or aria-label values.
 * @return string Attributes ready to print inside the opening tag.
 */
function prairie_ash_section_attrs( $block, $is_preview, $classes = array(), $extra = array() ) {
	$supports = prairie_ash_support_styles( $block );
	$classes  = array_values( array_filter( array_map( 'strval', (array) $classes ) ) );

	$attrs = array();
	// The design's own id is the default; an anchor the client set wins over it.
	$id = empty( $block['anchor'] ) ? ( empty( $extra['id'] ) ? '' : $extra['id'] ) : $block['anchor'];
	if ( '' !== (string) $id ) {
		$attrs['id'] = prairie_ash_unique_id( (string) $id );
	}
	if ( ! empty( $extra['aria-label'] ) ) {
		$attrs['aria-label'] = $extra['aria-label'];
	}
	$style = trim( $supports . ( empty( $extra['style'] ) ? '' : $extra['style'] ) );

	if ( ! $is_preview ) {
		$attrs['class'] = implode( ' ', $classes );
		if ( '' !== $style ) {
			$attrs['style'] = $style;
		}

		return get_block_wrapper_attributes( $attrs );
	}

	if ( ! empty( $block['name'] ) ) {
		$classes[] = 'wp-block-' . str_replace( '/', '-', $block['name'] );
	}
	if ( ! empty( $block['className'] ) ) {
		$classes[] = $block['className'];
	}
	$attrs['class'] = implode( ' ', array_unique( array_filter( $classes ) ) );
	if ( '' !== $style ) {
		$attrs['style'] = $style;
	}

	return trim( prairie_ash_attrs( $attrs ) );
}

/**
 * Claim an id for an element of a section, unique within one rendering of the content.
 *
 * The first element asking for an id gets it as it is, so the design's ids and
 * the links that point at them stay as they were. A later element asking for
 * the same id, such as a section placed twice or two sections whose headings
 * share a name, gets the next free numbered form instead.
 *
 * @since 1.2.1
 *
 * @param string $base  The id the element would carry on its own.
 * @param bool   $reset Forget every id claimed so far, for a new rendering.
 * @return string The id to print, empty when resetting.
 */
function prairie_ash_unique_id( $base, $reset = false ) {
	static $used = array();

	if ( $reset ) {
		$used = array();
		return '';
	}

	$id     = $base;
	$number = 2;
	while ( isset( $used[ $id ] ) ) {
		$id = $base . '-' . $number;
		++$number;
	}
	$used[ $id ] = true;

	return $id;
}

/**
 * Start every rendering of a post's content with no id claimed.
 *
 * Runs before core parses the blocks, so content rendered twice in one
 * request, once for a plugin and once for the page, prints the same ids both
 * times.
 *
 * @since 1.2.1
 *
 * @param string $content The post content.
 * @return string The same content.
 */
function prairie_ash_reset_unique_ids( $content ) {
	prairie_ash_unique_id( '', true );

	return $content;
}
add_filter( 'the_content', 'prairie_ash_reset_unique_ids', 8 );

/**
 * The id of a section heading, or an empty string when there is no heading.
 *
 * @since 1.2.1
 *
 * @param string $base    The id the design gives the heading.
 * @param string $heading The heading text as the template tests it.
 * @return string
 */
function prairie_ash_heading_id( $base, $heading ) {
	// The same test the templates use to decide whether the heading is printed at all.
	return $heading ? prairie_ash_unique_id( $base ) : '';
}

/**
 * Print the aria-labelledby attribute of a section, only when its heading exists.
 *
 * @since 1.2.1
 *
 * @param string $id The heading id from prairie_ash_heading_id().
 * @return void
 */
function prairie_ash_labelledby( $id ) {
	if ( '' !== (string) $id ) {
		echo ' aria-labelledby="' . esc_attr( $id ) . '"';
	}
}

/**
 * Build the inline style of a section from its core supports.
 *
 * Serialisation of every support is skipped in block.json, so nothing reaches
 * the page unless it is built here. Only preset values can be chosen in this
 * theme, so every value resolves to a design custom property. The text colour
 * lands on the section root, where the running text inherits it, and a
 * background image takes core's defaults of cover, centred when contained.
 *
 * @since 1.0.0
 *
 * @param array $block The block array handed to the render template.
 * @return string CSS declarations, empty when nothing was chosen.
 */
function prairie_ash_support_styles( $block ) {
	$style = isset( $block['style'] ) && is_array( $block['style'] ) ? $block['style'] : array();
	$input = array();

	if ( ! empty( $style['spacing'] ) && is_array( $style['spacing'] ) ) {
		$input['spacing'] = array_intersect_key( $style['spacing'], array_flip( array( 'padding', 'margin' ) ) );
	}
	if ( ! empty( $style['dimensions']['minHeight'] ) ) {
		$input['dimensions'] = array( 'minHeight' => $style['dimensions']['minHeight'] );
	}
	if ( ! empty( $style['background'] ) && is_array( $style['background'] ) ) {
		$background = $style['background'];
		// Core's own defaults, which skipped serialisation never runs: cover, and centred when contained.
		if ( ! empty( $background['backgroundImage'] ) ) {
			if ( empty( $background['backgroundSize'] ) ) {
				$background['backgroundSize'] = 'cover';
			}
			if ( 'contain' === $background['backgroundSize'] && empty( $background['backgroundPosition'] ) ) {
				$background['backgroundPosition'] = '50% 50%';
			}
		}
		$input['background'] = $background;
	}

	$css = '';
	if ( ! empty( $input ) ) {
		$result = wp_style_engine_get_styles( $input );
		$css    = isset( $result['css'] ) ? $result['css'] : '';
	}

	if ( ! empty( $block['backgroundColor'] ) ) {
		$css .= 'background-color:var(--wp--preset--color--' . sanitize_key( $block['backgroundColor'] ) . ');';
	}
	if ( ! empty( $block['textColor'] ) ) {
		$css .= 'color:var(--wp--preset--color--' . sanitize_key( $block['textColor'] ) . ');';
	}

	return $css;
}

/**
 * The text elements of each section block, for the per-element panels.
 *
 * Read from the elements key of each block's own fields.php, so a block's
 * elements live beside its markup. A key is permanent once styles are saved.
 *
 * @since 1.0.0
 *
 * @return array<string, array<string, string>> Element labels keyed by block name and element key.
 */
function prairie_ash_block_elements() {
	static $elements = null;

	if ( null !== $elements ) {
		return $elements;
	}

	$elements = array();
	foreach ( prairie_ash_block_definitions() as $name => $definition ) {
		if ( ! empty( $definition['elements'] ) ) {
			$elements[ $name ] = $definition['elements'];
		}
	}

	return $elements;
}

/**
 * The style attribute for one text element of a section block.
 *
 * Prints nothing when the client changed nothing on that element, so an
 * untouched block keeps the markup exactly. Every value is a preset slug,
 * checked against the theme's own lists before it reaches the page.
 *
 * @since 1.0.0
 *
 * @param array  $block The block array handed to the render template.
 * @param string $key   Element key as registered for this block.
 * @return void
 */
function prairie_ash_el( $block, $key ) {
	echo prairie_ash_el_attr( $block, $key ); // Escaped in prairie_ash_el_attr().
}

/**
 * Build the style attribute for one text element.
 *
 * @since 1.0.0
 *
 * @param array  $block The block array handed to the render template.
 * @param string $key   Element key.
 * @return string The attribute with a leading space, or an empty string.
 */
function prairie_ash_el_attr( $block, $key ) {
	$css = prairie_ash_el_css( $block, $key );

	return '' === $css ? '' : ' style="' . esc_attr( $css ) . '"';
}

/**
 * The CSS declarations chosen for one text element.
 *
 * @since 1.0.0
 *
 * @param array  $block The block array handed to the render template.
 * @param string $key   Element key.
 * @return string
 */
function prairie_ash_el_css( $block, $key ) {
	if ( empty( $block['paStyle'][ $key ] ) || ! is_array( $block['paStyle'][ $key ] ) ) {
		return '';
	}

	$chosen  = $block['paStyle'][ $key ];
	$presets = prairie_ash_presets();
	$css     = '';

	if ( ! empty( $chosen['align'] ) && in_array( $chosen['align'], array( 'left', 'center', 'right' ), true ) ) {
		$css .= 'text-align:' . $chosen['align'] . ';align-self:stretch;';
	}
	if ( ! empty( $chosen['size'] ) && in_array( $chosen['size'], $presets['sizes'], true ) ) {
		$css .= 'font-size:var(--wp--preset--font-size--' . $chosen['size'] . ');';
		$css .= 'line-height:var(--wp--custom--line-height--' . $chosen['size'] . ');';
	}
	if ( ! empty( $chosen['color'] ) && in_array( $chosen['color'], $presets['colors'], true ) ) {
		$css .= 'color:var(--wp--preset--color--' . $chosen['color'] . ');';
	}

	return $css;
}

/**
 * The preset slugs a per-element choice may name.
 *
 * @since 1.0.0
 *
 * @return array{sizes: string[], colors: string[]}
 */
function prairie_ash_presets() {
	static $presets = null;

	if ( null === $presets ) {
		$sizes   = wp_get_global_settings( array( 'typography', 'fontSizes', 'theme' ) );
		$colors  = wp_get_global_settings( array( 'color', 'palette', 'theme' ) );
		$presets = array(
			'sizes'  => wp_list_pluck( (array) $sizes, 'slug' ),
			'colors' => wp_list_pluck( (array) $colors, 'slug' ),
		);
	}

	return $presets;
}

/**
 * Load the editor script that draws one styling panel per text element.
 *
 * Written against the editor's own globals, with no build step. The element
 * list comes from the same definitions the server renders from.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_editor_assets() {
	wp_enqueue_script(
		'prairie-ash-element-styles',
		get_theme_file_uri( 'assets/js/editor-elements.js' ),
		array( 'wp-hooks', 'wp-compose', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ),
		prairie_ash_asset_version( 'assets/js/editor-elements.js' ),
		array( 'in_footer' => true )
	);

	$sizes = array();
	foreach ( (array) wp_get_global_settings( array( 'typography', 'fontSizes', 'theme' ) ) as $size ) {
		$sizes[] = array(
			'slug' => $size['slug'],
			'name' => $size['name'],
		);
	}

	wp_add_inline_script(
		'prairie-ash-element-styles',
		'window.prairieAshElements = ' . wp_json_encode(
			array(
				'blocks' => prairie_ash_block_elements(),
				'sizes'  => $sizes,
			)
		) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'prairie_ash_editor_assets' );

/**
 * The block style variation chosen for a section, as one of the allowed slugs.
 *
 * A style variation lands in the block's className as is-style-<slug>. A block
 * saved before any style was chosen carries none, and a slug outside the
 * allowed set is stale, so both resolve to the first slug, which is the design's
 * own look for that section.
 *
 * @since 1.0.0
 *
 * @param array    $block    The block array handed to the render template.
 * @param string[] $variants Allowed slugs, the design's own first.
 * @return string
 */
function prairie_ash_block_style( $block, $variants ) {
	$class_names = empty( $block['className'] ) ? array() : preg_split( '/\s+/', (string) $block['className'] );

	foreach ( $class_names as $class_name ) {
		if ( 0 === strpos( $class_name, 'is-style-' ) && in_array( substr( $class_name, 9 ), $variants, true ) ) {
			return substr( $class_name, 9 );
		}
	}

	return $variants[0];
}
