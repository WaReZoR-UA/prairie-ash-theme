<?php
/**
 * Checkout: the design's fields mapped onto WooCommerce's, delivery or pickup,
 * the error summary, and the roast notes consent.
 *
 * WooCommerce still validates and places the order. The theme decides which of
 * its fields exist, what they are called, when the address is required, and
 * which parts of the page refresh when the delivery choice or the total changes.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * The checkout fields the design draws, with WooCommerce's own keys.
 *
 * Each entry: wording key of the label, type, autocomplete, whether it is
 * required, and the wording key of its error message. The address fields are
 * required only for delivery, which prairie_ash_checkout_validate() decides.
 *
 * @since 1.0.0
 *
 * @return array<string, array>
 */
function prairie_ash_checkout_field_list() {
	return array(
		'billing_email'      => array( 'label_email', 'email', 'email', true, 'error_email' ),
		'billing_first_name' => array( 'label_first_name', 'text', 'given-name', true, 'error_first_name' ),
		'billing_last_name'  => array( 'label_last_name', 'text', 'family-name', true, 'error_last_name' ),
		'billing_phone'      => array( 'label_phone', 'tel', 'tel', false, '' ),
		'billing_address_1'  => array( 'label_street', 'text', 'street-address', false, 'error_street' ),
		'billing_city'       => array( 'label_city', 'text', 'address-level2', false, 'error_city' ),
		'billing_postcode'   => array( 'label_postcode', 'text', 'postal-code', false, 'error_postcode' ),
		'billing_state'      => array( 'label_province', 'text', 'address-level1', false, 'error_province' ),
	);
}

/**
 * Narrow WooCommerce's checkout fields to the design's set.
 *
 * Company, the second address line, the shipping address and the order notes
 * are not in the design. The country is Canada, the only country the shop
 * sells to, and is posted as a hidden value.
 *
 * @since 1.0.0
 *
 * @param array $fields Checkout fields by group.
 * @return array
 */
function prairie_ash_checkout_fields( $fields ) {
	$billing = array();

	foreach ( prairie_ash_checkout_field_list() as $key => $field ) {
		$billing[ $key ] = array(
			'label'        => prairie_ash_wording( $field[0] ),
			'type'         => $field[1],
			'required'     => $field[3],
			'autocomplete' => $field[2],
			'validate'     => 'billing_email' === $key ? array( 'email' ) : ( 'billing_postcode' === $key ? array( 'postcode' ) : array() ),
		);
	}
	$billing['billing_country'] = array(
		'label'    => '',
		'type'     => 'hidden',
		'required' => true,
		'default'  => 'CA',
	);

	$fields['billing']  = $billing;
	$fields['shipping'] = array();
	$fields['order']    = array();

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'prairie_ash_checkout_fields', 100 );

/**
 * Whether the chosen delivery is pickup at the cafe.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function prairie_ash_chosen_pickup() {
	$chosen = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods' ) : array();
	$first  = isset( $chosen[0] ) ? (string) $chosen[0] : '';

	return prairie_ash_is_pickup( strtok( $first, ':' ) );
}

/**
 * Require the address for delivery, and write every error in the design's words.
 *
 * WooCommerce's own messages name the field and its rule. The design writes one
 * plain instruction per field, the same one its form script shows beside the
 * field, so the summary and the field agree.
 *
 * @since 1.0.0
 *
 * @param array    $data   Posted checkout data.
 * @param WP_Error $errors Validation errors.
 * @return void
 */
function prairie_ash_checkout_validate( $data, $errors ) {
	$list   = prairie_ash_checkout_field_list();
	$pickup = isset( $data['shipping_method'][0] ) && prairie_ash_is_pickup( strtok( (string) $data['shipping_method'][0], ':' ) );

	if ( ! $pickup ) {
		foreach ( array( 'billing_address_1', 'billing_city', 'billing_postcode', 'billing_state' ) as $key ) {
			if ( '' === trim( (string) ( $data[ $key ] ?? '' ) ) && ! $errors->get_error_data( $key . '_required' ) ) {
				$errors->add( $key . '_required', prairie_ash_wording( $list[ $key ][4] ), array( 'id' => $key ) );
			}
		}
	}

	// Rewrite WooCommerce's messages into the design's, keyed by the field each one belongs to.
	foreach ( $errors->get_error_codes() as $code ) {
		$error_data = $errors->get_error_data( $code );
		$key        = is_array( $error_data ) && isset( $error_data['id'] ) ? $error_data['id'] : '';
		if ( isset( $list[ $key ] ) && '' !== $list[ $key ][4] ) {
			$errors->remove( $code );
			$errors->add( $code, prairie_ash_wording( $list[ $key ][4] ), array( 'id' => $key ) );
		}
	}
}
add_action( 'woocommerce_after_checkout_validation', 'prairie_ash_checkout_validate', 10, 2 );

/**
 * Keep the optional roast notes consent with the order.
 *
 * Unticked by default, and recorded either way with the time, so the shop can
 * show when consent was given.
 *
 * @since 1.0.0
 *
 * @param WC_Order $order The order being created.
 * @param array    $data  Posted checkout data.
 * @return void
 */
function prairie_ash_checkout_consent( $order, $data ) {
	unset( $data );
	// WooCommerce verified the checkout nonce before creating the order.
	$consent = filter_input( INPUT_POST, 'roast_notes', FILTER_UNSAFE_RAW );

	$order->update_meta_data( '_pa_roast_notes_consent', 'yes' === $consent ? 'yes' : 'no' );
	$order->update_meta_data( '_pa_roast_notes_consent_time', gmdate( 'c' ) );
}
add_action( 'woocommerce_checkout_create_order', 'prairie_ash_checkout_consent', 10, 2 );

/**
 * Show the consent on the order screen in the admin.
 *
 * @since 1.0.0
 *
 * @param WC_Order $order The order.
 * @return void
 */
function prairie_ash_admin_consent( $order ) {
	printf(
		'<p><strong>%1$s</strong> %2$s</p>',
		esc_html__( 'Roast notes consent:', 'prairie-ash' ),
		esc_html( 'yes' === $order->get_meta( '_pa_roast_notes_consent' ) ? __( 'given at checkout', 'prairie-ash' ) : __( 'not given', 'prairie-ash' ) )
	);
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'prairie_ash_admin_consent' );

/**
 * The parts of the checkout that change with the delivery choice and the total.
 *
 * Each part is printed by its template, keyed by the selector WooCommerce's
 * checkout script replaces it by: the total beside the title, the delivery
 * options, the payment panel and the order review. The place order button,
 * whose label carries the total, is refreshed with them and carries their
 * fingerprint, see prairie_ash_checkout_parts_hash().
 *
 * @since 1.1.0
 *
 * @return array<string, string> Selector to HTML.
 */
function prairie_ash_checkout_parts() {
	$parts = array();

	ob_start();
	wc_get_template( 'checkout/pa-title-total.php' );
	$parts['.checkout-title__total'] = ob_get_clean();

	ob_start();
	wc_get_template( 'checkout/pa-delivery-options.php' );
	$parts['.checkout-choice'] = ob_get_clean();

	ob_start();
	woocommerce_checkout_payment();
	$parts['#payment'] = ob_get_clean();

	ob_start();
	woocommerce_order_review();
	$parts['.checkout-review__order'] = ob_get_clean();

	return $parts;
}

/**
 * The fingerprint of the checkout parts as they stand.
 *
 * The page prints it beside the place order button. WooCommerce's script
 * asks for the parts again as soon as the page loads, and replaces every part
 * it receives on that first answer, even an unchanged one: the motion layer
 * has already taken those elements over, so a replaced part would lose its
 * motion and a few frames under the busy shade. With the fingerprint, only
 * parts that changed are sent back.
 *
 * @since 1.1.0
 *
 * @param array<string, string>|null $parts Selector to HTML, or null to print them now.
 * @return string
 */
function prairie_ash_checkout_parts_hash( $parts = null ) {
	return md5( implode( "\n", null === $parts ? prairie_ash_checkout_parts() : $parts ) );
}

/**
 * Refresh the parts of the design that depend on the delivery choice and the total.
 *
 * WooCommerce's own review table and payment box are replaced by the design's,
 * found by their own selectors, with the delivery options, the total beside
 * the title and the place order button. When none of them changed since the
 * page or the last refresh printed them, none is sent, so nothing on the page
 * is replaced.
 *
 * @since 1.0.0
 *
 * @param array $fragments Selector to HTML.
 * @return array
 */
function prairie_ash_checkout_fragments( $fragments ) {
	unset( $fragments['.woocommerce-checkout-review-order-table'], $fragments['.woocommerce-checkout-payment'] );

	$parts = prairie_ash_checkout_parts();
	$hash  = prairie_ash_checkout_parts_hash( $parts );

	$posted = array();
	$nonce  = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'update-order-review' ) && isset( $_POST['post_data'] ) ) {
		parse_str( sanitize_text_field( wp_unslash( $_POST['post_data'] ) ), $posted );
	}
	if ( isset( $posted['pa_checkout_parts'] ) && $hash === $posted['pa_checkout_parts'] ) {
		return $fragments;
	}

	ob_start();
	wc_get_template( 'checkout/pa-place-order.php', array( 'prairie_ash_parts_hash' => $hash ) );
	$parts['.checkout-review__actions'] = ob_get_clean();

	return array_merge( $fragments, $parts );
}
add_filter( 'woocommerce_update_order_review_fragments', 'prairie_ash_checkout_fragments' );

/**
 * The number of coffees in the cart, as the design words it.
 *
 * @since 1.0.0
 *
 * @return string For example "two coffees".
 */
function prairie_ash_cart_count_words() {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	$text  = 1 === $count ? prairie_ash_wording( 'cart_count_one' ) : str_replace( '[count]', prairie_ash_number_word( $count ), prairie_ash_wording( 'cart_count_many' ) );

	return strtolower( $text );
}

/**
 * The next roast day after a moment: the next Tuesday or Friday.
 *
 * @since 1.0.0
 *
 * @param DateTimeInterface $from Moment.
 * @return DateTimeImmutable
 */
function prairie_ash_next_roast_day( $from ) {
	$day = DateTimeImmutable::createFromInterface( $from )->setTimezone( wp_timezone() )->setTime( 0, 0 );

	do {
		$day = $day->modify( '+1 day' );
	} while ( ! in_array( (int) $day->format( 'w' ), array( 2, 5 ), true ) );

	return $day;
}

/**
 * Print one checkout field in the design's markup, with WooCommerce's key.
 *
 * The data attributes feed the design's own form script, which checks the
 * field before the order is sent and builds the error summary from the same
 * wording WooCommerce's validation prints after it. The script prints the
 * error beside the field as markup, so the error is escaped here, and the
 * format named in the hint is set as a figure in it, as the design sets it.
 * A required label carries its "(required)" as the design's mark, which the
 * motion layer stamps once the label is written.
 *
 * @since 1.0.0
 *
 * @param string $key  Field key, for example billing_email.
 * @param string $hint Hint shown under the label, for example the postal code format.
 * @return void
 */
function prairie_ash_checkout_input( $key, $hint = '' ) {
	$list = prairie_ash_checkout_field_list();
	if ( ! isset( $list[ $key ] ) ) {
		return;
	}

	list( $label, $type, $autocomplete, $required, $error ) = $list[ $key ];

	$address  = in_array( $key, array( 'billing_address_1', 'billing_city', 'billing_postcode', 'billing_state' ), true );
	$required = $required || $address;
	$code     = in_array( $type, array( 'tel' ), true ) || in_array( $key, array( 'billing_postcode' ), true );
	$message  = '';
	if ( $required && $error ) {
		$message = esc_html( prairie_ash_wording( 'error_prefix' ) . ' ' . prairie_ash_wording( $error ) );
		if ( preg_match( '/\[([^\]]+)\]/', $hint, $pattern ) ) {
			$message = str_replace( esc_html( $pattern[1] ), '<span class="figure">' . esc_html( $pattern[1] ) . '</span>', $message );
		}
	}
	$attrs = array(
		'class'            => $code ? 'input input--code' : 'input',
		'id'               => $key,
		'type'             => $type,
		'name'             => $key,
		'value'            => (string) WC()->checkout()->get_value( $key ),
		'autocomplete'     => $autocomplete,
		'aria-describedby' => '' !== $hint ? $key . '-hint' : '',
		'data-required'    => $required ? ( 'email' === $type ? 'email' : '' ) : null,
		'data-error'       => $message,
		'data-summary'     => $required && $error ? prairie_ash_wording( $error ) : '',
	);
	?>
	<div class="field">
		<?php if ( $required ) : ?>
			<label class="field__label" for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( prairie_ash_wording( $label ) ); ?> <span class="field__mark"><?php echo esc_html( prairie_ash_wording( 'required' ) ); ?></span></label>
		<?php else : ?>
			<label class="field__label" for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( prairie_ash_wording( $label ) . ' ' . prairie_ash_wording( 'optional' ) ); ?></label>
		<?php endif; ?>
		<?php if ( '' !== $hint ) : ?>
			<span class="field__hint field__hint--format" id="<?php echo esc_attr( $key . '-hint' ); ?>"><?php prairie_ash_hint_text( $hint ); ?></span>
		<?php endif; ?>
		<input<?php echo prairie_ash_input_attrs( $attrs ); ?>>
	</div>
	<?php
}

/**
 * Attributes of an input, with data-required printed bare when it has no value.
 *
 * @since 1.0.0
 *
 * @param array $attrs Attribute values by name. Null leaves the attribute out.
 * @return string Escaped attributes with a leading space.
 */
function prairie_ash_input_attrs( $attrs ) {
	$out = '';

	foreach ( $attrs as $name => $value ) {
		if ( null === $value || ( '' === $value && 'data-required' !== $name && 'value' !== $name ) ) {
			continue;
		}
		$out .= ( 'data-required' === $name && '' === $value ) ? ' data-required' : sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
	}

	return $out;
}

/**
 * Print a field hint, with the format inside it set as the design's pattern.
 *
 * The client writes the format in square brackets, for example
 * "Format [A1A 1A1]".
 *
 * @since 1.0.0
 *
 * @param string $hint Hint text.
 * @return void
 */
function prairie_ash_hint_text( $hint ) {
	echo wp_kses(
		(string) preg_replace( '/\[([^\]]+)\]/', '<span class="field__pattern">$1</span>', esc_html( $hint ) ),
		array( 'span' => array( 'class' => true ) )
	);
}

/**
 * Leave out WooCommerce's "cart is empty" notice on the empty cart.
 *
 * The design's empty cart says it in its own heading, so the notice would
 * only repeat it, in an element the design does not draw.
 *
 * @since 1.1.0
 *
 * @return void
 */
function prairie_ash_drop_empty_cart_notice() {
	remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
}
add_action( 'init', 'prairie_ash_drop_empty_cart_notice' );
