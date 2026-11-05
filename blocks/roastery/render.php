<?php
/**
 * Render template for the prairie-ash/roastery block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'roastery' );
$ad_lines = array(
	array(
		'big'  => $f['ad_1_big'],
		'note' => $f['ad_1_note'],
	),
	array(
		'label' => $f['ad_2_label'],
		'big'   => $f['ad_2_big'],
	),
	array(
		'note'   => $f['ad_3_note'],
		'medium' => $f['ad_3_medium'],
	),
);
$has_ad   = implode( '', array_merge( ...array_map( 'array_values', $ad_lines ) ) ) !== '';
$title_id = prairie_ash_heading_id( 'roastery-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'roastery' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="roastery__container">
		<div class="roastery__window" data-motion="scene">
			<div class="roastery__text">
				<div class="roastery__text-top">
					<?php if ( $f['label'] ) : ?>
						<p class="roastery__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['label'] ); ?></p>
					<?php endif; ?>
					<?php if ( $f['heading'] ) : ?>
						<h2 class="roastery__title" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
					<?php endif; ?>
				</div>
				<div class="roastery__text-bottom">
					<?php if ( $f['body'] ) : ?>
						<p class="roastery__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
					<?php endif; ?>
					<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-wall' ); ?>
				</div>
			</div>
			<?php if ( $f['image'] ) : ?>
				<div class="roastery__media media-ibg">
					<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'roastery__media' ), $f['heading'], $f['image_decorative'] ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( $has_ad ) : ?>
			<div class="roastery__ad" data-motion="wall">
				<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
				<ul class="roastery__ad-lines">
					<?php if ( $f['ad_1_big'] || $f['ad_1_note'] ) : ?>
						<li class="roastery__ad-line"<?php prairie_ash_el( $block, 'advert' ); ?>>
							<?php if ( $f['ad_1_big'] ) : ?>
								<span class="roastery__ad-big"><?php echo esc_html( $f['ad_1_big'] ); ?></span>
							<?php endif; ?>
							<?php if ( $f['ad_1_note'] ) : ?>
								<span class="roastery__ad-note"><?php echo esc_html( $f['ad_1_note'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endif; ?>
					<?php if ( $f['ad_2_label'] || $f['ad_2_big'] ) : ?>
						<li class="roastery__ad-line roastery__ad-line--end"<?php prairie_ash_el( $block, 'advert' ); ?>>
							<?php if ( $f['ad_2_label'] ) : ?>
								<span class="roastery__ad-label"><?php echo esc_html( $f['ad_2_label'] ); ?></span>
							<?php endif; ?>
							<?php if ( $f['ad_2_big'] ) : ?>
								<span class="roastery__ad-big"><?php echo esc_html( $f['ad_2_big'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endif; ?>
					<?php if ( $f['ad_3_note'] || $f['ad_3_medium'] ) : ?>
						<li class="roastery__ad-line"<?php prairie_ash_el( $block, 'advert' ); ?>>
							<?php if ( $f['ad_3_note'] ) : ?>
								<span class="roastery__ad-note"><?php echo esc_html( $f['ad_3_note'] ); ?></span>
							<?php endif; ?>
							<?php if ( $f['ad_3_medium'] ) : ?>
								<span class="roastery__ad-medium"><?php echo esc_html( $f['ad_3_medium'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endif; ?>
				</ul>
				<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
			</div>
		<?php endif; ?>
		<div class="roastery__proof">
			<?php if ( $f['team_image'] || $f['team_caption'] ) : ?>
				<figure class="roastery__team">
					<?php if ( $f['team_image'] ) : ?>
						<div class="roastery__team-media media-ibg">
							<?php prairie_ash_image( $f['team_image'], prairie_ash_slot_sizes( 'roastery__team-media' ), $f['team_caption'] ); ?>
						</div>
					<?php endif; ?>
					<?php if ( $f['team_caption'] ) : ?>
						<figcaption class="roastery__team-caption"<?php prairie_ash_el( $block, 'caption' ); ?>><?php echo esc_html( $f['team_caption'] ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
			<div class="roastery__compare">
				<?php if ( $f['compare_title'] ) : ?>
					<h3 class="roastery__compare-title"<?php prairie_ash_el( $block, 'compare' ); ?>><?php echo esc_html( $f['compare_title'] ); ?></h3>
				<?php endif; ?>
				<?php if ( $f['compare_1_label'] || $f['compare_1_body'] ) : ?>
					<div class="roastery__compare-item">
						<?php if ( $f['compare_1_label'] ) : ?>
							<p class="roastery__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['compare_1_label'] ); ?></p>
						<?php endif; ?>
						<?php if ( $f['compare_1_body'] ) : ?>
							<p class="roastery__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['compare_1_body'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( $f['compare_2_label'] || $f['compare_2_facts'] || $f['compare_2_body'] ) : ?>
					<div class="roastery__compare-item">
						<?php if ( $f['compare_2_label'] ) : ?>
							<p class="roastery__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['compare_2_label'] ); ?></p>
						<?php endif; ?>
						<?php foreach ( $f['compare_2_facts'] as $row ) : ?>
							<?php if ( ! empty( $row['fact'] ) ) : ?>
								<p class="roastery__fact"<?php prairie_ash_el( $block, 'facts' ); ?>><?php echo esc_html( $row['fact'] ); ?></p>
							<?php endif; ?>
						<?php endforeach; ?>
						<?php if ( $f['compare_2_body'] ) : ?>
							<p class="roastery__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['compare_2_body'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
