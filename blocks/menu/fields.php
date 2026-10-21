<?php
/**
 * Fields of the home menu board: a few drinks and pastries with prices.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Menu highlights',
	'elements' => array(
		'heading' => 'Heading',
		'items'   => 'Item names',
		'prices'  => 'Prices',
		'notes'   => 'Notes',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array(
			'repeater',
			'items',
			'Items on the board',
			array(
				'instructions' => 'Any number, drag to reorder.',
				'collapsed'    => 'name',
				'button_label' => 'Add an item',
				'sub_fields'   => array(
					array( 'text', 'name', 'Item' ),
					array( 'text', 'price', 'Price', array( 'instructions' => 'As printed, for example $4.75.' ) ),
				),
			),
		),
		array( 'text', 'price_note', 'Price note' ),
		array( 'textarea', 'bakery', 'Bakery note' ),
		array( 'link', 'link', 'Link to the full menu' ),
		array( 'image', 'image', 'Photo' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);
