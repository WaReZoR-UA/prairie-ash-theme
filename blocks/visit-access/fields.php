<?php
/**
 * Fields of the premises accessibility band.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Accessibility of the cafe',
	'elements' => array(
		'label'   => 'Small title',
		'heading' => 'Heading',
		'body'    => 'Text',
		'labels'  => 'Row labels',
		'values'  => 'Row text',
	),
	'fields'   => array(
		array( 'text', 'label', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array(
			'repeater',
			'facts',
			'Facts',
			array(
				'instructions' => 'One row per fact, any number.',
				'collapsed'    => 'label',
				'button_label' => 'Add a fact',
				'sub_fields'   => array(
					array( 'text', 'label', 'Label' ),
					array( 'text', 'value', 'Text' ),
				),
			),
		),
		array( 'image', 'image_entrance', 'Photo of the entrance' ),
		array( 'image', 'image_inside', 'Photo inside' ),
	),
);
