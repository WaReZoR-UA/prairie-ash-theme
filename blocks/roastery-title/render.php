<?php
/**
 * Render template for the prairie-ash/roastery-title block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'roastery-title' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'roastery-title', 'band', 'band--wall' ) ); ?> aria-labelledby="roastery-title">
	<div class="roastery-title__container" data-motion="roast-sign">
		<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
		<div class="roastery-title__text">
			<div class="roastery-title__sign">
				<?php if ( $f['eyebrow'] ) : ?>
					<p class="roastery-title__eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( $f['heading'] ) : ?>
					<h1 class="roastery-title__heading" id="roastery-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
				<?php endif; ?>
			</div>
			<?php if ( $f['intro'] ) : ?>
				<p class="roastery-title__intro"<?php prairie_ash_el( $block, 'intro' ); ?>><?php echo esc_html( $f['intro'] ); ?></p>
			<?php endif; ?>
		</div>
		<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
		<?php if ( $f['image'] ) : ?>
			<div class="roastery-title__media media-ibg">
				<?php
				// lcp-image: the title photo paints the Largest Contentful Paint of the roastery page.
				prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'roastery-title__media' ), $f['heading'], $f['image_decorative'] );
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
