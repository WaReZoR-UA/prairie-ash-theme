<?php
/**
 * Render template for the prairie-ash/menu-drinks block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'menu-drinks' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'menu-drinks', 'band', 'band--deep' ), array( 'aria-label' => $f['label'] ) ); ?>>
	<div class="menu-drinks__container">
		<?php foreach ( $f['groups'] as $group ) : ?>
			<div class="menu-drinks__group">
				<?php if ( $group['heading'] ) : ?>
					<h2 class="menu-drinks__heading"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $group['heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $group['items'] ) : ?>
					<dl class="menu-drinks__list lv lv--price">
						<?php foreach ( $group['items'] as $item ) : ?>
							<div class="lv__row"><dt class="lv__label"<?php prairie_ash_el( $block, 'items' ); ?>><?php echo esc_html( $item['name'] ); ?></dt><dd class="lv__value" data-motion="price"<?php prairie_ash_el( $block, 'prices' ); ?>><?php echo esc_html( $item['price'] ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
				<?php if ( $group['note'] ) : ?>
					<p class="menu-drinks__note"<?php prairie_ash_el( $block, 'note' ); ?>><?php echo esc_html( $group['note'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
		<?php prairie_ash_editor_hint( $is_preview && ! $f['groups'], __( 'Add a group of drinks.', 'prairie-ash' ) ); ?>
	</div>
</section>
