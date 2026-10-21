<?php
/**
 * Render template for the prairie-ash/roastery-cupping block.
 *
 * The date is read as stored, eight digits, and formatted here in the site's
 * time zone, so the plate and its datetime attribute come from one value.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f    = prairie_ash_fields( 'roastery-cupping' );
$raw  = function_exists( 'get_field' ) ? (string) get_field( 'next_date', false, false ) : '';
$when = preg_match( '/^\d{8}$/', $raw ) ? DateTimeImmutable::createFromFormat( '!Ymd', $raw, wp_timezone() ) : false;
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'roastery-cupping', 'band', 'band--wall' ), array( 'id' => 'cupping' ) ); ?> aria-labelledby="roastery-cupping-title">
	<div class="roastery-cupping__container">
		<?php if ( $f['image'] ) : ?>
			<div class="roastery-cupping__media media-ibg" data-motion-parallax>
				<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'roastery-cupping__media' ), $f['heading'], $f['image_decorative'] ); ?>
			</div>
		<?php endif; ?>
		<div class="roastery-cupping__bill">
			<?php if ( $f['label'] ) : ?>
				<p class="roastery-cupping__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['label'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h2 class="roastery-cupping__heading" id="roastery-cupping-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="roastery-cupping__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
			<?php if ( $when || $f['next_text'] ) : ?>
				<div class="roastery-cupping__next">
					<?php if ( $when ) : ?>
						<time class="date-plate" data-motion="plate" datetime="<?php echo esc_attr( $when->format( 'Y-m-d' ) ); ?>">
							<span class="date-plate__weekday"><?php echo esc_html( wp_date( 'D', $when->getTimestamp() ) ); ?></span>
							<span class="date-plate__day"><?php echo esc_html( wp_date( 'j', $when->getTimestamp() ) ); ?></span>
							<span class="date-plate__month"><?php echo esc_html( wp_date( 'M Y', $when->getTimestamp() ) ); ?></span>
						</time>
					<?php endif; ?>
					<?php if ( $f['next_text'] ) : ?>
						<p class="roastery-cupping__next-text"<?php prairie_ash_el( $block, 'next' ); ?>><?php echo esc_html( $f['next_text'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-paper' ); ?>
		</div>
	</div>
</section>
