<?php
/**
 * Fields of the wholesale offer and inquiry band. The form is edited under
 * Formidable, Forms.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Wholesale offer and inquiry',
	'elements' => array(
		'heading' => 'Heading',
		'figures' => 'Figures',
		'caption' => 'Figure captions',
		'body'    => 'Text',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array(
			'repeater',
			'figures',
			'Figures',
			array(
				'instructions' => 'Each figure counts up as it scrolls into view. Any number.',
				'collapsed'    => 'caption',
				'button_label' => 'Add a figure',
				'sub_fields'   => array(
					array( 'text', 'number', 'Figure' ),
					array( 'text', 'caption', 'Caption' ),
				),
			),
		),
		array( 'textarea', 'body', 'Text' ),
		array( 'image', 'image', 'Photo' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
		array(
			'select',
			'form',
			'Form',
			array(
				'choices'      => array(),
				'allow_null'   => 1,
				'instructions' => 'The form is edited under Formidable, Forms. Its fields, wording and messages live there.',
			),
		),
	),
);
