<?php
/**
 * The empty cart, in the design's markup.
 *
 * Overrides WooCommerce's cart/cart-empty.php.
 *
 * @package PrairieAsh
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_cart_is_empty' );

$prairie_ash_image = function_exists( 'get_field' ) ? get_field( 'wording_empty_image', 'option' ) : 0;
?>
<section class="cart-empty" aria-labelledby="cart-empty-title">
	<div class="cart-empty__container">
		<div class="cart-empty__text" data-motion="sign">
			<p class="cart-empty__eyebrow"><?php echo esc_html( prairie_ash_wording( 'empty_eyebrow' ) ); ?></p>
			<h1 class="cart-empty__heading" id="cart-empty-title"><?php echo esc_html( prairie_ash_wording( 'empty_heading' ) ); ?></h1>
			<p class="cart-empty__body"><?php echo esc_html( prairie_ash_wording( 'empty_body' ) ); ?></p>
			<a class="text-link text-link--on-paper" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php echo esc_html( prairie_ash_wording( 'empty_link' ) ); ?></a>
		</div>
		<?php if ( $prairie_ash_image ) : ?>
			<div class="cart-empty__media media-ibg">
				<?php
				// lcp-image: the photo paints the Largest Contentful Paint of the empty cart.
				prairie_ash_lcp_image( $prairie_ash_image, prairie_ash_slot_sizes( 'cart-empty__media' ), '', true );
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
