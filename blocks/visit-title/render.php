<?php
/**
 * Render template for the prairie-ash/visit-title block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'visit-title' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'visit-title', 'band', 'band--wall' ) ); ?> aria-labelledby="visit-title">
	<div class="visit-title__container" data-motion="visit-sign">
		<?php if ( $f['image'] ) : ?>
			<div class="visit-title__media media-ibg">
				<?php
				// lcp-image: the storefront photo paints the Largest Contentful Paint of the visit page.
				prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'visit-title__media' ), $f['heading'], $f['image_decorative'] );
				?>
			</div>
		<?php endif; ?>
		<div class="visit-title__plate">
			<span class="pinstripe pinstripe--brick" aria-hidden="true" data-motion="stripe"></span>
			<?php if ( $f['eyebrow'] ) : ?>
				<p class="visit-title__eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['eyebrow'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h1 class="visit-title__heading" id="visit-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
			<?php endif; ?>
			<?php if ( $f['intro'] ) : ?>
				<p class="visit-title__intro"<?php prairie_ash_el( $block, 'intro' ); ?>><?php echo esc_html( $f['intro'] ); ?></p>
			<?php endif; ?>
			<span class="pinstripe pinstripe--brick" aria-hidden="true" data-motion="stripe"></span>
		</div>
	</div>
</section>
