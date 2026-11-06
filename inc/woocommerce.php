<?php
/**
 * WooCommerce: the coffee fields on a product, the price line as the design
 * writes it, the coffee sign card, adding to the cart from a card, and the
 * delivery choices at checkout.
 *
 * The shop, the product page, the cart, the checkout and the order received
 * page are WooCommerce's own templates overridden in woocommerce/ with the
 * design's markup, so everything WooCommerce does still happens.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

// The design draws its own notices, breadcrumb and layout, so WooCommerce's
// stylesheets would only fight it.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Register the coffee fields shown on the cards and on the product page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_register_product_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$fields = array(
		array( 'tab', 'tab_card', 'Card and summary' ),
		array( 'text', 'origin_line', 'Origin line', array( 'instructions' => 'The small line above the name on cards and on the product page, for example "Huila, Colombia, direct from the farm".' ) ),
		array( 'text', 'page_origin', 'Origin line on the product page', array( 'instructions' => 'Leave empty to use the origin line above. Set it when the product page says more than the card, for example "Sidama, Ethiopia, direct from the washing station".' ) ),
		array( 'text', 'notes', 'Tasting notes' ),
		array( 'text', 'roast_line', 'Roast line', array( 'instructions' => 'Shown on the cards, for example "Light roast, washed".' ) ),
		array( 'text', 'pack_label', 'Pack size, for a product with no bag sizes', array( 'instructions' => 'Printed after the price, for example "3 x 100 g". A coffee with bag sizes prints each size from its variations instead.' ) ),
		array(
			'checkbox',
			'brew',
			'Brew methods it suits',
			array(
				'choices'      => array(
					'filter'   => 'Filter brewing',
					'espresso' => 'Espresso',
				),
				'layout'       => 'horizontal',
				'instructions' => 'Used by the "Show coffees for" buttons in the shop.',
			),
		),
		array( 'text', 'card_button_label', 'Card button text', array( 'placeholder' => 'Add to cart' ) ),
		array(
			'text',
			'card_button_aria',
			'Card button, read by screen readers',
			array(
				'placeholder'  => 'Add [name] to cart',
				'instructions' => 'Leave empty to read "Add" followed by the coffee\'s name.',
			),
		),
		array(
			'text',
			'sold_out_label',
			'Text when sold out',
			array(
				'placeholder'  => 'Sold out',
				'instructions' => 'Shown on the button and in the stock row while the coffee is out of stock, for example "Sold out until Friday\'s roast".',
			),
		),
		array(
			'repeater',
			'facts',
			'Facts beside the price',
			array(
				'instructions' => 'One row per fact, any number. The stock row is added after them from the stock in WooCommerce.',
				'collapsed'    => 'label',
				'button_label' => 'Add a fact',
				'sub_fields'   => array(
					array( 'text', 'label', 'Label' ),
					array( 'text', 'value', 'Value' ),
				),
			),
		),
		array( 'tab', 'tab_story', 'Where it comes from' ),
		array( 'text', 'story_title', 'Heading', array( 'placeholder' => 'Where this coffee comes from' ) ),
		array( 'textarea', 'story_body', 'Text', array( 'instructions' => 'Leave a blank line between paragraphs.' ) ),
		array( 'image', 'story_image', 'Photo' ),
		array( 'tab', 'tab_brewing', 'How to brew it' ),
		array( 'text', 'brewing_title', 'Heading', array( 'placeholder' => 'How Mara brews it' ) ),
		array( 'image', 'brewing_image', 'Photo' ),
		array(
			'repeater',
			'brewing',
			'Recipe',
			array(
				'collapsed'    => 'label',
				'button_label' => 'Add a step',
				'sub_fields'   => array(
					array( 'text', 'label', 'Label' ),
					array( 'text', 'value', 'Value' ),
				),
			),
		),
	);

	$expanded = array();
	foreach ( $fields as $entry ) {
		$expanded[] = prairie_ash_expand_field( 'product', $entry );
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_pa_product',
			'title'    => 'Coffee details',
			'fields'   => $expanded,
			'position' => 'acf_after_title',
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'prairie_ash_register_product_fields' );

/**
 * A price as plain text, the way the design letters it.
 *
 * The design animates price figures character by character, so the price is
 * printed as one text run rather than as WooCommerce's nested spans.
 *
 * @since 1.0.0
 *
 * @param float|string $amount Price.
 * @return string For example "$21.00".
 */
function prairie_ash_price_text( $amount ) {
	return html_entity_decode( wp_strip_all_tags( wc_price( (float) $amount ) ), ENT_QUOTES, 'UTF-8' );
}

/**
 * The price line of a coffee: every bag size with its price.
 *
 * For a coffee with bag sizes, one "price for size" per size, from the lowest
 * price among that size's variations. For a product without sizes, the price
 * and the pack size field.
 *
 * @since 1.0.0
 *
 * @param WC_Product $product Product.
 * @return string For example "$21.00 for 340 g, $56.00 for 1 kg".
 */
function prairie_ash_price_line( $product ) {
	$for = prairie_ash_wording( 'price_for' );

	if ( $product->is_type( 'variable' ) ) {
		$by_size = array();
		foreach ( $product->get_available_variations( 'objects' ) as $variation ) {
			$size = $variation->get_attribute( 'pa_bag-size' );
			if ( '' === $size ) {
				continue;
			}
			$price = (float) $variation->get_price();
			if ( ! isset( $by_size[ $size ] ) || $price < $by_size[ $size ] ) {
				$by_size[ $size ] = $price;
			}
		}

		if ( empty( $by_size ) ) {
			$prices = $product->get_variation_prices();
			$low    = empty( $prices['price'] ) ? 0 : min( $prices['price'] );

			return prairie_ash_price_text( $low );
		}

		asort( $by_size );
		$parts = array();
		foreach ( $by_size as $size => $price ) {
			$parts[] = prairie_ash_price_text( $price ) . ' ' . $for . ' ' . prairie_ash_nbsp_units( $size );
		}

		return implode( ', ', $parts );
	}

	$pack = (string) get_field( 'pack_label', $product->get_id() );

	return prairie_ash_price_text( $product->get_price() ) . ( '' !== $pack ? ' ' . $for . ' ' . prairie_ash_nbsp_units( $pack ) : '' );
}

/**
 * Keep a figure and its unit together, as the design does with a no-break space.
 *
 * @since 1.0.0
 *
 * @param string $text For example "340 g" or "3 x 100 g".
 * @return string
 */
function prairie_ash_nbsp_units( $text ) {
	return str_replace( ' ', "\xC2\xA0", trim( $text ) );
}

/**
 * The variation a card button adds: the first bag size with whole beans.
 *
 * The design's card adds a coffee in one click, so it adds the default the
 * product page also starts on. The default attributes set on the product in
 * WooCommerce win when they are set.
 *
 * @since 1.0.0
 *
 * @param WC_Product $product Product.
 * @return int Product or variation ID to add, 0 when nothing can be added.
 */
function prairie_ash_card_add_id( $product ) {
	if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return 0;
	}

	if ( ! $product->is_type( 'variable' ) ) {
		return $product->get_id();
	}

	$defaults = $product->get_default_attributes();
	$best     = 0;
	foreach ( $product->get_available_variations( 'objects' ) as $variation ) {
		if ( ! $variation->is_in_stock() ) {
			continue;
		}
		$matches = true;
		foreach ( $defaults as $name => $value ) {
			if ( $variation->get_attribute( $name ) !== '' && sanitize_title( $variation->get_attribute( $name ) ) !== $value ) {
				$matches = false;
			}
		}
		if ( $matches ) {
			return $variation->get_id();
		}
		if ( ! $best ) {
			$best = $variation->get_id();
		}
	}

	return $best;
}

/**
 * The card and product fields of a coffee, resolved for printing.
 *
 * @since 1.0.0
 *
 * @param WC_Product $product Product.
 * @return array<string, mixed>
 */
function prairie_ash_coffee( $product ) {
	$id       = $product->get_id();
	$name     = $product->get_name();
	$sold_out = ! $product->is_in_stock();
	$label    = (string) get_field( 'card_button_label', $id );
	$aria     = (string) get_field( 'card_button_aria', $id );
	$sold     = (string) get_field( 'sold_out_label', $id );

	return array(
		'id'         => $id,
		'name'       => $name,
		'url'        => get_permalink( $id ),
		'origin'     => (string) get_field( 'origin_line', $id ),
		'notes'      => (string) get_field( 'notes', $id ),
		'roast'      => (string) get_field( 'roast_line', $id ),
		'price'      => prairie_ash_price_line( $product ),
		'brew'       => implode( ' ', array_map( 'sanitize_html_class', (array) get_field( 'brew', $id ) ) ),
		'image'      => $product->get_image_id(),
		'sold_out'   => $sold_out,
		'sold_label' => '' !== $sold ? $sold : prairie_ash_wording( 'sold_out' ),
		'button'     => '' !== $label ? $label : prairie_ash_wording( 'card_button' ),
		'aria'       => '' !== $aria ? $aria : ( '' === $label ? sprintf( '%1$s %2$s %3$s', prairie_ash_wording( 'card_button_aria_before' ), $name, prairie_ash_wording( 'card_button_aria_after' ) ) : '' ),
		'add_id'     => $sold_out ? 0 : prairie_ash_card_add_id( $product ),
		'slug'       => $product->get_slug(),
	);
}

/**
 * Print the shared form every card button submits.
 *
 * A card button names this form, so a coffee is added with a plain post when
 * JavaScript is off, and the card markup keeps the design's own elements.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_card_cart_form() {
	echo '<form id="pa-card-cart" method="post" action="" hidden></form>';
}
add_action( 'wp_footer', 'prairie_ash_card_cart_form', 5 );

/**
 * Resolve the variation from the chosen sizes when the browser posted none.
 *
 * The product page sends the variation the script found. With JavaScript off
 * it sends only the chosen attributes, and WooCommerce refuses a variable
 * product without a variation ID, so it is looked up here before WooCommerce's
 * own handler runs on wp_loaded.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_resolve_posted_variation() {
	if ( empty( $_POST['add-to-cart'] ) || ! empty( $_POST['variation_id'] ) ) {
		return;
	}

	$product = wc_get_product( absint( wp_unslash( $_POST['add-to-cart'] ) ) );
	if ( ! $product || ! $product->is_type( 'variable' ) ) {
		return;
	}

	// Read only the product's own attributes, the way WooCommerce's handler does: a global
	// attribute posts its term slug, a product's own attribute its option text, and a value
	// that is not a string is not an option at all.
	$attributes = array();
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->get_variation() ) {
			continue;
		}
		$key = 'attribute_' . sanitize_title( $attribute->get_name() );
		if ( ! isset( $_POST[ $key ] ) || ! is_scalar( $_POST[ $key ] ) ) {
			continue;
		}
		$attributes[ $key ] = $attribute->is_taxonomy()
			? sanitize_title( wp_unslash( (string) $_POST[ $key ] ) )
			: html_entity_decode( sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) ), ENT_QUOTES, get_bloginfo( 'charset' ) );
	}

	$variation_id = ( new WC_Product_Data_Store_CPT() )->find_matching_product_variation( $product, $attributes );
	if ( $variation_id ) {
		$_POST['variation_id']    = $variation_id;
		$_REQUEST['variation_id'] = $variation_id;
	}
}
add_action( 'wp_loaded', 'prairie_ash_resolve_posted_variation', 5 );

/**
 * Replace the header cart links when the cart changes without a page load.
 *
 * @since 1.0.0
 *
 * @param array $fragments Selector to HTML.
 * @return array
 */
function prairie_ash_cart_fragments( $fragments ) {
	ob_start();
	prairie_ash_cart_link();
	$fragments['a[data-cart-link]'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'prairie_ash_cart_fragments' );

/**
 * Offer one shipping choice and pickup, as the design draws them.
 *
 * Shipping is a flat rate below the free shipping minimum and free above it.
 * Showing both at once would offer a paid rate beside a free one for the same
 * parcel, so the flat rate is dropped whenever free shipping is available.
 *
 * @since 1.0.0
 *
 * @param WC_Shipping_Rate[] $rates Rates for a package.
 * @return WC_Shipping_Rate[]
 */
function prairie_ash_shipping_rates( $rates ) {
	$has_free = false;
	foreach ( $rates as $rate ) {
		if ( 'free_shipping' === $rate->get_method_id() ) {
			$has_free = true;
		}
	}

	if ( ! $has_free ) {
		return $rates;
	}

	foreach ( $rates as $key => $rate ) {
		if ( 'flat_rate' === $rate->get_method_id() ) {
			unset( $rates[ $key ] );
		}
	}

	return $rates;
}
add_filter( 'woocommerce_package_rates', 'prairie_ash_shipping_rates', 100 );

/**
 * Skip WooCommerce's own breadcrumb and wrappers: the templates print the design's.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_woocommerce_cleanup() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	remove_action( 'woocommerce_before_single_product', 'woocommerce_output_all_notices', 10 );
	add_action( 'woocommerce_before_single_product', 'prairie_ash_product_notices', 10 );
}
add_action( 'init', 'prairie_ash_woocommerce_cleanup' );

/**
 * Print the notices above a product only when there are any.
 *
 * WooCommerce prints an empty notices wrapper on every product page. The
 * design has no element there, so the wrapper is left out until a notice,
 * such as an add-to-cart error posted without JavaScript, has to be shown.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_product_notices() {
	if ( function_exists( 'wc_notice_count' ) && wc_notice_count() > 0 ) {
		woocommerce_output_all_notices();
	}
}

/**
 * Whether a cart or order line is a pickup.
 *
 * @since 1.0.0
 *
 * @param string $method_id Shipping method ID.
 * @return bool
 */
function prairie_ash_is_pickup( $method_id ) {
	return in_array( $method_id, array( 'local_pickup', 'pickup_location' ), true );
}

/**
 * The products the shop list draws.
 *
 * On the shop the list is WooCommerce's own main query, narrowed by a search.
 * Elsewhere it is every published product, in the shop's own order.
 *
 * @since 1.0.0
 *
 * @return WC_Product[]
 */
function prairie_ash_shop_products() {
	static $products = null;

	if ( null !== $products ) {
		return $products;
	}

	global $wp_query;
	if ( $wp_query instanceof WP_Query && ( is_shop() || is_product_taxonomy() ) ) {
		$ids = wp_list_pluck( $wp_query->posts, 'ID' );
	} else {
		$ids = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => -1,
				'orderby' => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'return'  => 'ids',
			)
		);
	}

	$products = array_values( array_filter( array_map( 'wc_get_product', $ids ) ) );

	return $products;
}

/**
 * Show every product on the shop, and search it from the design's q field.
 *
 * The list has no page size: the grid wraps at any count, and a page that
 * silently drops the rest would be a cap by another name.
 *
 * @since 1.0.0
 *
 * @param WP_Query $query The query.
 * @return void
 */
function prairie_ash_shop_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! ( $query->is_post_type_archive( 'product' ) || $query->is_page( wc_get_page_id( 'shop' ) ) ) ) {
		return;
	}

	$query->set( 'posts_per_page', -1 );

	$term = prairie_ash_shop_search_term();
	if ( '' !== $term ) {
		$query->set( 's', $term );
	}
}
add_action( 'pre_get_posts', 'prairie_ash_shop_query' );

/**
 * A product field with the shop wording as its default.
 *
 * @since 1.0.0
 *
 * @param string $field   Product field name.
 * @param string $wording Shop wording name used when the field is empty.
 * @return string
 */
function prairie_ash_product_text( $field, $wording ) {
	$value = (string) get_field( $field, get_the_ID() );

	return '' !== trim( $value ) ? $value : prairie_ash_wording( $wording );
}

/**
 * Print text with every figure set in the design's figure face.
 *
 * League Gothic figures inside running text, as the markup sets bag sizes,
 * quantities and prices. The text is escaped before the spans are added.
 *
 * @since 1.0.0
 *
 * @param string $text Text.
 * @return void
 */
function prairie_ash_figure_text( $text ) {
	echo wp_kses(
		(string) preg_replace( '/\d[\d.,]*/', '<span class="figure">$0</span>', esc_html( $text ) ),
		array( 'span' => array( 'class' => true ) )
	);
}

/**
 * What a line's bag is: size and grind, or the pack of a set.
 *
 * The grind reads from the attribute term's description, for example "ground
 * for espresso", which the client edits under Products, Attributes. A term with
 * no description prints its name in lower case.
 *
 * @since 1.0.0
 *
 * @param WC_Product $product Product or variation in the line.
 * @return string For example "340 g bag, whole bean".
 */
function prairie_ash_bag_details( $product ) {
	if ( ! $product->is_type( 'variation' ) ) {
		$pack = (string) get_field( 'pack_label', $product->get_id() );

		return prairie_ash_nbsp_units( $pack );
	}

	$parts = array();
	$size  = $product->get_attribute( 'pa_bag-size' );
	if ( '' !== $size ) {
		$parts[] = prairie_ash_nbsp_units( $size ) . ' ' . prairie_ash_wording( 'bag' );
	}
	$attributes = $product->get_attributes();
	if ( ! empty( $attributes['pa_grind'] ) ) {
		$term = get_term_by( 'slug', $attributes['pa_grind'], 'pa_grind' );
		if ( $term ) {
			$parts[] = '' !== trim( $term->description ) ? trim( $term->description ) : strtolower( $term->name );
		}
	}

	return implode( ', ', $parts );
}

/**
 * The cart's lines, resolved for printing.
 *
 * @since 1.0.0
 *
 * @param array $items Cart contents.
 * @return array<int, array<string, mixed>>
 */
function prairie_ash_cart_lines( $items ) {
	$lines = array();

	foreach ( $items as $key => $item ) {
		$product = $item['data'];
		if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 ) {
			continue;
		}
		$parent  = $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : $product;
		$lines[] = array(
			'key'      => $key,
			'product'  => $parent,
			'name'     => $parent->get_name(),
			'image'    => $product->get_image_id() ? $product->get_image_id() : $parent->get_image_id(),
			'details'  => prairie_ash_bag_details( $product ),
			'quantity' => (int) $item['quantity'],
			'max'      => (int) $product->get_max_purchase_quantity(),
			'each'     => prairie_ash_price_text( $product->get_price() ),
			'total'    => prairie_ash_price_text( $item['line_subtotal'] ),
		);
	}

	return $lines;
}

/**
 * The line above the cart heading: how many coffees, and when each was roasted.
 *
 * The roast date is the product's fact labelled with the "Roasted" wording.
 *
 * @since 1.0.0
 *
 * @param array $lines Cart lines from prairie_ash_cart_lines().
 * @return string
 */
function prairie_ash_cart_eyebrow( $lines ) {
	$count = 0;
	$dates = array();
	foreach ( $lines as $line ) {
		$count += $line['quantity'];
		foreach ( (array) get_field( 'facts', $line['product']->get_id() ) as $fact ) {
			if ( isset( $fact['label'] ) && strtolower( $fact['label'] ) === strtolower( prairie_ash_wording( 'roasted' ) ) ) {
				$dates[ $line['name'] ] = $line['name'] . ' ' . strtolower( prairie_ash_wording( 'roasted' ) ) . ' ' . $fact['value'];
			}
		}
	}

	$text = 1 === $count ? prairie_ash_wording( 'cart_count_one' ) : str_replace( '[count]', prairie_ash_number_word( $count ), prairie_ash_wording( 'cart_count_many' ) );

	return $dates ? $text . ': ' . implode( ', ', $dates ) : $text;
}

/**
 * The shipping value of the cart or the checkout, as the design writes it.
 *
 * @since 1.0.0
 *
 * @return string "Free", a price, or the wording for a cost not known yet.
 */
function prairie_ash_shipping_value() {
	$cart = WC()->cart;

	if ( ! $cart->show_shipping() || ( empty( WC()->session->get( 'chosen_shipping_methods' ) ) && ! $cart->get_shipping_total() ) ) {
		$packages = WC()->shipping()->get_packages();
		if ( empty( $packages ) || empty( $packages[0]['rates'] ) ) {
			return prairie_ash_wording( 'shipping_unknown' );
		}
	}

	$cost = (float) $cart->get_shipping_total();

	return $cost > 0 ? prairie_ash_price_text( $cost ) : prairie_ash_wording( 'free' );
}

/**
 * Remove a cart line posted by its remove button.
 *
 * The cart form carries WooCommerce's own cart nonce, which is checked here
 * before WooCommerce's cart is touched.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_remove_cart_line() {
	if ( empty( $_POST['pa_remove_item'] ) || empty( $_POST['woocommerce-cart-nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-cart-nonce'] ) ), 'woocommerce-cart' ) ) {
		return;
	}

	$key  = sanitize_text_field( wp_unslash( $_POST['pa_remove_item'] ) );
	$item = WC()->cart->get_cart_item( $key );
	if ( $item && WC()->cart->remove_cart_item( $key ) ) {
		$product = wc_get_product( $item['product_id'] );
		wc_add_notice( prairie_ash_wording_with( 'removed_notice', array( 'coffee' => $product ? $product->get_name() : '' ) ), 'success' );
	}

	wp_safe_redirect( wc_get_cart_url() );
	exit;
}
add_action( 'wp_loaded', 'prairie_ash_remove_cart_line', 15 );

/**
 * A discount as the design writes a price, with a minus sign.
 *
 * @since 1.2.1
 *
 * @param float|string $amount Discount, positive or negative.
 * @return string For example "-$10.00".
 */
function prairie_ash_discount_text( $amount ) {
	return prairie_ash_price_text( -1 * abs( (float) $amount ) );
}

/**
 * The rows between the subtotal and the shipping of a cart: coupons and fees.
 *
 * Without them the rows of the summary do not add up to the total. Amounts are
 * before tax, as the subtotal is, and the tax row carries the rest.
 *
 * @since 1.2.1
 *
 * @param WC_Cart $cart The cart.
 * @return array<int, array{0: string, 1: string}> Label and value of each row.
 */
function prairie_ash_cart_adjustment_rows( $cart ) {
	$rows = array();

	foreach ( array_keys( $cart->get_coupons() ) as $code ) {
		$rows[] = array(
			prairie_ash_wording_with( 'discount_row', array( 'code' => wc_strtoupper( (string) $code ) ) ),
			prairie_ash_discount_text( $cart->get_coupon_discount_amount( (string) $code, true ) ),
		);
	}

	foreach ( $cart->get_fees() as $fee ) {
		$rows[] = array( (string) $fee->name, prairie_ash_price_text( $fee->total ) );
	}

	return $rows;
}
