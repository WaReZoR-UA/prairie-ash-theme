<?php
/**
 * Front-end assets and font preloads.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the compiled design stylesheet and the theme bundle.
 *
 * The stylesheet is the global part of the design's SCSS, compiled into
 * assets/css/main.css. The rules only a WooCommerce template uses are split
 * into their own sheets and enqueued here on that template; the rules only a
 * block uses load with the block, see prairie_ash_enqueue_block_sheets(). The
 * bundle is the design's own JS plus the WordPress glue, deferred, with no
 * dependency: it uses no jQuery of its own.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_enqueue_assets() {
	wp_enqueue_style(
		'prairie-ash-main',
		get_theme_file_uri( 'assets/css/main.css' ),
		array(),
		prairie_ash_asset_version( 'assets/css/main.css' )
	);

	wp_enqueue_script(
		'prairie-ash-main',
		get_theme_file_uri( 'assets/js/main.js' ),
		array(),
		prairie_ash_asset_version( 'assets/js/main.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_add_inline_script(
		'prairie-ash-main',
		'window.prairieAsh = ' . wp_json_encode( prairie_ash_script_data() ) . ';',
		'before'
	);

	foreach ( prairie_ash_split_sheets() as $name => $sheet ) {
		if ( array_filter( $sheet['templates'], 'prairie_ash_is_template' ) ) {
			$args = prairie_ash_split_sheet_args( $name );
			wp_enqueue_style( $args['handle'], $args['src'], $args['deps'], $args['ver'] );
			wp_style_add_data( $args['handle'], 'path', $args['path'] );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'prairie_ash_enqueue_assets' );

/**
 * The stylesheets split out of the design's stylesheet by tools/build.mjs.
 *
 * Each sheet holds the rules that can only match where one of its blocks or
 * WooCommerce templates renders, and names those blocks and templates.
 *
 * @since 1.0.0
 *
 * @return array<string, array{blocks: string[], templates: string[]}> Sheets keyed by file name.
 */
function prairie_ash_split_sheets() {
	static $sheets = null;

	if ( null === $sheets ) {
		$manifest = get_theme_file_path( 'assets/css/split/manifest.json' );
		$sheets   = file_exists( $manifest ) ? wp_json_file_decode( $manifest, array( 'associative' => true ) ) : array();
		$sheets   = is_array( $sheets ) ? $sheets : array();
	}

	return $sheets;
}

/**
 * Whether the request is drawn by one of the WooCommerce template groups the
 * stylesheet is split by.
 *
 * @since 1.0.0
 *
 * @param string $template Template group: product, cart or checkout.
 * @return bool
 */
function prairie_ash_is_template( $template ) {
	switch ( $template ) {
		case 'product':
			return function_exists( 'is_product' ) && is_product();
		case 'cart':
			return function_exists( 'is_cart' ) && is_cart();
		case 'checkout':
			return function_exists( 'is_checkout' ) && is_checkout();
	}

	return false;
}

/**
 * Arguments for one split stylesheet.
 *
 * The path lets WordPress print a small sheet inline instead of as a request,
 * within its styles_inline_size_limit. Every sheet depends on the global one,
 * so it is printed after it, which is the order the build split them for.
 *
 * @since 1.0.0
 *
 * @param string $name File name without the extension.
 * @return array{handle: string, src: string, deps: string[], ver: string, path: string}
 */
function prairie_ash_split_sheet_args( $name ) {
	$relative = 'assets/css/split/' . $name . '.css';

	return array(
		'handle' => 'prairie-ash-' . $name,
		'src'    => get_theme_file_uri( $relative ),
		'deps'   => array( 'prairie-ash-main' ),
		'ver'    => prairie_ash_asset_version( $relative ),
		'path'   => get_theme_file_path( $relative ),
	);
}

/**
 * Load each block's share of the stylesheet only where the block renders.
 *
 * The theme loads block assets on demand (inc/performance.php), so
 * wp_enqueue_block_style() enqueues a sheet when one of its blocks renders,
 * and WordPress moves it into the head. A sheet several blocks share is one
 * handle, printed once.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_enqueue_block_sheets() {
	foreach ( prairie_ash_split_sheets() as $name => $sheet ) {
		foreach ( $sheet['blocks'] as $block_name ) {
			wp_enqueue_block_style( $block_name, prairie_ash_split_sheet_args( $name ) );
		}
	}
}
add_action( 'init', 'prairie_ash_enqueue_block_sheets' );

/**
 * Every stylesheet the editor canvas needs: the global one, every split sheet
 * and the editor's own additions, in that order.
 *
 * @since 1.0.0
 *
 * @return string[] Paths relative to the theme root.
 */
function prairie_ash_editor_sheets() {
	$sheets = array( 'assets/css/main.css' );
	foreach ( array_keys( prairie_ash_split_sheets() ) as $name ) {
		$sheets[] = 'assets/css/split/' . $name . '.css';
	}
	$sheets[] = 'assets/css/editor.css';

	return $sheets;
}

/**
 * Data the theme bundle reads: endpoints and the wording its scripts print.
 *
 * Wording printed by a script is read from the same settings as the markup, so
 * the client changes it in one place.
 *
 * @since 1.0.0
 *
 * @return array
 */
function prairie_ash_script_data() {
	$data = array(
		'analyticsOffText' => prairie_ash_setting( 'analytics_off_text' ),
		'pagesOpenLabel'   => prairie_ash_setting( 'pages_toggle_close_label' ),
		'pagesClosedLabel' => prairie_ash_setting( 'pages_toggle_label' ),
		'hoursJoiner'      => prairie_ash_setting( 'hours_joiner' ),
		'todayOpen'        => prairie_ash_setting( 'today_open' ),
		'todayClosed'      => prairie_ash_setting( 'today_closed' ),
		'todayTomorrow'    => prairie_ash_setting( 'today_tomorrow' ),
	);

	if ( function_exists( 'WC' ) ) {
		$data['wcAjax']      = WC_AJAX::get_endpoint( '%%endpoint%%' );
		$data['cartUrl']     = wc_get_cart_url();
		$data['addingLabel'] = prairie_ash_wording( 'card_adding_label' );
		$data['addedLabel']  = prairie_ash_wording( 'card_added_label' );
	}

	return $data;
}

/**
 * Preload all three font files.
 *
 * Wp_preload_resources prints link rel="preload". A font is a CORS-mode fetch
 * even from the same origin, so crossorigin is set or the preload is wasted.
 * The faces are font-display: optional, so a face that has not arrived by the
 * first paint is never swapped in on that page; the italic is preloaded with
 * the other two so it is there wherever the page paints it.
 *
 * @since 1.0.0
 *
 * @param array $resources Resources to preload.
 * @return array
 */
function prairie_ash_preload_fonts( $resources ) {
	foreach ( array( 'LeagueGothic-Regular.woff2', 'InstrumentSerif-Regular.woff2', 'InstrumentSerif-Italic.woff2' ) as $font ) {
		$resources[] = array(
			'href'        => get_theme_file_uri( 'assets/fonts/' . $font ),
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}

	return $resources;
}
add_filter( 'wp_preload_resources', 'prairie_ash_preload_fonts' );
