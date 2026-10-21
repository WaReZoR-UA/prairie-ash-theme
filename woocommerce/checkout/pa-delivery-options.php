<?php
/**
 * The delivery choice: one option per shipping rate WooCommerce offers for the
 * customer's address, ship or pick up, with the cost of each beneath it.
 *
 * A fragment: WooCommerce's checkout script replaces it whenever the order
 * review refreshes, so the cost under "Ship to my address" follows the total.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_packages = WC()->shipping()->get_packages();
$prairie_ash_rates    = empty( $prairie_ash_packages[0]['rates'] ) ? array() : $prairie_ash_packages[0]['rates'];
$prairie_ash_chosen   = (array) WC()->session->get( 'chosen_shipping_methods' );
$prairie_ash_current  = isset( $prairie_ash_chosen[0] ) ? $prairie_ash_chosen[0] : '';
if ( ! isset( $prairie_ash_rates[ $prairie_ash_current ] ) && $prairie_ash_rates ) {
	$prairie_ash_current = (string) array_key_first( $prairie_ash_rates );
}
?>
<div class="options checkout-choice" role="radiogroup" aria-labelledby="checkout-delivery-title">
	<?php if ( ! $prairie_ash_rates ) : ?>
		<p class="checkout-choice__note"><?php echo esc_html( prairie_ash_wording( 'no_delivery' ) ); ?></p>
	<?php endif; ?>
	<?php foreach ( $prairie_ash_rates as $prairie_ash_rate_id => $prairie_ash_rate ) : ?>
		<?php
		$prairie_ash_input  = 'delivery-' . sanitize_html_class( str_replace( ':', '-', $prairie_ash_rate_id ) );
		$prairie_ash_pickup = prairie_ash_is_pickup( $prairie_ash_rate->get_method_id() );
		$prairie_ash_cost   = (float) $prairie_ash_rate->get_cost();
		if ( $prairie_ash_pickup ) {
			$prairie_ash_note = prairie_ash_wording_with( 'pickup_note', array( 'address' => prairie_ash_setting( 'street' ) ) );
		} else {
			$prairie_ash_note = prairie_ash_wording_with( 'ship_note', array( 'cost' => $prairie_ash_cost > 0 ? prairie_ash_price_text( $prairie_ash_cost ) : prairie_ash_wording( 'free_on_order' ) ) );
		}
		?>
		<div class="options__item checkout-choice__item">
			<input id="<?php echo esc_attr( $prairie_ash_input ); ?>" class="options__input shipping_method" type="radio" name="shipping_method[0]" data-index="0" value="<?php echo esc_attr( $prairie_ash_rate_id ); ?>"<?php checked( $prairie_ash_rate_id, $prairie_ash_current ); ?> aria-describedby="<?php echo esc_attr( $prairie_ash_input . '-note' ); ?>" data-pickup="<?php echo $prairie_ash_pickup ? '1' : '0'; ?>">
			<label for="<?php echo esc_attr( $prairie_ash_input ); ?>" class="options__label"><span class="options__text"><?php echo esc_html( $prairie_ash_rate->get_label() ); ?></span></label>
			<p class="checkout-choice__note" id="<?php echo esc_attr( $prairie_ash_input . '-note' ); ?>"><?php echo esc_html( $prairie_ash_note ); ?></p>
		</div>
	<?php endforeach; ?>
</div>
