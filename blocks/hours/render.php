<?php
/**
 * Render template for the prairie-ash/hours block.
 *
 * The data-hours table is what the design's script reads to write the
 * "Open today" line in the browser.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'hours' );
$hours    = prairie_ash_hours();
$street   = prairie_ash_setting( 'street' );
$city     = prairie_ash_setting( 'locality' );
$title_id = prairie_ash_heading_id( 'hours-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'hours' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="hours__container">
		<h2 class="hours__title"<?php echo $title_id ? ' id="' . esc_attr( $title_id ) . '"' : ''; ?><?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
		<div class="hours__row">
			<div class="hours__find">
				<p class="hours__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $f['find_label'] ); ?></p>
				<?php if ( $street ) : ?>
					<p class="hours__street"<?php prairie_ash_el( $block, 'street' ); ?>><?php echo esc_html( $street ); ?></p>
				<?php endif; ?>
				<?php if ( $city ) : ?>
					<p class="hours__city"<?php prairie_ash_el( $block, 'city' ); ?>><?php echo esc_html( $city ); ?></p>
				<?php endif; ?>
				<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-paper' ); ?>
			</div>
			<div class="hours__open">
				<p class="hours__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $f['hours_label'] ); ?></p>
				<dl class="hours__table" data-hours>
					<?php foreach ( $hours as $row ) : ?>
						<div class="hours__table-row" data-days="<?php echo esc_attr( $row['days'] ); ?>" data-open="<?php echo esc_attr( $row['open'] ); ?>" data-close="<?php echo esc_attr( $row['close'] ); ?>">
							<dt class="hours__day"<?php prairie_ash_el( $block, 'days' ); ?>><?php echo esc_html( $row['label'] ); ?></dt>
							<dd class="hours__time"<?php prairie_ash_el( $block, 'times' ); ?>><?php echo esc_html( $row['time'] ); ?></dd>
						</div>
					<?php endforeach; ?>
					<?php if ( $f['show_roast_days'] && prairie_ash_setting( 'roast_days_label' ) && prairie_ash_setting( 'roast_days' ) ) : ?>
						<div class="hours__table-row hours__table-row--roast">
							<dt class="hours__day"<?php prairie_ash_el( $block, 'days' ); ?>><?php echo esc_html( prairie_ash_setting( 'roast_days_label' ) ); ?></dt>
							<dd class="hours__time"<?php prairie_ash_el( $block, 'times' ); ?>><?php echo esc_html( prairie_ash_setting( 'roast_days' ) ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>
			</div>
		</div>
	</div>
</section>
