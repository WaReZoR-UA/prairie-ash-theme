<?php
/**
 * Fields of the getting-here band: text, ways to arrive and the map picture
 * that links out to the map service.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Getting here, in full',
	'elements' => array(
		'heading' => 'Heading',
		'body'    => 'Text',
		'labels'  => 'Row labels',
		'values'  => 'Row text',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array(
			'repeater',
			'ways',
			'Ways to get here',
			array(
				'instructions' => 'One row per way, any number.',
				'collapsed'    => 'label',
				'button_label' => 'Add a way',
				'sub_fields'   => array(
					array( 'text', 'label', 'Label' ),
					array( 'text', 'value', 'Text' ),
				),
			),
		),
		array( 'text', 'map_link_label', 'Map link text', array( 'instructions' => 'Leads to the map link set under Site settings.' ) ),
		array( 'image', 'map_image', 'Map picture, phones' ),
		array( 'image', 'map_image_wide', 'Map picture, wider screens', array( 'instructions' => 'Shown from 768 pixels wide. Leave empty to use the picture above at every width.' ) ),
	),
);
