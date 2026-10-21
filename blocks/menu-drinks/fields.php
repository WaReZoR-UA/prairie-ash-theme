<?php
/**
 * Fields of the drinks band: groups of drinks, each a price list with a note.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Drinks and prices',
	'elements' => array(
		'heading' => 'Group headings',
		'items'   => 'Drink names',
		'prices'  => 'Prices',
		'note'    => 'Notes',
	),
	'fields'   => array(
		array( 'text', 'label', 'Section name, read by screen readers', array( 'default' => 'Drinks' ) ),
		array(
			'repeater',
			'groups',
			'Groups',
			array(
				'instructions' => 'One group per kind of drink, any number. Each lists any number of drinks.',
				'collapsed'    => 'heading',
				'button_label' => 'Add a group',
				'sub_fields'   => array(
					array( 'text', 'heading', 'Heading' ),
					array(
						'repeater',
						'items',
						'Drinks',
						array(
							'collapsed'    => 'name',
							'button_label' => 'Add a drink',
							'sub_fields'   => array(
								array( 'text', 'name', 'Drink' ),
								array( 'text', 'price', 'Price', array( 'instructions' => 'As printed, for example $4.75 or $5.25 / $5.75.' ) ),
							),
						),
					),
					array( 'text', 'note', 'Note under the list' ),
				),
			),
		),
	),
);
