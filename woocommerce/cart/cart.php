<?php
/**
 * The cart, in the design's markup: the lines and the order summary.
 *
 * A quantity change submits the form to WooCommerce's own update handler, by
 * script as soon as the number changes, or with the update button that only a
 * visitor without JavaScript sees. Removing a line posts the line's key, which
 * the theme hands to WooCommerce's cart.
 *
 * Overrides WooCommerce's cart/cart.php.
 *
 * @package PrairieAsh
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_cart  = WC()->cart;
$prairie_ash_lines = prairie_ash_cart_lines( $prairie_ash_cart->get_cart() );

do_action( 'woocommerce_before_cart' );
?>
<section class="cart-items" aria-labelledby="cart-title">
	<div class="cart-items__container">
		<div class="cart-items__title" data-motion="sign">
			<p class="cart-items__eyebrow"><?php echo esc_html( prairie_ash_cart_eyebrow( $prairie_ash_lines ) ); ?></p>
			<h1 class="cart-items__heading" id="cart-title"><?php echo esc_html( prairie_ash_wording( 'cart_heading' ) ); ?></h1>
		</div>
		<form class="cart-table woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" data-cart-form>
			<div class="cart-table__head" aria-hidden="true">
				<span class="cart-table__col cart-table__col--coffee"><?php echo esc_html( prairie_ash_wording( 'col_coffee' ) ); ?></span>
				<span class="cart-table__col cart-table__col--each"><?php echo esc_html( prairie_ash_wording( 'col_each' ) ); ?></span>
				<span class="cart-table__col cart-table__col--total"><?php echo esc_html( prairie_ash_wording( 'col_total' ) ); ?></span>
			</div>
			<ul class="cart-table__list">
				<?php foreach ( $prairie_ash_lines as $prairie_ash_line ) : ?>
					<li class="cart-line">
						<?php if ( $prairie_ash_line['image'] ) : ?>
							<div class="cart-line__thumb media-ibg">
								<?php prairie_ash_image( $prairie_ash_line['image'], prairie_ash_slot_sizes( 'cart-line__thumb' ), '', true ); ?>
							</div>
						<?php endif; ?>
						<div class="cart-line__text">
							<p class="cart-line__name"><?php echo esc_html( $prairie_ash_line['name'] ); ?></p>
							<p class="cart-line__details"><?php prairie_ash_figure_text( $prairie_ash_line['details'] ); ?><span class="cart-line__each-inline">, <?php prairie_ash_figure_text( $prairie_ash_line['each'] ); ?> <?php echo esc_html( prairie_ash_wording( 'each' ) ); ?></span></p>
							<button class="text-link text-link--on-paper cart-line__remove" type="submit" name="pa_remove_item" value="<?php echo esc_attr( $prairie_ash_line['key'] ); ?>"><?php echo esc_html( prairie_ash_wording( 'remove' ) . ' ' . $prairie_ash_line['name'] ); ?></button>
						</div>
						<div class="field cart-line__quantity">
							<label class="field__label" for="quantity-<?php echo esc_attr( $prairie_ash_line['key'] ); ?>"><?php echo esc_html( prairie_ash_wording( 'quantity' ) ); ?></label>
							<input class="input input--code" id="quantity-<?php echo esc_attr( $prairie_ash_line['key'] ); ?>" type="number" name="cart[<?php echo esc_attr( $prairie_ash_line['key'] ); ?>][qty]" value="<?php echo esc_attr( $prairie_ash_line['quantity'] ); ?>" min="0"<?php echo $prairie_ash_line['max'] > 0 ? ' max="' . absint( $prairie_ash_line['max'] ) . '"' : ''; ?> inputmode="numeric" data-cart-quantity>
						</div>
						<p class="cart-line__each"><span class="visually-hidden"><?php echo esc_html( prairie_ash_wording( 'each_hidden' ) ); ?> </span><?php echo esc_html( $prairie_ash_line['each'] ); ?></p>
						<p class="cart-line__total"><span class="cart-line__total-label"><?php echo esc_html( prairie_ash_wording( 'col_total' ) ); ?></span> <span class="cart-line__total-value"><?php echo esc_html( $prairie_ash_line['total'] ); ?></span></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
			<noscript><button class="button button--outline" type="submit" name="update_cart" value="1"><?php echo esc_html( prairie_ash_wording( 'update_cart' ) ); ?></button></noscript>
		</form>
	</div>
</section>

<section class="cart-summary band band--wall" aria-labelledby="cart-summary-title">
	<div class="cart-summary__container">
		<div class="cart-summary__text">
			<h2 class="cart-summary__title" id="cart-summary-title"><?php echo esc_html( prairie_ash_wording( 'summary_heading' ) ); ?></h2>
			<?php if ( prairie_ash_wording( 'summary_note' ) ) : ?>
				<p class="cart-summary__note"><?php echo esc_html( prairie_ash_wording( 'summary_note' ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="cart-summary__totals">
			<dl class="lv lv--total">
				<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'subtotal_before_tax' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_price_text( $prairie_ash_cart->get_subtotal() ) ); ?></dd></div>
				<?php if ( $prairie_ash_cart->needs_shipping() ) : ?>
					<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'cart_shipping_label' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_shipping_value() ); ?></dd></div>
				<?php endif; ?>
				<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'tax' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_price_text( $prairie_ash_cart->get_total_tax() ) ); ?></dd></div>
				<div class="lv__row lv__row--due"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'total_to_pay' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_price_text( $prairie_ash_cart->get_total( 'edit' ) ) ); ?></dd></div>
			</dl>
			<div class="cart-summary__actions">
				<a class="button button--primary" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php echo esc_html( prairie_ash_wording( 'go_to_checkout' ) ); ?></a>
				<a class="text-link text-link--on-wall" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php echo esc_html( prairie_ash_wording( 'keep_shopping' ) ); ?></a>
			</div>
		</div>
	</div>
</section>
<?php
do_action( 'woocommerce_after_cart' );
