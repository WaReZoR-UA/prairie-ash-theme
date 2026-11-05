<?php
/**
 * The missing page.
 *
 * Says that the address leads nowhere and offers the way back, in the same
 * wall and paper bands as the fallback template.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';

get_header();
?>
		<main class="page" id="main" tabindex="-1">
			<section class="results-title band band--wall" aria-labelledby="results-title">
				<div class="results-title__container">
					<h1 class="results-title__heading" id="results-title"><?php esc_html_e( 'Page not found', 'prairie-ash' ); ?></h1>
				</div>
			</section>
			<section class="results-list band band--paper">
				<div class="results-list__container">
					<p class="results-list__empty"><?php esc_html_e( 'Nothing lives at this address. The page may have moved, or the address may be mistyped.', 'prairie-ash' ); ?></p>
					<div class="results-list__links">
						<a class="text-link text-link--on-paper" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the home page', 'prairie-ash' ); ?></a>
						<?php if ( $prairie_ash_shop && home_url( '/' ) !== $prairie_ash_shop ) : ?>
							<a class="text-link text-link--on-paper" href="<?php echo esc_url( $prairie_ash_shop ); ?>"><?php esc_html_e( 'Browse the coffees', 'prairie-ash' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</section>
		</main>
<?php
get_footer();
