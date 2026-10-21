<?php
/**
 * The shop: the sections the client placed on the Shop page in the editor.
 *
 * WooCommerce runs its product query for this request and the Shop list
 * section draws it, so a search and the shop's order apply as WooCommerce
 * decides them, while the title, the tools and the wording stay blocks the
 * client edits like any other page.
 *
 * Overrides WooCommerce's archive-product.php.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

get_header();

$prairie_ash_shop = get_post( wc_get_page_id( 'shop' ) );
?>
		<main class="page" id="main" tabindex="-1" data-motion-scope>
			<?php
			if ( $prairie_ash_shop ) {
				echo prairie_ash_page_content( $prairie_ash_shop );
			}
			?>
		</main>
<?php
get_footer();
