<?php
/**
 * Render template for the prairie-ash/about-story block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'about-story' );
$title_id = prairie_ash_heading_id( 'about-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'about-story', 'band', 'band--deep' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="about-story__container" data-motion="about-sign">
		<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
		<div class="about-story__heading">
			<?php if ( $f['eyebrow'] ) : ?>
				<p class="about-story__eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['eyebrow'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h1 class="about-story__title" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
			<?php endif; ?>
		</div>
		<div class="about-story__row">
			<?php if ( $f['image'] ) : ?>
				<div class="about-story__media media-ibg" data-motion-parallax>
					<?php
					// lcp-image: the story photo paints the Largest Contentful Paint of the about page.
					prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'about-story__media' ), $f['heading'], $f['image_decorative'] );
					?>
				</div>
			<?php endif; ?>
			<div class="about-story__text">
				<?php foreach ( prairie_ash_paragraphs( $f['body'] ) as $paragraph ) : ?>
					<p class="about-story__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $paragraph ); ?></p>
				<?php endforeach; ?>
				<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-wall' ); ?>
			</div>
		</div>
	</div>
</section>
