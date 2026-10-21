<?php
/**
 * The coffee sign: one coffee's origin, name, notes, roast, price and button.
 *
 * Shared by the home page board and the shop list. A coffee that is out of
 * stock draws the design's sold-out sign, with the quantity and the button
 * disabled and the sold-out wording on the button.
 *
 * Expects $args['coffee'] from prairie_ash_coffee() and $args['block'], the
 * section block whose per-element panels style the card text.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_c     = $args['coffee'];
$prairie_ash_block = isset( $args['block'] ) ? $args['block'] : array();
?>
<article class="coffee-sign" data-motion="card">
	<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
	<div class="coffee-sign__body">
		<?php if ( $prairie_ash_c['origin'] ) : ?>
			<p class="coffee-sign__origin"<?php prairie_ash_el( $prairie_ash_block, 'card_origin' ); ?>><?php echo esc_html( $prairie_ash_c['origin'] ); ?></p>
		<?php endif; ?>
		<h3 class="coffee-sign__name"<?php prairie_ash_el( $prairie_ash_block, 'card_name' ); ?>><a class="coffee-sign__link" href="<?php echo esc_url( $prairie_ash_c['url'] ); ?>"><?php echo esc_html( $prairie_ash_c['name'] ); ?></a></h3>
		<?php if ( $prairie_ash_c['notes'] ) : ?>
			<p class="coffee-sign__notes"<?php prairie_ash_el( $prairie_ash_block, 'card_text' ); ?>><?php echo esc_html( $prairie_ash_c['notes'] ); ?></p>
		<?php endif; ?>
		<?php if ( $prairie_ash_c['roast'] ) : ?>
			<p class="coffee-sign__roast"<?php prairie_ash_el( $prairie_ash_block, 'card_text' ); ?>><?php echo esc_html( $prairie_ash_c['roast'] ); ?></p>
		<?php endif; ?>
		<p class="coffee-sign__price" data-motion="price"<?php prairie_ash_el( $prairie_ash_block, 'card_price' ); ?>><?php echo esc_html( $prairie_ash_c['price'] ); ?></p>
		<?php if ( $prairie_ash_c['sold_out'] ) : ?>
			<div class="coffee-sign__buy">
				<div class="field coffee-sign__quantity">
					<label class="field__label" for="quantity-<?php echo esc_attr( $prairie_ash_c['slug'] ); ?>"><?php echo esc_html( prairie_ash_wording( 'quantity' ) ); ?></label>
					<input class="input" id="quantity-<?php echo esc_attr( $prairie_ash_c['slug'] ); ?>" type="number" name="quantity" value="1" min="1" inputmode="numeric" disabled>
				</div>
				<button class="button button--cream coffee-sign__button" type="button" disabled><?php echo esc_html( $prairie_ash_c['sold_label'] ); ?></button>
			</div>
		<?php elseif ( $prairie_ash_c['add_id'] ) : ?>
			<button class="button button--cream coffee-sign__button" type="submit" form="pa-card-cart" name="add-to-cart" value="<?php echo esc_attr( $prairie_ash_c['add_id'] ); ?>"<?php echo prairie_ash_attrs( array( 'aria-label' => $prairie_ash_c['aria'] ) ); ?> data-card-add><?php echo esc_html( $prairie_ash_c['button'] ); ?></button>
		<?php endif; ?>
	</div>
</article>
