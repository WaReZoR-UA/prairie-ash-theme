<?php
/**
 * Render template for the prairie-ash/contact-details block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'contact-details' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'contact-details', 'band', 'band--deep' ) ); ?> aria-labelledby="contact-details-title">
	<div class="contact-details__container">
		<div class="contact-details__intro">
			<?php if ( $f['heading'] ) : ?>
				<h2 class="contact-details__heading" id="contact-details-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="contact-details__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
		</div>
		<div class="contact-details__contact" data-motion="contact-board">
			<p class="contact-details__prose contact-measure"<?php prairie_ash_el( $block, 'prose' ); ?>><?php prairie_ash_contact_text( $f['prose'] ); ?></p>
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
