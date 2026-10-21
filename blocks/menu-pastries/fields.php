<?php
/**
 * Fields of the pastries band: photo, text and a price list.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Pastries and prices',
	'elements' => array(
		'heading' => 'Heading',
		'body'    => 'Text',
		'items'   => 'Pastry names',
		'prices'  => 'Prices',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array(
			'repeater',
			'items',
			'Pastries',
			array(
				'instructions' => 'Any number, drag to reorder.',
				'collapsed'    => 'name',
				'button_label' => 'Add a pastry',
				'sub_fields'   => array(
					array( 'text', 'name', 'Pastry' ),
					array( 'text', 'price', 'Price' ),
				),
			),
		),
		array( 'image', 'image', 'Photo' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);
