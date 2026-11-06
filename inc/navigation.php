<?php
/**
 * Header navigation, the phone page list and the cart link.
 *
 * The links come from the "Main navigation" menu, so the client adds, removes
 * and reorders them under Appearance, Menus. A menu item given the CSS class
 * header__item--wide is hidden on phones and reached through the page list, as
 * the design draws it.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * The page that counts as current for the navigation.
 *
 * A product belongs to the shop, so the shop link is current on a product page.
 *
 * @since 1.0.0
 *
 * @return int Page or post ID, 0 when none.
 */
function prairie_ash_current_nav_id() {
	if ( function_exists( 'is_woocommerce' ) && ( is_shop() || is_product() || is_product_taxonomy() ) ) {
		return (int) wc_get_page_id( 'shop' );
	}

	return (int) get_queried_object_id();
}

/**
 * The items of a menu location, flattened to what the header prints.
 *
 * @since 1.0.0
 *
 * @param string $location Menu location.
 * @return array<int, array{label: string, url: string, classes: string[], current: bool}>
 */
function prairie_ash_menu_items( $location ) {
	$locations = get_nav_menu_locations();

	if ( empty( $locations[ $location ] ) ) {
		return array();
	}

	$items   = wp_get_nav_menu_items( $locations[ $location ] );
	$current = prairie_ash_current_nav_id();
	$out     = array();

	foreach ( (array) $items as $item ) {
		if ( ! empty( $item->menu_item_parent ) ) {
			continue;
		}

		$out[] = array(
			'label'   => $item->title,
			'url'     => $item->url,
			'classes' => array_filter( (array) $item->classes ),
			'current' => $current > 0 && 'post_type' === $item->type && (int) $item->object_id === $current,
			'object'  => 'post_type' === $item->type ? (int) $item->object_id : 0,
		);
	}

	if ( $current > 0 && ! array_filter( wp_list_pluck( $out, 'current' ) ) ) {
		$out = prairie_ash_mark_scene_owner( $out, $current );
	}

	return $out;
}

/**
 * Mark the navigation page a follow-up page belongs to.
 *
 * A page that opens with the same scene as a page in the navigation is that
 * page's follow-up, as the sent notice after a message is to Contact and the
 * one after an inquiry is to Wholesale, and the design marks that page as
 * current. A page whose scene no navigation page shares marks nothing.
 *
 * @since 1.1.0
 *
 * @param array $items   Items as built by prairie_ash_menu_items().
 * @param int   $current ID of the current page.
 * @return array The items, with the owner marked current.
 */
function prairie_ash_mark_scene_owner( $items, $current ) {
	$flag = prairie_ash_content_flag( (string) get_post_field( 'post_content', $current ) );
	if ( '' === $flag ) {
		return $items;
	}

	foreach ( $items as $index => $item ) {
		if ( $item['object'] && prairie_ash_content_flag( (string) get_post_field( 'post_content', $item['object'] ) ) === $flag ) {
			$items[ $index ]['current'] = true;
			break;
		}
	}

	return $items;
}

/**
 * The cart link text, with the number of items in the cart.
 *
 * @since 1.0.0
 *
 * @return string
 */
function prairie_ash_cart_label() {
	$count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	$noun  = 1 === $count ? prairie_ash_setting( 'cart_item_one' ) : prairie_ash_setting( 'cart_item_many' );

	return sprintf( '%1$s, %2$d %3$s', prairie_ash_setting( 'cart_label' ), $count, $noun );
}

/**
 * Print the cart link the header and the page list share.
 *
 * Data-cart-link lets the cart fragments replace the link after an item is
 * added without a page load.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_cart_link() {
	if ( ! function_exists( 'wc_get_cart_url' ) ) {
		return;
	}

	printf(
		'<a class="nav-link" href="%1$s"%2$s data-cart-link data-cart-hash="%3$s">%4$s</a>',
		esc_url( wc_get_cart_url() ),
		is_cart() ? ' aria-current="page"' : '',
		esc_attr( WC()->cart ? WC()->cart->get_cart_hash() : '' ),
		esc_html( prairie_ash_cart_label() )
	);
}

/**
 * Bring the header cart count up to date on a page served from a page cache.
 *
 * The count is printed on the server, so a cached page shows the cart of
 * whoever filled the cache. WooCommerce keeps the hash of the visitor's cart in
 * the woocommerce_cart_hash cookie, and the link carries the hash of the cart it
 * was printed for. Only when the two differ is the link fetched again, from
 * WooCommerce's own fragments endpoint, so an uncached page makes no request.
 * WooCommerce's cart fragments script does the same, but needs jQuery on every
 * page.
 *
 * @since 1.2.1
 *
 * @return void
 */
function prairie_ash_cart_link_refresh() {
	if ( ! function_exists( 'WC' ) || ! class_exists( 'WC_AJAX' ) || is_cart() || is_checkout() ) {
		return;
	}

	$script = <<<'JS'
(function () {
	var links = document.querySelectorAll("a[data-cart-link]");
	if (!links.length || !window.fetch) return;
	var found = document.cookie.match(/(?:^|;\s*)woocommerce_cart_hash=([^;]*)/);
	if ((found ? decodeURIComponent(found[1]) : "") === (links[0].getAttribute("data-cart-hash") || "")) return;
	fetch(%s, { method: "POST", credentials: "same-origin" })
		.then(function (response) { return response.json(); })
		.then(function (result) {
			var html = result && result.fragments && result.fragments["a[data-cart-link]"];
			if (!html) return;
			document.querySelectorAll("a[data-cart-link]").forEach(function (link) {
				var holder = document.createElement("template");
				holder.innerHTML = html;
				if (holder.content.firstElementChild) link.replaceWith(holder.content.firstElementChild);
			});
		})
		.catch(function () {});
})();
JS;

	wp_print_inline_script_tag( sprintf( $script, wp_json_encode( WC_AJAX::get_endpoint( 'get_refreshed_fragments' ) ) ) );
}
add_action( 'wp_footer', 'prairie_ash_cart_link_refresh', 20 );

/**
 * The link of the header's directions button.
 *
 * @since 1.0.0
 *
 * @return string URL, empty when the setting is empty.
 */
function prairie_ash_directions_url() {
	$link = prairie_ash_setting( 'directions_link' );

	if ( ! is_string( $link ) || '' === $link ) {
		return '';
	}

	// On the page the button leads to, it jumps to the getting-here section instead.
	if ( untrailingslashit( $link ) === untrailingslashit( get_permalink() ) && is_page() ) {
		return '#getting-here';
	}

	return $link;
}
