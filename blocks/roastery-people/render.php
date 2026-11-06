<?php
/**
 * Render template for the prairie-ash/roastery-people block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'roastery-people' );
$title_id = prairie_ash_heading_id( 'roastery-people-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'roastery-people', 'band', 'band--deep' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="roastery-people__container">
		<div class="roastery-people__text">
			<?php if ( $f['label'] ) : ?>
				<p class="roastery-people__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['label'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h2 class="roastery-people__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="roastery-people__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
			<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-wall' ); ?>
		</div>
		<?php if ( $f['image'] ) : ?>
			<div class="roastery-people__media media-ibg">
				<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'roastery-people__media' ), $f['heading'], $f['image_decorative'], array(), array(), array( 'data-motion' => 'roast-photo' ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
