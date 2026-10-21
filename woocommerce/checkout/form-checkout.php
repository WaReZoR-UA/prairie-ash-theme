<?php
/**
 * The checkout, in the design's markup: error summary, title with the total,
 * your details, delivery or pickup, payment, and the review with the consent
 * and the place order button.
 *
 * The form is WooCommerce's checkout form: its fields carry WooCommerce's keys,
 * its script refreshes the parts marked as fragments, and its handler places
 * the order.
 *
 * Overrides WooCommerce's checkout/form-checkout.php.
 *
 * @package PrairieAsh
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

$checkout = WC()->checkout();

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'prairie-ash' ) ) );
	return;
}

do_action( 'woocommerce_before_checkout_form', $checkout );
?>
<form name="checkout" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" method="post" novalidate data-error-summary enctype="multipart/form-data">
	<section class="checkout-errors" aria-label="<?php echo esc_attr( prairie_ash_wording( 'errors_label' ) ); ?>">
		<div class="checkout-errors__container">
			<div class="error-summary error-summary--panel" role="alert" data-error-summary-box hidden tabindex="-1">
				<h2 class="error-summary__title"><?php echo esc_html( prairie_ash_wording( 'errors_title' ) ); ?></h2>
				<ul class="error-summary__list" data-error-summary-list></ul>
			</div>
		</div>
	</section>

	<section class="checkout-title" aria-labelledby="checkout-title">
		<div class="checkout-title__container" data-motion="sign">
			<div class="checkout-title__text">
				<p class="checkout-title__eyebrow"><?php echo esc_html( prairie_ash_wording_with( 'checkout_eyebrow', array( 'count' => prairie_ash_cart_count_words() ) ) ); ?></p>
				<h1 class="checkout-title__heading" id="checkout-title"><?php echo esc_html( prairie_ash_wording( 'checkout_heading' ) ); ?></h1>
				<p class="checkout-title__note"><?php echo esc_html( prairie_ash_wording( 'checkout_note' ) ); ?></p>
			</div>
			<?php wc_get_template( 'checkout/pa-title-total.php' ); ?>
		</div>
	</section>

	<section class="checkout-step band band--wall" aria-labelledby="checkout-details-title">
		<div class="checkout-step__container">
			<div class="checkout-step__text">
				<h2 class="checkout-step__title" id="checkout-details-title"><?php echo esc_html( prairie_ash_wording( 'details_title' ) ); ?></h2>
				<p class="checkout-step__note"><?php echo esc_html( prairie_ash_wording( 'details_note' ) ); ?></p>
			</div>
			<div class="checkout-step__panel">
				<div class="checkout-fields">
					<?php prairie_ash_checkout_input( 'billing_email' ); ?>
					<div class="checkout-fields__row">
						<?php prairie_ash_checkout_input( 'billing_first_name' ); ?>
						<?php prairie_ash_checkout_input( 'billing_last_name' ); ?>
					</div>
					<?php prairie_ash_checkout_input( 'billing_phone' ); ?>
				</div>
			</div>
		</div>
	</section>

	<section class="checkout-step band band--deep" aria-labelledby="checkout-delivery-title">
		<div class="checkout-step__container">
			<div class="checkout-step__text">
				<h2 class="checkout-step__title" id="checkout-delivery-title"><?php echo esc_html( prairie_ash_wording( 'delivery_title' ) ); ?></h2>
				<p class="checkout-step__note"><?php echo esc_html( prairie_ash_wording( 'delivery_note' ) ); ?></p>
			</div>
			<div class="checkout-step__panel">
				<div class="checkout-fields">
					<?php wc_get_template( 'checkout/pa-delivery-options.php' ); ?>
					<div class="checkout-fields__group" data-address-fields<?php echo prairie_ash_chosen_pickup() ? ' hidden' : ''; ?>>
						<?php prairie_ash_checkout_input( 'billing_address_1' ); ?>
						<div class="checkout-fields__row checkout-fields__row--stack">
							<?php prairie_ash_checkout_input( 'billing_city' ); ?>
							<?php prairie_ash_checkout_input( 'billing_postcode', prairie_ash_wording( 'hint_postcode' ) ); ?>
						</div>
						<?php prairie_ash_checkout_input( 'billing_state' ); ?>
					</div>
					<input type="hidden" name="billing_country" id="billing_country" value="CA">
				</div>
			</div>
		</div>
	</section>

	<section class="checkout-step band band--wall" aria-labelledby="checkout-payment-title">
		<div class="checkout-step__container">
			<div class="checkout-step__text">
				<h2 class="checkout-step__title" id="checkout-payment-title"><?php echo esc_html( prairie_ash_wording( 'payment_title' ) ); ?></h2>
				<p class="checkout-step__note"><?php echo esc_html( prairie_ash_wording( 'payment_note' ) ); ?></p>
			</div>
			<?php woocommerce_checkout_payment(); ?>
		</div>
	</section>

	<section class="checkout-review" aria-labelledby="checkout-review-title">
		<div class="checkout-review__container">
			<?php woocommerce_order_review(); ?>
			<div class="checkout-review__submit">
				<div class="checkbox">
					<input class="checkbox__input" id="checkout-consent" type="checkbox" name="roast_notes" value="yes" aria-describedby="checkout-consent-sender">
					<label class="checkbox__label" for="checkout-consent">
						<span class="checkbox__text"><?php echo esc_html( prairie_ash_wording( 'consent_label' ) ); ?></span>
					</label>
				</div>
				<p class="checkout-review__small" id="checkout-consent-sender"><?php echo esc_html( prairie_ash_wording( 'consent_sender' ) ); ?></p>
				<p class="checkout-review__small"><?php echo esc_html( prairie_ash_wording( 'privacy_note' ) ); ?></p>
				<?php prairie_ash_link( prairie_ash_wording( 'privacy_link' ), get_privacy_policy_url(), 'text-link text-link--on-paper' ); ?>
				<?php wc_get_template( 'checkout/pa-place-order.php' ); ?>
			</div>
		</div>
	</section>
	<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
</form>
<?php
do_action( 'woocommerce_after_checkout_form', $checkout );
