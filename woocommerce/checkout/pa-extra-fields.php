<?php
/**
 * The checkout fields WooCommerce asks for that the design does not draw.
 *
 * The design draws its own fields, see prairie_ash_checkout_input(). A field a
 * WooCommerce setting or an extension adds, such as the account fields when an
 * account is required, is validated by WooCommerce whether it is shown or not,
 * so it is printed here with WooCommerce's own field markup, between the hooks
 * WooCommerce's billing form, account form and order notes fire around theirs.
 * With nothing added and no account offered, nothing is printed.
 *
 * @package PrairieAsh
 *
 * @var WC_Checkout $checkout The checkout.
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_drawn = array_merge( array_keys( prairie_ash_checkout_field_list() ), array( 'billing_country' ) );

do_action( 'woocommerce_before_checkout_billing_form', $checkout );
foreach ( array( 'billing', 'shipping', 'order' ) as $prairie_ash_group ) {
	foreach ( (array) $checkout->get_checkout_fields( $prairie_ash_group ) as $prairie_ash_key => $prairie_ash_field ) {
		if ( ! in_array( $prairie_ash_key, $prairie_ash_drawn, true ) ) {
			woocommerce_form_field( $prairie_ash_key, $prairie_ash_field, $checkout->get_value( $prairie_ash_key ) );
		}
	}
}
do_action( 'woocommerce_after_checkout_billing_form', $checkout );

if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) :
	?>
	<?php if ( ! $checkout->is_registration_required() ) : ?>
		<div class="checkbox">
			<input class="checkbox__input" id="createaccount" type="checkbox" name="createaccount" value="1"<?php checked( true === $checkout->get_value( 'createaccount' ) || true === apply_filters( 'woocommerce_create_account_default_checked', false ) ); ?>>
			<label class="checkbox__label" for="createaccount">
				<span class="checkbox__text"><?php echo esc_html( prairie_ash_wording( 'create_account' ) ); ?></span>
			</label>
		</div>
	<?php endif; ?>
	<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>
	<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
		<div class="create-account">
			<?php
			foreach ( $checkout->get_checkout_fields( 'account' ) as $prairie_ash_key => $prairie_ash_field ) {
				woocommerce_form_field( $prairie_ash_key, $prairie_ash_field, $checkout->get_value( $prairie_ash_key ) );
			}
			?>
		</div>
	<?php endif; ?>
	<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	<?php
endif;

do_action( 'woocommerce_before_order_notes', $checkout );
do_action( 'woocommerce_after_order_notes', $checkout );
