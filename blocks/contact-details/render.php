<?php
/**
 * Render template for the prairie-ash/contact-details block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'contact-details' );
$title_id = prairie_ash_heading_id( 'contact-details-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'contact-details', 'band', 'band--deep' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="contact-details__container">
		<div class="contact-details__intro">
			<?php if ( $f['heading'] ) : ?>
				<h2 class="contact-details__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="contact-details__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
		</div>
		<div class="contact-details__contact" data-motion="contact-board">
			<?php if ( prairie_ash_contact_text_complete( $f['prose'] ) ) : ?>
				<p class="contact-details__prose contact-measure"<?php prairie_ash_el( $block, 'prose' ); ?>><?php prairie_ash_contact_text( $f['prose'] ); ?></p>
			<?php else : ?>
				<?php prairie_ash_editor_hint( $is_preview, __( 'The contact sentence is hidden while it is empty or names a Site setting that is empty.', 'prairie-ash' ) ); ?>
			<?php endif; ?>
			<?php if ( $f['links'] ) : ?>
				<ul class="contact-details__links">
					<?php foreach ( $f['links'] as $row ) : ?>
						<li><?php prairie_ash_link_field( $row['link'], 'text-link text-link--on-wall' ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
