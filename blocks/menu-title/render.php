<?php
/**
 * Render template for the prairie-ash/menu-title block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'menu-title' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'menu-title', 'band', 'band--deep' ) ); ?> aria-labelledby="menu-title">
	<div class="menu-title__container">
		<div class="menu-title__board" data-motion="sign">
			<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
			<div class="menu-title__row">
				<div class="menu-title__text">
					<?php if ( $f['eyebrow'] ) : ?>
						<p class="menu-title__eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['eyebrow'] ); ?></p>
					<?php endif; ?>
					<?php if ( $f['heading'] ) : ?>
						<h1 class="menu-title__heading" id="menu-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
					<?php endif; ?>
					<?php if ( $f['intro'] ) : ?>
						<p class="menu-title__intro"<?php prairie_ash_el( $block, 'intro' ); ?>><?php echo esc_html( $f['intro'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $f['image'] ) : ?>
					<div class="menu-title__media media-ibg">
						<?php
						// lcp-image: the title photo paints the Largest Contentful Paint of the menu page.
						prairie_ash_lcp_image( $f['image'], prairie_ash_slot_sizes( 'menu-title__media' ), $f['heading'], $f['image_decorative'] );
						?>
					</div>
				<?php endif; ?>
			</div>
			<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
		</div>
	</div>
</section>
