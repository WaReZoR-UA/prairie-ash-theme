<?php
/**
 * The motion layer's page flag: which scene a page opens with.
 *
 * The design's motion modules (src/js/files/*.js) letter each page's title on
 * load. So that the first paint already shows the faint title they write over,
 * the markup sets a flag on the html element from the head, after the
 * stylesheet, only when motion is allowed: data-hero="pre" on Home,
 * data-shop="pre" on the shop and so on. The page stylesheets draw the faint
 * state from that flag (src/scss/*-motion.scss), and the module takes the flag
 * over when it starts.
 *
 * The flag is printed in the head and never in the body: a script inside the
 * body pauses the parser, the browser shows the new page mid-parse and aborts
 * the cross-document view transition.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Which flag each section block sets when it opens a page.
 *
 * A block that serves two pages of the design names its scene in a field, so
 * the value here is the flag used when that field is empty.
 *
 * @since 1.1.0
 *
 * @return array<string, array{flag: string, scenes?: string[]}> Flags keyed by block name.
 */
function prairie_ash_motion_flags() {
	return array(
		'prairie-ash/hero'            => array( 'flag' => 'hero' ),
		'prairie-ash/roastery-title'  => array( 'flag' => 'roast' ),
		'prairie-ash/shop-title'      => array( 'flag' => 'shop' ),
		'prairie-ash/visit-title'     => array( 'flag' => 'visit' ),
		'prairie-ash/about-story'     => array( 'flag' => 'about' ),
		'prairie-ash/menu-title'      => array( 'flag' => 'menu' ),
		'prairie-ash/wholesale-title' => array( 'flag' => 'wholesale' ),
		'prairie-ash/contact-title'   => array( 'flag' => 'contact' ),
		'prairie-ash/sent'            => array(
			'flag'   => 'contact',
			'scenes' => array( 'contact', 'wholesale' ),
		),
		'prairie-ash/signup-done'     => array( 'flag' => 'signup' ),
		'prairie-ash/shipping-title'  => array( 'flag' => 'shipping' ),
		'prairie-ash/legal-title'     => array(
			'flag'   => 'privacy',
			'scenes' => array( 'privacy', 'accessibility' ),
		),
	);
}

/**
 * The flag of the scene the current request opens with.
 *
 * WooCommerce's own templates are recognised by its conditionals. Every other
 * page takes the flag of the first section block in its content that opens a
 * scene, see prairie_ash_content_flag().
 *
 * @since 1.1.0
 *
 * @return string The flag name, or an empty string when the page has none.
 */
function prairie_ash_motion_flag() {
	if ( function_exists( 'is_woocommerce' ) ) {
		if ( is_product() ) {
			return 'product';
		}
		if ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ) {
			return 'shop';
		}
		if ( is_wc_endpoint_url( 'order-received' ) ) {
			return 'order';
		}
		if ( is_cart() ) {
			return 'cart';
		}
		if ( is_checkout() ) {
			return 'checkout';
		}
	}

	if ( ! is_singular() ) {
		return '';
	}

	return prairie_ash_content_flag( (string) get_post_field( 'post_content', get_queried_object_id() ) );
}

/**
 * The flag of the scene a page's content opens with.
 *
 * @since 1.1.0
 *
 * @param string $content Post content.
 * @return string The flag of the first section block that opens a scene, or an empty string.
 */
function prairie_ash_content_flag( $content ) {
	$flags = prairie_ash_motion_flags();
	foreach ( parse_blocks( $content ) as $block ) {
		$name = $block['blockName'] ?? '';
		if ( ! isset( $flags[ $name ] ) ) {
			continue;
		}
		$flag  = $flags[ $name ]['flag'];
		$scene = $block['attrs']['data']['scene'] ?? '';
		if ( isset( $flags[ $name ]['scenes'] ) && in_array( $scene, $flags[ $name ]['scenes'], true ) ) {
			$flag = $scene;
		}
		return $flag;
	}

	return '';
}

/**
 * The scene a block that serves two pages of the design renders.
 *
 * @since 1.1.0
 *
 * @param string $block_name Block name, e.g. 'prairie-ash/sent'.
 * @param string $scene      The block's scene field value.
 * @return string One of the block's scenes, the default one when the field is empty.
 */
function prairie_ash_motion_scene( $block_name, $scene ) {
	$flags = prairie_ash_motion_flags();
	if ( ! isset( $flags[ $block_name ] ) ) {
		return '';
	}
	$entry = $flags[ $block_name ];

	return isset( $entry['scenes'] ) && in_array( $scene, $entry['scenes'], true ) ? $scene : $entry['flag'];
}

/**
 * Print the page flag in the head, right after the stylesheet.
 *
 * Hooked at the priority of wp_print_styles and registered after it, so the
 * flag follows the stylesheet link the way the markup's head writes it. When
 * a theme script under assets/js fails to load before the scene starts, the
 * flag is removed again, so the page is shown finished rather than held at
 * its motion start state.
 *
 * @since 1.1.0
 * @since 1.2.1 The flag is removed when a theme script fails to load.
 *
 * @return void
 */
function prairie_ash_print_motion_flag() {
	$flag = prairie_ash_motion_flag();
	if ( '' === $flag ) {
		return;
	}

	wp_print_inline_script_tag(
		sprintf(
			'(function () { if (!window.matchMedia("(prefers-reduced-motion: no-preference)").matches) return; var d = document.documentElement; d.dataset.%1$s = "pre"; window.addEventListener("error", function (e) { var s = e.target; if (s && "SCRIPT" === s.tagName && -1 !== s.src.indexOf("/assets/js/") && "pre" === d.dataset.%1$s) delete d.dataset.%1$s; }, true); })();',
			preg_replace( '/[^a-z]/', '', $flag )
		)
	);
}
add_action( 'wp_head', 'prairie_ash_print_motion_flag', 8 );
