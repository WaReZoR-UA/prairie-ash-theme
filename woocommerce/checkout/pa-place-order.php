<?php
/**
 * The place order button, whose label carries the total, and the way back.
 *
 * A fragment, refreshed with the order review. The button keeps WooCommerce's
 * id and name, which its checkout script and handler look for, and carries its
 * label in data-value, which the script restores when the payment method
 * changes. It also carries the fingerprint of the checkout parts, so a refresh
 * sends back only parts that changed, see prairie_ash_checkout_fragments().
 * Payment methods print express buttons and notes on the hooks around it, as
 * they do around WooCommerce's own button.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_hash  = isset( $prairie_ash_parts_hash ) ? (string) $prairie_ash_parts_hash : prairie_ash_checkout_parts_hash();
$prairie_ash_label = prairie_ash_wording_with( 'place_order', array( 'total' => prairie_ash_price_text( WC()->cart->get_total( 'edit' ) ) ) );
?>
<div class="checkout-review__actions">
	<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
	<button class="button button--primary" type="submit" id="place_order" name="woocommerce_checkout_place_order" value="<?php echo esc_attr( $prairie_ash_label ); ?>" data-value="<?php echo esc_attr( $prairie_ash_label ); ?>"><?php echo esc_html( $prairie_ash_label ); ?></button>
	<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
	<a class="text-link text-link--on-paper" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php echo esc_html( prairie_ash_wording( 'back_to_cart' ) ); ?></a>
	<input type="hidden" name="pa_checkout_parts" value="<?php echo esc_attr( $prairie_ash_hash ); ?>">
</div>
