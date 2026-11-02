<?php
/**
 * Render template for the prairie-ash/sent block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f     = prairie_ash_fields( 'sent' );
$scene = prairie_ash_motion_scene( 'prairie-ash/sent', (string) $f['scene'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'sent', 'band', 'band--wall' ) ); ?> aria-labelledby="sent-title">
	<div class="sent__container" data-motion="<?php echo esc_attr( $scene ); ?>-sent">
		<div class="form-panel form-notice sent__panel" role="status" tabindex="-1" data-form-notice data-motion="sign">
			<?php if ( $f['heading'] ) : ?>
				<h1 class="form-panel__title" id="sent-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="form-panel__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
			<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-paper' ); ?>
		</div>
		<?php if ( $f['image'] ) : ?>
			<div class="sent__media media-ibg">
				<?php
				// lcp-image: the photo beside the notice paints the Largest Contentful Paint of a sent page.
				prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'sent__media' ), $f['heading'], $f['image_decorative'] );
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
