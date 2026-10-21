<?php
/**
 * Order received, in the design's markup: the confirmation, what happens next,
 * and the receipt.
 *
 * The next step depends on the delivery: a shipped order leaves on the next
 * roast day after the order, a pickup is ready at the bar from that day.
 *
 * Overrides WooCommerce's checkout/thankyou.php.
 *
 * @package PrairieAsh
 * @version 11.2.0
 *
 * @var WC_Order|false $order The order.
 */

defined( 'ABSPATH' ) || exit;

if ( ! $order || $order->has_status( 'failed' ) ) {
	wc_get_template( 'checkout/order-received.php', array( 'order' => $order ) );
	return;
}

$prairie_ash_placed = $order->get_date_created() ? $order->get_date_created() : new WC_DateTime();
$prairie_ash_next   = prairie_ash_next_roast_day( $prairie_ash_placed );
$prairie_ash_pickup = false;
foreach ( $order->get_shipping_methods() as $prairie_ash_method ) {
	$prairie_ash_pickup = $prairie_ash_pickup || prairie_ash_is_pickup( $prairie_ash_method->get_method_id() );
}
$prairie_ash_values = array(
	'date'   => wp_date( 'l, F j, Y', $prairie_ash_placed->getTimestamp() ),
	'next'   => wp_date( 'l, F j, Y', $prairie_ash_next->getTimestamp() ),
	'number' => $order->get_order_number(),
	'email'  => $order->get_billing_email(),
	'method' => $order->get_payment_method_title(),
);
$prairie_ash_lines  = array();
foreach ( $order->get_items() as $prairie_ash_item ) {
	$prairie_ash_product = $prairie_ash_item->get_product();
	if ( ! $prairie_ash_product ) {
		continue;
	}
	$prairie_ash_parent  = $prairie_ash_product->get_parent_id() ? wc_get_product( $prairie_ash_product->get_parent_id() ) : $prairie_ash_product;
	$prairie_ash_lines[] = array(
		'name'     => $prairie_ash_parent->get_name(),
		'image'    => $prairie_ash_product->get_image_id() ? $prairie_ash_product->get_image_id() : $prairie_ash_parent->get_image_id(),
		'details'  => prairie_ash_bag_details( $prairie_ash_product ),
		'quantity' => $prairie_ash_item->get_quantity(),
		'total'    => prairie_ash_price_text( $prairie_ash_item->get_total() ),
	);
}
$prairie_ash_ship = (float) $order->get_shipping_total();
?>
<section class="confirmation band band--wall" aria-labelledby="confirmation-title">
	<div class="confirmation__container">
		<div class="confirmation__message">
			<div class="confirmation__heading" data-motion="sign">
				<p class="confirmation__eyebrow"><?php echo esc_html( prairie_ash_wording_with( 'received_eyebrow', $prairie_ash_values ) ); ?></p>
				<h1 class="confirmation__title" id="confirmation-title"><?php echo esc_html( prairie_ash_wording_with( 'received_title', $prairie_ash_values ) ); ?></h1>
				<p class="confirmation__note"><?php echo esc_html( prairie_ash_wording_with( $prairie_ash_pickup ? 'received_note_pickup' : 'received_note_ship', $prairie_ash_values ) ); ?></p>
			</div>
			<div class="confirmation__steps">
				<h2 class="confirmation__steps-title"><?php echo esc_html( prairie_ash_wording( 'next_title' ) ); ?></h2>
				<dl class="lv lv--fact lv--stack lv--loose">
					<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( $prairie_ash_pickup ? 'pickup' : 'shipping' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_wording_with( $prairie_ash_pickup ? 'next_pickup' : 'next_ship', $prairie_ash_values ) ); ?></dd></div>
					<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'questions_label' ) ); ?></dt><dd class="lv__value"><?php prairie_ash_contact_text( prairie_ash_wording_with( 'questions_text', array( 'number' => $prairie_ash_values['number'] ) ) ); ?></dd></div>
				</dl>
			</div>
			<div class="confirmation__links">
				<a class="text-link text-link--on-wall" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php echo esc_html( prairie_ash_wording( 'shop_more' ) ); ?></a>
				<?php prairie_ash_link( prairie_ash_wording( 'hours_link' ), prairie_ash_setting( 'directions_link' ), 'text-link text-link--on-wall' ); ?>
			</div>
		</div>
		<div class="confirmation__receipt">
			<h2 class="confirmation__receipt-title"><?php echo esc_html( prairie_ash_wording( 'receipt_title' ) ); ?></h2>
			<ul class="confirmation__lines">
				<?php foreach ( $prairie_ash_lines as $prairie_ash_line ) : ?>
					<li class="receipt-line">
						<?php if ( $prairie_ash_line['image'] ) : ?>
							<div class="receipt-line__thumb media-ibg">
								<?php prairie_ash_image( $prairie_ash_line['image'], prairie_ash_slot_sizes( 'receipt-line__thumb' ), '', true ); ?>
							</div>
						<?php endif; ?>
						<div class="receipt-line__text">
							<p class="receipt-line__name"><?php echo esc_html( $prairie_ash_line['name'] ); ?></p>
							<p class="receipt-line__details"><?php prairie_ash_figure_text( $prairie_ash_line['details'] . ', ' . strtolower( prairie_ash_wording( 'quantity' ) ) . ' ' . $prairie_ash_line['quantity'] ); ?></p>
						</div>
						<p class="receipt-line__total"><span class="visually-hidden"><?php echo esc_html( prairie_ash_wording( 'col_total' ) ); ?> </span><?php echo esc_html( $prairie_ash_line['total'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<dl class="lv lv--total">
				<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'subtotal' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_price_text( $order->get_subtotal() ) ); ?></dd></div>
				<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( $prairie_ash_pickup ? 'pickup' : 'shipping' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( $prairie_ash_ship > 0 ? prairie_ash_price_text( $prairie_ash_ship ) : prairie_ash_wording( 'free' ) ); ?></dd></div>
				<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'tax' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_price_text( $order->get_total_tax() ) ); ?></dd></div>
				<div class="lv__row lv__row--paid"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'total_paid' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_price_text( $order->get_total() ) ); ?></dd></div>
			</dl>
			<p class="confirmation__payment"><?php echo esc_html( prairie_ash_wording_with( 'payment_line', $prairie_ash_values ) ); ?></p>
		</div>
	</div>
</section>
