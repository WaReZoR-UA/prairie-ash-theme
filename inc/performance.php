<?php
/**
 * What the theme deliberately switches off or on for page weight, each with
 * its reason. The full account is in PERFORMANCE.md.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/*
 * Emoji detection script and styles. The design uses no emoji and the client's
 * copy carries none, so the inline detection script and its styles are a
 * request the page does not use.
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

/*
 * Core block styles only where a core block renders. Pages are stacks of the
 * theme's own sections, which carry no core block markup, so the full core
 * block library stylesheet would load on every page for nothing.
 */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/*
 * Remote block patterns. The editor offers the theme's sections only, so the
 * pattern catalogue fetched from wordpress.org would never be used.
 */
add_filter( 'should_load_remote_block_patterns', '__return_false' );

/**
 * Leave WooCommerce's front-end scripts off the pages that sell nothing.
 *
 * WooCommerce enqueues its add-to-cart, cookie and notice scripts, and jQuery
 * with them, on every page. Outside the shop, a product, the cart, the checkout
 * and the account they have nothing to act on: the coffee cards on content
 * pages add to the cart through the theme's own script, which needs no jQuery.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_dequeue_shop_scripts() {
	if ( ! function_exists( 'is_woocommerce' ) || is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		return;
	}

	foreach ( array( 'wc-add-to-cart', 'woocommerce', 'wc-js-cookie', 'wc-jquery-blockui' ) as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'prairie_ash_dequeue_shop_scripts', 99 );

/**
 * Defer jQuery and Formidable's script on the pages that load them.
 *
 * Formidable enqueues jQuery and its own script only on a page that prints a
 * form, and WooCommerce prints jQuery in the head of its own pages. Both
 * plugins' scripts that depend on jQuery here bind on document ready or are
 * deferred already, and the theme's own form handling listens on the form
 * element rather than waiting for either. WordPress keeps a script blocking
 * by itself when a dependent has no loading strategy or carries an inline
 * script after it, so a plugin that needs jQuery at once still gets it at once.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_defer_form_scripts() {
	foreach ( array( 'jquery-core', 'jquery-migrate', 'formidable' ) as $handle ) {
		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_script_add_data( $handle, 'strategy', 'defer' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'prairie_ash_defer_form_scripts', 100 );

/**
 * Prefetch a same-site page when the visitor shows intent to open it.
 *
 * The document of the next page is fetched on hover or pointer down, so the
 * cross-document view transition starts sooner. It is fetched and not
 * prerendered: the motion layer starts with the page, as it does in the
 * markup, and a prerendered page would play its on-load lettering while
 * hidden and arrive finished. Core switches speculative loading off for a
 * logged-in visitor and for plain permalinks by passing null, and that is
 * kept.
 *
 * @since 1.0.0
 * @since 1.1.0 Prefetch instead of prerender, so a page's motion starts when it is shown.
 *
 * @param array<string, string>|null $config Mode and eagerness, or null when speculative loading is off.
 * @return array<string, string>|null
 */
function prairie_ash_speculation_config( $config ) {
	if ( null === $config ) {
		return null;
	}

	return array(
		'mode'      => 'prefetch',
		'eagerness' => 'moderate',
	);
}
add_filter( 'wp_speculation_rules_configuration', 'prairie_ash_speculation_config' );

/**
 * Never prefetch the cart, the checkout (order received and order pay sit
 * under it) or the account pages (orders sit under it).
 *
 * Their state changes with every visit, and a prefetch would fetch it for a
 * visit that may never happen. Core already excludes every URL that carries a
 * query string, which covers the add-to-cart links. Paths are relative to the
 * home URL; core prefixes them.
 *
 * @since 1.0.0
 *
 * @param string[] $paths Path patterns excluded from speculative loading.
 * @return string[]
 */
function prairie_ash_speculation_exclude( $paths ) {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return $paths;
	}

	foreach ( array( 'cart', 'checkout', 'myaccount' ) as $page ) {
		$page_id = wc_get_page_id( $page );
		if ( $page_id > 0 && get_post( $page_id ) ) {
			$paths[] = '/' . get_page_uri( $page_id ) . '/*';
		}
	}

	return $paths;
}
add_filter( 'wp_speculation_rules_href_exclude_paths', 'prairie_ash_speculation_exclude' );
