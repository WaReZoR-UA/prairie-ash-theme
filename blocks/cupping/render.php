<?php
/**
 * Render template for the prairie-ash/cupping block.
 *
 * The tablet crop is a picture source, as the design draws it. The source
 * carries the attachment's own srcset, so the browser picks a file that fits.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'cupping' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'cupping' ) ); ?> aria-labelledby="cupping-title">
	<div class="cupping__container">
		<?php if ( $f['image'] ) : ?>
			<div class="cupping__media media-ibg" data-motion-parallax>
				<?php
				prairie_ash_image(
					$f['image'],
					prairie_ash_slot_sizes( 'cupping__media' ),
					$f['heading'],
					$f['image_decorative'],
					array(),
					$f['image_wide'] ? array(
						array(
							'media' => '(min-width: 48em) and (max-width: 61.99875em)',
							'image' => $f['image_wide'],
							'sizes' => prairie_ash_slot_sizes( 'cupping__media-wide' ),
						),
					) : array()
				);
				?>
			</div>
		<?php endif; ?>
		<div class="cupping__bill">
			<?php if ( $f['label'] ) : ?>
				<p class="cupping__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['label'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h2 class="cupping__title" id="cupping-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['date'] ) : ?>
				<p class="cupping__date"<?php prairie_ash_el( $block, 'date' ); ?>><?php echo esc_html( $f['date'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['venue'] ) : ?>
				<p class="cupping__venue"<?php prairie_ash_el( $block, 'venue' ); ?>><?php echo esc_html( $f['venue'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['status'] ) : ?>
				<p class="cupping__status"<?php prairie_ash_el( $block, 'venue' ); ?>><?php echo esc_html( $f['status'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="cupping__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
			<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-paper' ); ?>
		</div>
	</div>
</section>
