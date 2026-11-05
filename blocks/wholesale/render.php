<?php
/**
 * Render template for the prairie-ash/wholesale block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'wholesale' );
$title_id = prairie_ash_heading_id( 'wholesale-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'wholesale' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="wholesale__container">
		<?php if ( $f['heading'] ) : ?>
			<h2 class="wholesale__title" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
		<?php endif; ?>
		<div class="wholesale__row">
			<div class="wholesale__text">
				<div class="wholesale__text-top">
					<?php if ( $f['label'] ) : ?>
						<p class="wholesale__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['label'] ); ?></p>
					<?php endif; ?>
					<?php if ( $f['body'] ) : ?>
						<p class="wholesale__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
					<?php endif; ?>
					<?php if ( $f['terms'] ) : ?>
						<p class="wholesale__terms"<?php prairie_ash_el( $block, 'terms' ); ?>><?php echo esc_html( $f['terms'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-wall' ); ?>
			</div>
			<?php if ( $f['image'] ) : ?>
				<div class="wholesale__media media-ibg">
					<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'wholesale__media' ), $f['heading'], $f['image_decorative'] ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
