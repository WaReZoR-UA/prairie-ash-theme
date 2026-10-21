<?php
/**
 * Error notices, as the design's error summary panel.
 *
 * Each notice that belongs to a field links to it, so the summary works the
 * same way as the one the design's form script builds before sending.
 *
 * Overrides WooCommerce's notices/error.php.
 *
 * @package PrairieAsh
 * @version 8.6.0
 *
 * @var array $notices Notices, each with notice and data.
 */

defined( 'ABSPATH' ) || exit;

if ( ! $notices ) {
	return;
}
?>
<section class="checkout-errors woocommerce-error" aria-label="<?php echo esc_attr( prairie_ash_wording( 'errors_label' ) ); ?>">
	<div class="checkout-errors__container">
		<div class="error-summary error-summary--panel" role="alert" tabindex="-1" data-wc-errors>
			<h2 class="error-summary__title"><?php echo esc_html( prairie_ash_wording( 'notice_error_title' ) ); ?></h2>
			<ul class="error-summary__list">
				<?php foreach ( $notices as $prairie_ash_notice ) : ?>
					<?php $prairie_ash_field = isset( $prairie_ash_notice['data']['id'] ) ? (string) $prairie_ash_notice['data']['id'] : ''; ?>
					<li<?php echo '' !== $prairie_ash_field ? ' data-id="' . esc_attr( $prairie_ash_field ) . '"' : ''; ?>>
						<?php if ( '' !== $prairie_ash_field ) : ?>
							<a class="text-link text-link--on-paper" href="<?php echo esc_url( '#' . $prairie_ash_field ); ?>"><?php echo wp_kses_post( wp_strip_all_tags( $prairie_ash_notice['notice'] ) ); ?></a>
						<?php else : ?>
							<?php echo wc_kses_notice( $prairie_ash_notice['notice'] ); ?>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
