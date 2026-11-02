<?php
/**
 * Render template for the prairie-ash/shop-tools block.
 *
 * The search posts to the shop with q, which the theme hands to the product
 * search, so the field keeps the markup's own name. The brew buttons filter the
 * list in the browser by each coffee's brew methods. The wrapper carries the
 * motion hook the shop scenes read, as the design wraps the tools.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f          = prairie_ash_fields( 'shop-tools' );
$search_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$query      = prairie_ash_shop_search_term();
?>
<div data-motion="shop-tools">
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'shop-tools' ), array( 'aria-label' => $f['label'] ) ); ?>>
	<div class="shop-tools__container">
		<form class="shop-tools__search field" role="search" action="<?php echo esc_url( $search_url ); ?>" method="get">
			<label class="field__label" for="shop-search"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $f['search_label'] ); ?></label>
			<input class="input" id="shop-search" type="search" name="q" value="<?php echo esc_attr( $query ); ?>" autocomplete="off">
		</form>
		<fieldset class="shop-tools__filter" data-shop-filter>
			<legend class="shop-tools__legend"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $f['filter_legend'] ); ?></legend>
			<div class="options options--buttons">
				<div class="options__item">
					<input id="filter-brewing" class="options__input" type="radio" name="brew" value="filter">
					<label for="filter-brewing" class="options__label"><span class="options__text"><?php echo esc_html( $f['filter_filter'] ); ?></span></label>
				</div>
				<div class="options__item">
					<input id="filter-espresso" class="options__input" type="radio" name="brew" value="espresso">
					<label for="filter-espresso" class="options__label"><span class="options__text"><?php echo esc_html( $f['filter_espresso'] ); ?></span></label>
				</div>
				<div class="options__item">
					<input id="filter-all" class="options__input" type="radio" name="brew" value="all" checked>
					<label for="filter-all" class="options__label"><span class="options__text"><?php echo esc_html( $f['filter_all'] ); ?></span></label>
				</div>
			</div>
		</fieldset>
	</div>
</section>
</div>
