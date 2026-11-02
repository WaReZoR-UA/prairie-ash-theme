<?php
/**
 * The order review: one line per coffee and the totals.
 *
 * WooCommerce's checkout script replaces this block, found by its
 * checkout-review__order class, when a change of the order changes it, see
 * prairie_ash_checkout_fragments(). The block leaves out WooCommerce's
 * woocommerce-checkout-review-order-table class, so the script never lays its
 * busy shade over the receipt the motion layer prints.
 *
 * Overrides WooCommerce's checkout/review-order.php.
 *
 * @package PrairieAsh
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_cart  = WC()->cart;
$prairie_ash_lines = prairie_ash_cart_lines( $prairie_ash_cart->get_cart() );
?>
<div class="checkout-review__order">
	<h2 class="checkout-review__title" id="checkout-review-title"><?php echo esc_html( prairie_ash_wording( 'review_title' ) ); ?></h2>
	<div class="review-table">
		<div class="review-table__head" aria-hidden="true">
			<span class="review-table__col review-table__col--coffee"><?php echo esc_html( prairie_ash_wording( 'col_coffee' ) ); ?></span>
			<span class="review-table__col review-table__col--quantity"><?php echo esc_html( prairie_ash_wording( 'quantity' ) ); ?></span>
			<span class="review-table__col review-table__col--total"><?php echo esc_html( prairie_ash_wording( 'col_total' ) ); ?></span>
		</div>
		<ul class="review-table__list">
			<?php foreach ( $prairie_ash_lines as $prairie_ash_line ) : ?>
				<li class="review-line">
					<?php if ( $prairie_ash_line['image'] ) : ?>
						<div class="review-line__thumb media-ibg">
							<?php prairie_ash_image( $prairie_ash_line['image'], prairie_ash_slot_sizes( 'review-line__thumb' ), '', true ); ?>
						</div>
					<?php endif; ?>
					<div class="review-line__text">
						<p class="review-line__name"><?php echo esc_html( $prairie_ash_line['name'] ); ?></p>
						<p class="review-line__details"><?php prairie_ash_figure_text( $prairie_ash_line['details'] ); ?><span class="review-line__quantity-inline">, <?php echo esc_html( strtolower( prairie_ash_wording( 'quantity' ) ) ); ?> <span class="figure"><?php echo esc_html( $prairie_ash_line['quantity'] ); ?></span></span></p>
					</div>
					<p class="review-line__quantity"><span class="visually-hidden"><?php echo esc_html( prairie_ash_wording( 'quantity' ) ); ?> </span><?php echo esc_html( $prairie_ash_line['quantity'] ); ?></p>
					<p class="review-line__total"><span class="visually-hidden"><?php echo esc_html( prairie_ash_wording( 'col_total' ) ); ?> </span><?php echo esc_html( $prairie_ash_line['total'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="checkout-review__receipt" data-motion="cart-receipt">
		<dl class="lv lv--total">
			<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'subtotal' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_price_text( $prairie_ash_cart->get_subtotal() ) ); ?></dd></div>
			<?php if ( $prairie_ash_cart->needs_shipping() ) : ?>
				<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_chosen_pickup() ? prairie_ash_wording( 'pickup' ) : prairie_ash_wording( 'shipping' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_shipping_value() ); ?></dd></div>
			<?php endif; ?>
			<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'tax' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_price_text( $prairie_ash_cart->get_total_tax() ) ); ?></dd></div>
			<div class="lv__row lv__row--due"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'total_to_pay' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_price_text( $prairie_ash_cart->get_total( 'edit' ) ) ); ?></dd></div>
		</dl>
	</div>
</div>
