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
 * with them, on every page. Outside a product, the cart, the checkout and the
 * account they have nothing to act on: the coffee cards on content pages and
 * on the shop's own list and search add to the cart through the theme's own
 * script (src/js/wp/shop.js), which needs no jQuery.
 *
 * @since 1.0.0
 * @since 1.1.0 Left off the shop list and its search as well.
 *
 * @return void
 */
function prairie_ash_dequeue_shop_scripts() {
	if ( ! function_exists( 'is_woocommerce' ) || is_product() || is_cart() || is_checkout() || is_account_page() ) {
		return;
	}

	foreach ( array( 'wc-add-to-cart', 'woocommerce', 'wc-js-cookie', 'wc-jquery-blockui' ) as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'prairie_ash_dequeue_shop_scripts', 99 );

/**
 * Leave WooCommerce's cart script off the cart.
 *
 * The script drives WooCommerce's own cart markup, which the design's cart
 * replaces, see woocommerce/cart/cart.php. It still answers Enter in a
 * quantity of any form classed as a cart form: it cancels the key and then
 * finds none of its own markup to update, so Enter did nothing. Without it,
 * Enter submits the cart form through its first button, which updates the
 * cart, and the theme's own script updates a changed quantity as before.
 *
 * @since 1.2.1
 *
 * @return void
 */
function prairie_ash_dequeue_cart_script() {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		wp_dequeue_script( 'wc-cart' );
	}
}
add_action( 'wp_enqueue_scripts', 'prairie_ash_dequeue_cart_script', 99 );

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
 * An inline script a payment method prints in its own markup is not a
 * dependent WordPress knows of, so the cart, the checkout (with order pay and
 * order received) and the account pages keep jQuery blocking.
 *
 * @since 1.0.0
 * @since 1.2.1 Not on the cart, the checkout and the account pages.
 *
 * @return void
 */
function prairie_ash_defer_form_scripts() {
	// A payment method may print an inline script that calls jQuery at once, inside its
	// payment fields, so jQuery stays blocking wherever payment fields are printed.
	if ( function_exists( 'is_checkout' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		return;
	}

	foreach ( array( 'jquery-core', 'jquery-migrate', 'formidable' ) as $handle ) {
		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_script_add_data( $handle, 'strategy', 'defer' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'prairie_ash_defer_form_scripts', 100 );

/**
 * Hold jQuery and Formidable's script back until the visitor reaches for a form.
 *
 * A form that is not submitted over Ajax posts and validates on the server
 * without the plugin's script, so the page does not need it to load. The
 * three tags are printed inert, as data blocks the browser neither fetches
 * nor runs, and the theme's bundle (src/js/wp/form-intent.js) turns them into
 * scripts, in their order, the first time a pointer, a key or the focus lands
 * in a form. Formidable starts on jQuery's ready, which fires at once on a
 * page already parsed. The scripts load as before on a form's own response,
 * where the plugin focuses the errors, on a page with an Ajax form, and on a
 * page where any other script needs jQuery.
 *
 * @since 1.1.0
 *
 * @return void
 */
function prairie_ash_hold_form_scripts() {
	global $frm_vars;

	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
	if ( is_admin() || 'post' === $method || ! wp_script_is( 'formidable', 'enqueued' ) ) {
		return;
	}

	$forms = isset( $frm_vars['forms_loaded'] ) ? (array) $frm_vars['forms_loaded'] : array();
	foreach ( $forms as $form ) {
		if ( ! is_object( $form ) || ! empty( $form->options['ajax_submit'] ) || ! empty( $form->options['js_validate'] ) ) {
			return;
		}
	}

	$jquery  = array( 'jquery', 'jquery-core', 'jquery-migrate' );
	$scripts = wp_scripts();
	if ( array_intersect( $jquery, $scripts->done ) ) {
		return;
	}

	$pending = array_diff( $scripts->queue, $jquery, array( 'formidable' ) );
	$seen    = array();
	while ( $pending ) {
		$handle = array_pop( $pending );
		if ( isset( $seen[ $handle ] ) || ! isset( $scripts->registered[ $handle ] ) ) {
			continue;
		}
		$seen[ $handle ] = true;
		$deps            = (array) $scripts->registered[ $handle ]->deps;
		if ( array_intersect( $jquery, $deps ) ) {
			return;
		}
		$pending = array_merge( $pending, $deps );
	}

	add_filter( 'script_loader_tag', 'prairie_ash_inert_form_script', 10, 2 );
}
add_action( 'wp_print_footer_scripts', 'prairie_ash_hold_form_scripts', 1 );

/**
 * Print jQuery and Formidable's script as inert tags carrying their address.
 *
 * @since 1.1.0
 *
 * @param string $tag    The script tags printed for the handle.
 * @param string $handle The script handle.
 * @return string
 */
function prairie_ash_inert_form_script( $tag, $handle ) {
	if ( ! in_array( $handle, array( 'jquery-core', 'jquery-migrate', 'formidable' ), true ) ) {
		return $tag;
	}

	$tags = new WP_HTML_Tag_Processor( $tag );
	while ( $tags->next_tag( 'script' ) ) {
		$src = $tags->get_attribute( 'src' );
		if ( $handle . '-js' !== $tags->get_attribute( 'id' ) || ! is_string( $src ) ) {
			continue;
		}
		$tags->set_attribute( 'type', 'text/plain' );
		$tags->set_attribute( 'data-intent-src', $src );
		foreach ( array( 'src', 'defer', 'async', 'data-wp-strategy' ) as $attribute ) {
			$tags->remove_attribute( $attribute );
		}
	}

	return $tags->get_updated_html();
}

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
