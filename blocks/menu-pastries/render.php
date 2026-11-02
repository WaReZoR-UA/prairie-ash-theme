<?php
/**
 * Render template for the prairie-ash/menu-pastries block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'menu-pastries' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'menu-pastries', 'band', 'band--wall' ) ); ?> aria-labelledby="menu-pastries-title">
	<div class="menu-pastries__container">
		<?php if ( $f['image'] ) : ?>
			<div class="menu-pastries__media media-ibg" data-motion="menu-case">
				<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'menu-pastries__media' ), $f['heading'], $f['image_decorative'] ); ?>
			</div>
		<?php endif; ?>
		<div class="menu-pastries__text">
			<?php if ( $f['heading'] ) : ?>
				<h2 class="menu-pastries__heading" id="menu-pastries-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="menu-pastries__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['items'] ) : ?>
				<dl class="menu-pastries__list lv lv--price" data-motion="menu-board">
					<?php foreach ( $f['items'] as $item ) : ?>
						<div class="lv__row"><dt class="lv__label"<?php prairie_ash_el( $block, 'items' ); ?>><?php echo esc_html( $item['name'] ); ?></dt><dd class="lv__value" data-motion="price"<?php prairie_ash_el( $block, 'prices' ); ?>><?php echo esc_html( $item['price'] ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
		</div>
	</div>
</section>
