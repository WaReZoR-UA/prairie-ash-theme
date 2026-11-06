<?php
/**
 * Render template for the prairie-ash/shop-list block.
 *
 * On the shop itself the list is WooCommerce's own product query, so a search
 * narrows it. Anywhere else, the editor preview included, it lists every
 * published product. A search that finds nothing draws the design's no-results
 * band in place of the list.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

// The list is WooCommerce's own: without it there are no coffees to draw, and the page stays whole.
if ( ! function_exists( 'prairie_ash_shop_products' ) ) {
	prairie_ash_editor_hint( $is_preview, __( 'The shop list needs WooCommerce, which is not active.', 'prairie-ash' ) );
	return;
}

$f        = prairie_ash_fields( 'shop-list' );
$products = prairie_ash_shop_products();
$coffees  = array_map( 'prairie_ash_coffee', $products );

// Every count is read from the cards actually listed, as the brew filter shows them.
$count_text = array();
foreach ( array( 'all', 'filter', 'espresso' ) as $key ) {
	$shown_coffees = 0;
	$shown_others  = array();
	foreach ( $products as $index => $product ) {
		if ( 'all' !== $key && ! in_array( $key, explode( ' ', $coffees[ $index ]['brew'] ), true ) ) {
			continue;
		}
		if ( $product->is_type( 'variable' ) ) {
			++$shown_coffees;
		} else {
			$shown_others[] = $coffees[ $index ]['name'];
		}
	}
	$count_text[ $key ] = str_replace(
		array( '[count]', '[others]' ),
		array( prairie_ash_number_word( $shown_coffees ), prairie_ash_shop_others( $shown_others ) ),
		$f[ 'count_' . $key ]
	);
}

if ( ! $coffees && '' !== prairie_ash_shop_search_term() ) :
	$empty_title = str_replace( '[search]', prairie_ash_shop_search_term(), $f['empty_title'] );
	$title_id    = prairie_ash_heading_id( 'shop-empty-title', $empty_title );
	?>
	<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'shop-empty', 'band', 'band--deep' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
		<div class="shop-empty__container">
			<h2 class="shop-empty__title"<?php echo $title_id ? ' id="' . esc_attr( $title_id ) . '"' : ''; ?><?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $empty_title ); ?></h2>
			<p class="shop-empty__body"<?php prairie_ash_el( $block, 'count' ); ?>><?php echo esc_html( $f['empty_body'] ); ?></p>
			<?php prairie_ash_link( $f['empty_link'], wc_get_page_permalink( 'shop' ), 'text-link text-link--on-wall' ); ?>
		</div>
	</section>
	<?php
	return;
endif;
$title_id = prairie_ash_heading_id( 'shop-list-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'shop-list', 'band', 'band--deep' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="shop-list__container">
		<div class="shop-list__head">
			<h2 class="shop-list__title"<?php echo $title_id ? ' id="' . esc_attr( $title_id ) . '"' : ''; ?><?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<p class="shop-list__count" aria-live="polite" data-shop-count data-count-all="<?php echo esc_attr( $count_text['all'] ); ?>" data-count-filter="<?php echo esc_attr( $count_text['filter'] ); ?>" data-count-espresso="<?php echo esc_attr( $count_text['espresso'] ); ?>"<?php prairie_ash_el( $block, 'count' ); ?>><?php echo esc_html( $count_text['all'] ); ?></p>
		</div>
		<ul class="shop-list__grid" data-motion="shop-list">
			<?php foreach ( $coffees as $coffee ) : ?>
				<li class="product-card" id="<?php echo esc_attr( prairie_ash_unique_id( 'coffee-' . $coffee['slug'] ) ); ?>" data-brew="<?php echo esc_attr( $coffee['brew'] ); ?>">
					<?php if ( $coffee['image'] ) : ?>
						<div class="product-card__media media-ibg">
							<?php prairie_ash_image( $coffee['image'], prairie_ash_slot_sizes( 'product-card__media' ), $coffee['name'] ); ?>
						</div>
					<?php endif; ?>
					<?php
					get_template_part(
						'parts/coffee-sign',
						null,
						array(
							'coffee' => $coffee,
							'block'  => $block,
						)
					);
					?>
				</li>
			<?php endforeach; ?>
			<?php if ( $f['next_heading'] ) : ?>
				<li class="shop-list__next">
					<?php if ( $f['next_image'] ) : ?>
						<div class="shop-list__next-media media-ibg">
							<?php prairie_ash_image( $f['next_image'], prairie_ash_slot_sizes( 'shop-list__next-media' ), $f['next_heading'] ); ?>
						</div>
					<?php endif; ?>
					<div class="shop-list__next-text">
						<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
						<?php if ( $f['next_label'] ) : ?>
							<p class="shop-list__next-label"<?php prairie_ash_el( $block, 'next_label' ); ?>><?php echo esc_html( $f['next_label'] ); ?></p>
						<?php endif; ?>
						<h3 class="shop-list__next-heading"<?php prairie_ash_el( $block, 'next_name' ); ?>><?php echo esc_html( $f['next_heading'] ); ?></h3>
						<?php if ( $f['next_body'] ) : ?>
							<p class="shop-list__next-body"<?php prairie_ash_el( $block, 'next_body' ); ?>><?php echo esc_html( $f['next_body'] ); ?></p>
						<?php endif; ?>
					</div>
				</li>
			<?php endif; ?>
		</ul>
	</div>
</section>
