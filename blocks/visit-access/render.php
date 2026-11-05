<?php
/**
 * Render template for the prairie-ash/visit-access block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'visit-access' );
$title_id = prairie_ash_heading_id( 'visit-access-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'visit-access', 'band', 'band--wall' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="visit-access__container">
		<div class="visit-access__row">
			<div class="visit-access__text">
				<?php if ( $f['label'] ) : ?>
					<p class="visit-access__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['label'] ); ?></p>
				<?php endif; ?>
				<?php if ( $f['heading'] ) : ?>
					<h2 class="visit-access__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $f['body'] ) : ?>
					<p class="visit-access__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $f['facts'] ) : ?>
				<dl class="visit-access__table lv lv--feature-lead lv--stack" data-motion="visit-facts">
					<?php foreach ( $f['facts'] as $fact ) : ?>
						<div class="lv__row"><dt class="lv__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $fact['label'] ); ?></dt><dd class="lv__value"<?php prairie_ash_el( $block, 'values' ); ?>><?php echo esc_html( $fact['value'] ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
		</div>
		<?php if ( $f['image_entrance'] || $f['image_inside'] ) : ?>
			<div class="visit-access__media">
				<?php if ( $f['image_entrance'] ) : ?>
					<div class="visit-access__photo visit-access__photo--entrance media-ibg">
						<?php prairie_ash_image( $f['image_entrance'], prairie_ash_slot_sizes( 'visit-access__photo' ), $f['heading'] ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $f['image_inside'] ) : ?>
					<div class="visit-access__photo visit-access__photo--inside media-ibg">
						<?php prairie_ash_image( $f['image_inside'], prairie_ash_slot_sizes( 'visit-access__photo' ), $f['heading'] ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
