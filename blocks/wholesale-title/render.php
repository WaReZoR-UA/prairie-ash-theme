<?php
/**
 * Render template for the prairie-ash/wholesale-title block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'wholesale-title' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'wholesale-title', 'band', 'band--wall' ) ); ?> aria-labelledby="wholesale-title">
	<div class="wholesale-title__container" data-motion="wholesale-sign">
		<div class="wholesale-title__sign">
			<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
			<div class="wholesale-title__text">
				<?php if ( $f['eyebrow'] ) : ?>
					<p class="eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( $f['heading'] ) : ?>
					<h1 class="wholesale-title__heading" id="wholesale-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
				<?php endif; ?>
			</div>
			<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
			<div class="wholesale-title__aside">
				<?php if ( $f['intro'] ) : ?>
					<p class="wholesale-title__intro"<?php prairie_ash_el( $block, 'intro' ); ?>><?php echo esc_html( $f['intro'] ); ?></p>
				<?php endif; ?>
				<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-wall' ); ?>
			</div>
		</div>
		<?php if ( $f['image'] ) : ?>
			<div class="wholesale-title__media media-ibg">
				<?php
				// lcp-image: the title photo paints the Largest Contentful Paint of the wholesale page.
				prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'wholesale-title__media' ), $f['heading'], $f['image_decorative'] );
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
