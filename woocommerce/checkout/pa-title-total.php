<?php
/**
 * The total beside the checkout title, shown before any field is filled in.
 *
 * A fragment, refreshed with the order review.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_cart = WC()->cart;
$prairie_ash_ship = (float) $prairie_ash_cart->get_shipping_total();
?>
<div class="checkout-title__total">
	<p class="checkout-title__total-row"><span class="checkout-title__total-label"><?php echo esc_html( prairie_ash_wording( 'total_to_pay' ) ); ?></span> <span class="checkout-title__total-value"><?php echo esc_html( prairie_ash_price_text( $prairie_ash_cart->get_total( 'edit' ) ) ); ?></span></p>
	<p class="checkout-title__total-note">
		<?php
		echo esc_html(
			prairie_ash_wording_with(
				'checkout_total_note',
				array(
					'count'    => ucfirst( prairie_ash_cart_count_words() ),
					'shipping' => prairie_ash_chosen_pickup() ? prairie_ash_wording( 'pickup_free' ) : ( $prairie_ash_ship > 0 ? prairie_ash_wording( 'shipping' ) . ' ' . prairie_ash_price_text( $prairie_ash_ship ) : prairie_ash_wording( 'shipping_free' ) ),
					'tax'      => prairie_ash_price_text( $prairie_ash_cart->get_total_tax() ),
				)
			)
		);
		?>
	</p>
</div>
