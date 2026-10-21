<?php
/**
 * A page: the stack of section blocks the client placed in the editor.
 *
 * The cart and the checkout are WooCommerce's own pages. Their output comes
 * straight from WooCommerce's cart and checkout handlers, whose templates the
 * theme overrides in woocommerce/, without the shortcode's extra wrapper, so
 * the sections sit in the page exactly as the markup draws them.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
		<main class="page" id="main" tabindex="-1" data-motion-scope>
			<?php
			if ( function_exists( 'is_cart' ) && is_cart() ) {
				WC_Shortcode_Cart::output( array() );
			} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
				WC_Shortcode_Checkout::output( array() );
			} else {
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
			}
			?>
		</main>
<?php
get_footer();
