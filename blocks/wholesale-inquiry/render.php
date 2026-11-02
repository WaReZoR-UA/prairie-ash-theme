<?php
/**
 * Render template for the prairie-ash/wholesale-inquiry block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'wholesale-inquiry' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'wholesale-inquiry', 'band', 'band--wall' ), array( 'id' => 'inquiry-form' ) ); ?> aria-labelledby="wholesale-offer-title">
	<div class="wholesale-inquiry__container">
		<div class="wholesale-inquiry__offer">
			<?php if ( $f['heading'] ) : ?>
				<h2 class="wholesale-inquiry__heading" id="wholesale-offer-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['figures'] || $f['body'] ) : ?>
				<div class="wholesale-inquiry__terms" data-motion="wholesale-board">
					<?php if ( $f['figures'] ) : ?>
						<ul class="figures">
							<?php foreach ( $f['figures'] as $figure ) : ?>
								<li class="figures__item"><span class="figures__number"<?php prairie_ash_el( $block, 'figures' ); ?>><?php echo esc_html( $figure['number'] ); ?></span> <span class="figures__caption"<?php prairie_ash_el( $block, 'caption' ); ?>><?php echo esc_html( $figure['caption'] ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( $f['body'] ) : ?>
						<p class="wholesale-inquiry__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $f['image'] ) : ?>
				<div class="wholesale-inquiry__media media-ibg">
					<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'wholesale-inquiry__media' ), $f['heading'], $f['image_decorative'] ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php prairie_ash_form( $f['form'], $is_preview ); ?>
	</div>
</section>
