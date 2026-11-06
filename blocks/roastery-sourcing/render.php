<?php
/**
 * Render template for the prairie-ash/roastery-sourcing block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'roastery-sourcing' );
$title_id = prairie_ash_heading_id( 'roastery-sourcing-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'roastery-sourcing', 'band', 'band--wall' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="roastery-sourcing__container">
		<div class="roastery-sourcing__intro">
			<?php if ( $f['label'] ) : ?>
				<p class="roastery-sourcing__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['label'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h2 class="roastery-sourcing__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="roastery-sourcing__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $f['origins'] ) : ?>
			<div class="roastery-sourcing__partners">
				<?php foreach ( $f['origins'] as $origin ) : ?>
					<article class="origin">
						<?php if ( $origin['image'] ) : ?>
							<div class="origin__media media-ibg">
								<?php prairie_ash_image( $origin['image'], prairie_ash_slot_sizes( 'origin__media' ), $origin['heading'], $origin['image_decorative'] ); ?>
							</div>
						<?php endif; ?>
						<?php if ( $origin['label'] ) : ?>
							<p class="origin__label"<?php prairie_ash_el( $block, 'card_label' ); ?>><?php echo esc_html( $origin['label'] ); ?></p>
						<?php endif; ?>
						<?php if ( $origin['heading'] ) : ?>
							<h3 class="origin__heading"<?php prairie_ash_el( $block, 'card_name' ); ?>><?php echo esc_html( $origin['heading'] ); ?></h3>
						<?php endif; ?>
						<?php if ( $origin['body'] ) : ?>
							<p class="origin__body"<?php prairie_ash_el( $block, 'card_text' ); ?>><?php echo esc_html( $origin['body'] ); ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
