<?php
/**
 * Render template for the prairie-ash/shop-title block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'shop-title' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'shop-title' ) ); ?> aria-labelledby="shop-title">
	<div class="shop-title__container" data-motion="shop-sign">
		<?php if ( $f['image'] ) : ?>
			<div class="shop-title__media media-ibg">
				<?php
				// lcp-image: the title photo paints the Largest Contentful Paint of the shop.
				prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'shop-title__media' ), $f['heading'], $f['image_decorative'] );
				?>
			</div>
		<?php endif; ?>
		<div class="shop-title__label">
			<span class="pinstripe pinstripe--brick" aria-hidden="true" data-motion="stripe"></span>
			<?php if ( $f['eyebrow'] ) : ?>
				<p class="shop-title__eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['eyebrow'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h1 class="shop-title__heading" id="shop-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
			<?php endif; ?>
			<span class="pinstripe pinstripe--brick" aria-hidden="true" data-motion="stripe"></span>
			<?php if ( $f['intro'] ) : ?>
				<p class="shop-title__intro"<?php prairie_ash_el( $block, 'intro' ); ?>><?php echo esc_html( $f['intro'] ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
