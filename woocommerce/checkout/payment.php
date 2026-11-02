<?php
/**
 * The payment panel: each payment method WooCommerce offers, with the fields
 * its provider draws, inside the design's shaded panel.
 *
 * The provider's own fields replace the card fields the design drew as a
 * placeholder. The place order button lives in the review below, not here.
 * WooCommerce's checkout script replaces this block, found by its id, when a
 * change of the order changes it, see prairie_ash_checkout_fragments(). The
 * block leaves out WooCommerce's woocommerce-checkout-payment class, so the
 * script never lays its busy shade over the panel the motion layer writes in.
 *
 * Overrides WooCommerce's checkout/payment.php.
 *
 * @package PrairieAsh
 * @version 10.9.0
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_gateways = WC()->cart->needs_payment() ? WC()->payment_gateways()->get_available_payment_gateways() : array();
$prairie_ash_chosen   = WC()->session ? WC()->session->get( 'chosen_payment_method' ) : '';
if ( ! isset( $prairie_ash_gateways[ $prairie_ash_chosen ] ) ) {
	$prairie_ash_chosen = (string) array_key_first( $prairie_ash_gateways );
}
?>
<div id="payment" class="checkout-step__panel checkout-step__panel--shade" data-payment-fields data-motion="checkout-fields">
	<?php if ( ! $prairie_ash_gateways ) : ?>
		<div class="checkout-fields">
			<p class="checkout-fields__text"><?php echo esc_html( prairie_ash_wording( 'no_payment' ) ); ?></p>
		</div>
	<?php endif; ?>
	<?php foreach ( $prairie_ash_gateways as $prairie_ash_gateway ) : ?>
		<div class="checkout-fields">
			<input id="payment_method_<?php echo esc_attr( $prairie_ash_gateway->id ); ?>" class="visually-hidden" type="radio" name="payment_method" value="<?php echo esc_attr( $prairie_ash_gateway->id ); ?>"<?php checked( $prairie_ash_gateway->id, $prairie_ash_chosen ); ?> data-order_button_text="<?php echo esc_attr( $prairie_ash_gateway->order_button_text ); ?>">
			<label class="checkout-fields__label" for="payment_method_<?php echo esc_attr( $prairie_ash_gateway->id ); ?>"><?php echo esc_html( $prairie_ash_gateway->get_title() ); ?></label>
			<?php if ( $prairie_ash_gateway->has_fields() || $prairie_ash_gateway->get_description() ) : ?>
				<div class="checkout-fields__text payment_box payment_method_<?php echo esc_attr( $prairie_ash_gateway->id ); ?>">
					<?php $prairie_ash_gateway->payment_fields(); ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
