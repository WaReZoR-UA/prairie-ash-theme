<?php
/**
 * Render template for the prairie-ash/signup-done block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'signup-done' );
$title_id = prairie_ash_heading_id( 'signup-done-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'signup-done', 'band', 'band--wall' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="signup-done__container" data-motion="signup-sign">
		<div class="signup-done__message">
			<div class="signup-done__heading">
				<?php if ( $f['eyebrow'] ) : ?>
					<p class="eyebrow signup-done__eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( $f['heading'] ) : ?>
					<h1 class="signup-done__title" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
				<?php endif; ?>
				<?php if ( $f['lead'] ) : ?>
					<p class="signup-done__lead"<?php prairie_ash_el( $block, 'lead' ); ?>><?php echo esc_html( $f['lead'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $f['help_title'] || $f['help'] ) : ?>
				<div class="signup-done__help">
					<?php if ( $f['help_title'] ) : ?>
						<h2 class="signup-done__help-title"<?php prairie_ash_el( $block, 'help' ); ?>><?php echo esc_html( $f['help_title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( $f['help'] ) : ?>
						<dl class="lv lv--fact lv--roomy lv--stack">
							<?php foreach ( $f['help'] as $row ) : ?>
								<div class="lv__row"><dt class="lv__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $row['label'] ); ?></dt><dd class="lv__value"<?php prairie_ash_el( $block, 'values' ); ?>><?php echo esc_html( $row['value'] ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $f['sender'] ) : ?>
				<p class="signup-done__sender"<?php prairie_ash_el( $block, 'sender' ); ?>><?php prairie_ash_contact_text( $f['sender'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['links'] ) : ?>
				<div class="signup-done__links">
					<?php foreach ( $f['links'] as $row ) : ?>
						<?php prairie_ash_link_field( $row['link'], 'text-link text-link--on-wall' ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( $f['image'] ) : ?>
			<div class="signup-done__media media-ibg">
				<?php
				// lcp-image: the photo paints the Largest Contentful Paint of the signup confirmation page.
				prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'signup-done__media' ), $f['heading'], $f['image_decorative'] );
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
