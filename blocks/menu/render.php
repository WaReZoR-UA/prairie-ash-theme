<?php
/**
 * Render template for the prairie-ash/menu block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'menu' );
$title_id = prairie_ash_heading_id( 'menu-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'menu' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="menu__container">
		<div class="menu__board">
			<?php if ( $f['heading'] ) : ?>
				<h2 class="menu__title" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['items'] ) : ?>
				<ul class="menu__items">
					<?php foreach ( $f['items'] as $item ) : ?>
						<li class="menu__item"><span class="menu__name"<?php prairie_ash_el( $block, 'items' ); ?>><?php echo esc_html( $item['name'] ); ?></span><span class="menu__price" data-motion="price"<?php prairie_ash_el( $block, 'prices' ); ?>><?php echo esc_html( $item['price'] ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( $f['price_note'] || $f['bakery'] || ! empty( $f['link']['url'] ) ) : ?>
				<div class="menu__notes">
					<?php if ( $f['price_note'] ) : ?>
						<p class="menu__price-note"<?php prairie_ash_el( $block, 'notes' ); ?>><?php echo esc_html( $f['price_note'] ); ?></p>
					<?php endif; ?>
					<?php if ( $f['bakery'] ) : ?>
						<p class="menu__bakery"<?php prairie_ash_el( $block, 'notes' ); ?>><?php echo esc_html( $f['bakery'] ); ?></p>
					<?php endif; ?>
					<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-wall' ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( $f['image'] ) : ?>
			<div class="menu__media media-ibg">
				<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'menu__media' ), $f['heading'], $f['image_decorative'] ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
