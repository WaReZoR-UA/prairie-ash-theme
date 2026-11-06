<?php
/**
 * Render template for the prairie-ash/contact-message block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'contact-message' );
$title_id = prairie_ash_heading_id( 'contact-message-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'contact-message', 'band', 'band--wall' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="contact-message__container">
		<div class="contact-message__intro">
			<?php if ( $f['heading'] ) : ?>
				<h2 class="contact-message__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="contact-message__body contact-measure"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
			<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-wall' ); ?>
			<?php if ( $f['image'] ) : ?>
				<div class="contact-message__media media-ibg" data-motion="contact-photo">
					<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'contact-message__media' ), $f['heading'], $f['image_decorative'] ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php prairie_ash_form( $f['form'], $is_preview ); ?>
	</div>
</section>
