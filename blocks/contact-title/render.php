<?php
/**
 * Render template for the prairie-ash/contact-title block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'contact-title' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'contact-title' ) ); ?> aria-labelledby="contact-title">
	<div class="contact-title__container" data-motion="contact-sign">
		<div class="contact-title__text">
			<?php if ( $f['eyebrow'] ) : ?>
				<p class="eyebrow contact-title__eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['eyebrow'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h1 class="contact-title__heading" id="contact-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
			<?php endif; ?>
			<?php if ( $f['intro'] ) : ?>
				<p class="contact-title__intro"<?php prairie_ash_el( $block, 'intro' ); ?>><?php echo esc_html( $f['intro'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $f['image'] ) : ?>
			<div class="contact-title__media media-ibg">
				<?php
				// lcp-image: the title photo paints the Largest Contentful Paint of the contact page.
				prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'contact-title__media' ), $f['heading'], $f['image_decorative'] );
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
