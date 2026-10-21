<?php
/**
 * Fields of the home coffee board: the feature card, the coffees in rows of
 * two and the shipping note.
 *
 * The cards draw each coffee from WooCommerce, so a price, a sold-out state or
 * a new photo changes in the product and nowhere else.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Coffees we are roasting now',
	'elements' => array(
		'heading'     => 'Heading',
		'intro'       => 'Intro text',
		'card_origin' => 'Card small titles',
		'card_name'   => 'Card names',
		'card_text'   => 'Card notes and roast',
		'card_price'  => 'Card prices',
		'note'        => 'Shipping note',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'intro', 'Intro text' ),
		array( 'link', 'link', 'Link to the shop' ),
		array( 'image', 'image', 'Photo beside the feature card' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
		array(
			'post_object',
			'feature',
			'Feature coffee, under the photo',
			array(
				'post_type'     => array( 'product' ),
				'return_format' => 'id',
				'allow_null'    => 1,
			),
		),
		array(
			'relationship',
			'coffees',
			'Coffees on the board',
			array(
				'post_type'     => array( 'product' ),
				'return_format' => 'id',
				'filters'       => array( 'search' ),
				'instructions'  => 'Any number, in the order shown. The board sets them in rows of two.',
			),
		),
		array( 'textarea', 'note', 'Shipping note under the board' ),
	),
);
