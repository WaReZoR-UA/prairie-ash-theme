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

$f       = prairie_ash_fields( 'shop-list' );
$coffees = array();
foreach ( prairie_ash_shop_products() as $product ) {
	$coffees[] = prairie_ash_coffee( $product );
}
$counts = array(
	'all'      => 0,
	'filter'   => 0,
	'espresso' => 0,
);
foreach ( prairie_ash_shop_products() as $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		continue;
	}
	$brew = (array) get_field( 'brew', $product->get_id() );
	++$counts['all'];
	$counts['filter']   += in_array( 'filter', $brew, true ) ? 1 : 0;
	$counts['espresso'] += in_array( 'espresso', $brew, true ) ? 1 : 0;
}
$count_text = array();
foreach ( $counts as $key => $number ) {
	$count_text[ $key ] = str_replace( '[count]', prairie_ash_number_word( $number ), $f[ 'count_' . $key ] );
}

if ( ! $coffees && '' !== prairie_ash_shop_search_term() ) :
	?>
	<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'shop-empty', 'band', 'band--deep' ) ); ?> aria-labelledby="shop-empty-title">
		<div class="shop-empty__container">
			<h2 class="shop-empty__title" id="shop-empty-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( str_replace( '[search]', prairie_ash_shop_search_term(), $f['empty_title'] ) ); ?></h2>
			<p class="shop-empty__body"<?php prairie_ash_el( $block, 'count' ); ?>><?php echo esc_html( $f['empty_body'] ); ?></p>
			<?php prairie_ash_link( $f['empty_link'], wc_get_page_permalink( 'shop' ), 'text-link text-link--on-wall' ); ?>
		</div>
	</section>
	<?php
	return;
endif;
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'shop-list', 'band', 'band--deep' ) ); ?> aria-labelledby="shop-list-title">
	<div class="shop-list__container">
		<div class="shop-list__head">
			<h2 class="shop-list__title" id="shop-list-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<p class="shop-list__count" aria-live="polite" data-shop-count data-count-all="<?php echo esc_attr( $count_text['all'] ); ?>" data-count-filter="<?php echo esc_attr( $count_text['filter'] ); ?>" data-count-espresso="<?php echo esc_attr( $count_text['espresso'] ); ?>"<?php prairie_ash_el( $block, 'count' ); ?>><?php echo esc_html( $count_text['all'] ); ?></p>
		</div>
		<ul class="shop-list__grid">
			<?php foreach ( $coffees as $coffee ) : ?>
				<li class="product-card" id="<?php echo esc_attr( $coffee['slug'] ); ?>" data-brew="<?php echo esc_attr( $coffee['brew'] ); ?>">
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
