<?php
/**
 * Render template for the prairie-ash/shipping-title block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'shipping-title' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'shipping-title' ) ); ?> aria-labelledby="shipping-title">
	<div class="shipping-title__container" data-motion="shipping-sign">
		<div class="shipping-title__text">
			<?php if ( $f['eyebrow'] ) : ?>
				<p class="eyebrow shipping-title__eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['eyebrow'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h1 class="shipping-title__heading" id="shipping-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
			<?php endif; ?>
			<?php if ( $f['intro'] ) : ?>
				<p class="shipping-title__intro"<?php prairie_ash_el( $block, 'intro' ); ?>><?php echo esc_html( $f['intro'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['jumps'] ) : ?>
				<nav class="shipping-title__jumps" aria-label="<?php echo esc_attr( $f['jumps_label'] ); ?>">
					<?php foreach ( $f['jumps'] as $row ) : ?>
						<?php prairie_ash_link_field( $row['link'], 'text-link text-link--on-paper' ); ?>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>
		<?php if ( $f['image'] ) : ?>
			<div class="shipping-title__media media-ibg">
				<?php
				// lcp-image: the title photo paints the Largest Contentful Paint of the shipping page.
				prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'shipping-title__media' ), $f['heading'], $f['image_decorative'] );
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
