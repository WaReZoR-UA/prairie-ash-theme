<?php
/**
 * Render template for the prairie-ash/visit-hours block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f      = prairie_ash_fields( 'visit-hours' );
$street = prairie_ash_setting( 'street' );
$city   = prairie_ash_setting( 'locality' );
$phone  = prairie_ash_setting( 'phone_display' );
$email  = prairie_ash_setting( 'email' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'visit-hours', 'band', 'band--deep' ) ); ?> aria-labelledby="visit-hours-title">
	<div class="visit-hours__container">
		<?php if ( $f['heading'] ) : ?>
			<h2 class="visit-hours__heading" id="visit-hours-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
		<?php endif; ?>
		<div class="visit-hours__row">
			<div class="visit-hours__address">
				<p class="visit-hours__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $f['address_label'] ); ?></p>
				<?php if ( $street ) : ?>
					<p class="visit-hours__street"<?php prairie_ash_el( $block, 'street' ); ?>><?php echo esc_html( $street ); ?></p>
				<?php endif; ?>
				<?php if ( $city ) : ?>
					<p class="visit-hours__city"<?php prairie_ash_el( $block, 'city' ); ?>><?php echo esc_html( $city ); ?></p>
				<?php endif; ?>
				<?php if ( $phone || $email ) : ?>
					<dl class="visit-hours__contact lv lv--fact-lead lv--stack">
						<?php if ( $phone ) : ?>
							<div class="lv__row"><dt class="lv__label"><?php echo esc_html( $f['phone_label'] ); ?></dt><dd class="lv__value"><?php prairie_ash_link( $phone, prairie_ash_phone_href(), 'inline-link' ); ?></dd></div>
						<?php endif; ?>
						<?php if ( $email ) : ?>
							<div class="lv__row"><dt class="lv__label"><?php echo esc_html( $f['email_label'] ); ?></dt><dd class="lv__value"><?php prairie_ash_link( $email, 'mailto:' . $email, 'inline-link' ); ?></dd></div>
						<?php endif; ?>
					</dl>
				<?php endif; ?>
				<?php prairie_ash_link_field( $f['button'], 'button button--primary' ); ?>
			</div>
			<div class="visit-hours__open">
				<p class="visit-hours__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $f['hours_label'] ); ?></p>
				<dl class="visit-hours__table lv lv--hours lv--stack">
					<?php foreach ( prairie_ash_hours() as $row ) : ?>
						<div class="lv__row"><dt class="lv__label"><?php echo esc_html( $row['label'] ); ?></dt><dd class="lv__value"><?php echo esc_html( $row['time'] ); ?></dd></div>
					<?php endforeach; ?>
					<div class="lv__row visit-hours__roast"><dt class="lv__label"><?php echo esc_html( prairie_ash_setting( 'roast_days_label' ) ); ?></dt><dd class="lv__value"><?php echo esc_html( prairie_ash_setting( 'roast_days' ) ); ?></dd></div>
				</dl>
				<?php if ( $f['note'] ) : ?>
					<p class="visit-hours__note"<?php prairie_ash_el( $block, 'note' ); ?>><?php echo esc_html( $f['note'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
