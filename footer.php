<?php
/**
 * The site footer, the analytics notice and the closing of the page wrapper.
 *
 * Every value comes from Site settings and the "Footer legal links" menu.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_hours   = prairie_ash_hours();
$prairie_ash_phone   = prairie_ash_setting( 'phone_display' );
$prairie_ash_email   = prairie_ash_setting( 'email' );
$prairie_ash_address = prairie_ash_setting( 'address_line' );
$prairie_ash_legal   = prairie_ash_menu_items( 'legal' );
?>
		<footer class="footer" id="site-footer" data-motion-scope>
			<div class="footer__container">
				<?php get_template_part( 'parts/wordmark' ); ?>
				<div class="footer__columns">
					<div class="footer__column">
						<p class="footer__heading"><?php echo esc_html( prairie_ash_setting( 'business_name' ) ); ?></p>
						<?php if ( $prairie_ash_address || $prairie_ash_phone || $prairie_ash_email ) : ?>
							<address class="footer__contact">
								<?php if ( $prairie_ash_address ) : ?>
									<span><?php echo esc_html( $prairie_ash_address ); ?></span>
								<?php endif; ?>
								<?php prairie_ash_link( $prairie_ash_phone, prairie_ash_phone_href(), 'footer__contact-link' ); ?>
								<?php prairie_ash_link( $prairie_ash_email, $prairie_ash_email ? 'mailto:' . $prairie_ash_email : '', 'footer__contact-link' ); ?>
							</address>
						<?php endif; ?>
					</div>
					<div class="footer__column">
						<h2 class="footer__heading"><?php echo esc_html( prairie_ash_setting( 'footer_hours_heading' ) ); ?></h2>
						<dl class="footer__hours">
							<?php foreach ( $prairie_ash_hours as $prairie_ash_row ) : ?>
								<div class="footer__hours-row"><dt><?php echo esc_html( $prairie_ash_row['label'] ); ?></dt><dd><?php echo esc_html( $prairie_ash_row['time'] ); ?></dd></div>
							<?php endforeach; ?>
							<div class="footer__hours-row"><dt><?php echo esc_html( prairie_ash_setting( 'roast_days_label' ) ); ?></dt><dd><?php echo esc_html( prairie_ash_setting( 'roast_days' ) ); ?></dd></div>
							<div class="footer__hours-row"><dt><?php echo esc_html( prairie_ash_setting( 'cupping_label' ) ); ?></dt><dd><?php prairie_ash_nowrap_times( prairie_ash_setting( 'cupping_when' ) ); ?></dd></div>
						</dl>
					</div>
					<div class="footer__column">
						<h2 class="footer__heading"><?php echo esc_html( prairie_ash_setting( 'footer_touch_heading' ) ); ?></h2>
						<ul class="footer__links">
							<?php if ( prairie_ash_setting( 'instagram_url' ) ) : ?>
								<li><?php prairie_ash_link( prairie_ash_setting( 'instagram_label' ), prairie_ash_setting( 'instagram_url' ), 'text-link text-link--on-wall' ); ?></li>
							<?php endif; ?>
							<?php if ( prairie_ash_setting( 'roast_notes_link' ) ) : ?>
								<li><?php prairie_ash_link( prairie_ash_setting( 'roast_notes_label' ), prairie_ash_setting( 'roast_notes_link' ), 'text-link text-link--on-wall' ); ?></li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
				<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
				<div class="footer__legal">
					<?php foreach ( $prairie_ash_legal as $prairie_ash_item ) : ?>
						<?php prairie_ash_link( $prairie_ash_item['label'], $prairie_ash_item['url'], 'text-link text-link--on-wall', '', array( 'aria-current' => $prairie_ash_item['current'] ? 'page' : '' ) ); ?>
					<?php endforeach; ?>
					<button class="text-link text-link--on-wall" type="button" data-analytics-settings><?php echo esc_html( prairie_ash_setting( 'analytics_link_label' ) ); ?></button>
					<p class="footer__copyright"><?php echo esc_html( prairie_ash_setting( 'copyright' ) ); ?></p>
				</div>
			</div>
		</footer>
	<section class="analytics-notice" aria-labelledby="analytics-notice-title" data-analytics-notice hidden>
		<div class="analytics-notice__body">
			<h2 class="analytics-notice__title" id="analytics-notice-title"><?php echo esc_html( prairie_ash_setting( 'analytics_title' ) ); ?></h2>
			<p class="analytics-notice__text" role="status" data-analytics-text><?php echo esc_html( prairie_ash_setting( 'analytics_text' ) ); ?></p>
			<div class="analytics-notice__actions">
				<button class="button button--soot" type="button" data-analytics-choice="on"><?php echo esc_html( prairie_ash_setting( 'analytics_on_button' ) ); ?></button>
				<button class="button button--soot" type="button" data-analytics-choice="off"><?php echo esc_html( prairie_ash_setting( 'analytics_off_button' ) ); ?></button>
			</div>
		</div>
	</section>
	</div>
	<?php wp_footer(); ?>
</body>
</html>
