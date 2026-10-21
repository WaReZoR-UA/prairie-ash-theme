<?php
/**
 * Render template for the prairie-ash/hero block.
 *
 * Available here, from the field plugin: $block, $content, $is_preview,
 * $post_id, $wp_block, $context.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f       = prairie_ash_fields( 'hero' );
$heading = trim( $f['title_line_1'] . ' ' . $f['title_line_2'] );
$today   = $f['show_today'] ? prairie_ash_today_line() : '';
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'hero' ) ); ?>>
	<div class="hero__container">
		<div class="hero__sign" data-motion="sign" data-motion-ghost>
			<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
			<div class="hero__row">
				<?php if ( $heading ) : ?>
					<h1 class="hero__title" id="hero-title"<?php prairie_ash_el( $block, 'title' ); ?>>
						<?php if ( $f['title_line_1'] ) : ?>
							<span class="hero__title-line hero__title-line--l"><?php echo esc_html( $f['title_line_1'] ); ?></span>
						<?php endif; ?>
						<?php if ( $f['title_line_2'] ) : ?>
							<span class="hero__title-line hero__title-line--xl"><?php echo esc_html( $f['title_line_2'] ); ?></span>
						<?php endif; ?>
					</h1>
				<?php endif; ?>
				<?php prairie_ash_editor_hint( $is_preview && ! $heading, __( 'Add the heading of the hero.', 'prairie-ash' ) ); ?>
				<div class="hero__side">
					<div class="hero__side-top">
						<?php if ( $f['eyebrow'] ) : ?>
							<p class="hero__eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['eyebrow'] ); ?></p>
						<?php endif; ?>
						<?php if ( $f['label'] || $today ) : ?>
							<div class="hero__labels">
								<?php if ( $f['label'] ) : ?>
									<p class="hero__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $f['label'] ); ?></p>
								<?php endif; ?>
								<?php if ( $today ) : ?>
									<p class="hero__label" data-today<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $today ); ?></p>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="hero__side-bottom">
						<?php if ( $f['lead'] ) : ?>
							<p class="hero__lead"<?php prairie_ash_el( $block, 'lead' ); ?>><?php echo esc_html( $f['lead'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $f['primary_link']['url'] ) || ! empty( $f['secondary_link']['url'] ) ) : ?>
							<div class="hero__actions">
								<?php prairie_ash_link_field( $f['primary_link'], 'button button--primary' ); ?>
								<?php prairie_ash_link_field( $f['secondary_link'], 'text-link text-link--on-wall' ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
		</div>
		<?php if ( $f['image'] ) : ?>
			<figure class="hero__window">
				<div class="hero__media media-ibg" data-motion-parallax>
					<?php
					// lcp-image: the hero photo paints the Largest Contentful Paint of the home page.
					prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'hero__media' ), $heading, $f['image_decorative'] );
					?>
				</div>
				<?php if ( $f['caption'] ) : ?>
					<figcaption class="hero__caption"<?php prairie_ash_el( $block, 'caption' ); ?>><?php echo esc_html( $f['caption'] ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>
	</div>
</section>
