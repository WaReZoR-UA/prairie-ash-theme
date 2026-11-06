<?php
/**
 * Render template for the prairie-ash/about-team block.
 *
 * A portrait sits right beside its own name, so it takes the attachment's alt
 * when it has one and an empty alt otherwise, never the name again.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'about-team' );
$title_id = prairie_ash_heading_id( 'about-team-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'about-team', 'band', 'band--wall' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="about-team__container">
		<?php if ( $f['heading'] ) : ?>
			<h2 class="about-team__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
		<?php endif; ?>
		<?php if ( $f['people'] ) : ?>
			<div class="about-team__people">
				<?php foreach ( $f['people'] as $person ) : ?>
					<article class="person" data-motion="about-person">
						<?php if ( $person['image'] ) : ?>
							<div class="person__media media-ibg">
								<?php prairie_ash_image( $person['image'], prairie_ash_slot_sizes( 'person__media' ) ); ?>
							</div>
						<?php endif; ?>
						<div class="person__text">
							<?php if ( $person['name'] ) : ?>
								<h3 class="person__name"<?php prairie_ash_el( $block, 'name' ); ?>><?php echo esc_html( $person['name'] ); ?></h3>
							<?php endif; ?>
							<?php if ( $person['bio'] ) : ?>
								<p class="person__bio"<?php prairie_ash_el( $block, 'bio' ); ?>><?php echo esc_html( $person['bio'] ); ?></p>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $f['button']['url'] ) || ! empty( $f['link']['url'] ) ) : ?>
			<div class="about-team__actions">
				<?php prairie_ash_link_field( $f['button'], 'button button--primary' ); ?>
				<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-wall' ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
