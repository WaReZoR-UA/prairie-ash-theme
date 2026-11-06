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

/*
 * Wording comes from the site settings (inc/site-settings.php), with the
 * wording below as the fallback while a setting is empty or not registered.
 */
$prairie_ash_text = static function ( $key, $fallback ) {
	$value = function_exists( 'prairie_ash_setting' ) ? prairie_ash_setting( $key ) : '';
	return is_string( $value ) && '' !== trim( $value ) ? $value : $fallback;
};

$prairie_ash_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';

get_header();
?>
		<main class="page" id="main" tabindex="-1">
			<section class="results-title band band--wall" aria-labelledby="results-title">
				<div class="results-title__container">
					<h1 class="results-title__heading" id="results-title"><?php echo esc_html( $prairie_ash_text( 'not_found_heading', __( 'Page not found', 'prairie-ash' ) ) ); ?></h1>
				</div>
			</section>
			<section class="results-list band band--paper">
				<div class="results-list__container">
					<p class="results-list__empty"><?php echo esc_html( $prairie_ash_text( 'not_found_text', __( 'Nothing lives at this address. The page may have moved, or the address may be mistyped.', 'prairie-ash' ) ) ); ?></p>
					<div class="results-list__links">
						<a class="text-link text-link--on-paper" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $prairie_ash_text( 'home_link_label', __( 'Back to the home page', 'prairie-ash' ) ) ); ?></a>
						<?php if ( $prairie_ash_shop && home_url( '/' ) !== $prairie_ash_shop ) : ?>
							<a class="text-link text-link--on-paper" href="<?php echo esc_url( $prairie_ash_shop ); ?>"><?php echo esc_html( $prairie_ash_text( 'shop_link_label', __( 'Browse the coffees', 'prairie-ash' ) ) ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</section>
		</main>
<?php
get_footer();
