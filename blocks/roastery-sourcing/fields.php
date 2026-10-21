<?php
/**
 * Fields of the sourcing band: the intro and one card per origin partner.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Where our beans come from',
	'elements' => array(
		'label'      => 'Small title',
		'heading'    => 'Heading',
		'body'       => 'Text',
		'card_label' => 'Card small titles',
		'card_name'  => 'Card headings',
		'card_text'  => 'Card text',
	),
	'fields'   => array(
		array( 'text', 'label', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array(
			'repeater',
			'origins',
			'Origins',
			array(
				'instructions' => 'One card per partner, any number. The cards sit side by side and wrap.',
				'collapsed'    => 'heading',
				'button_label' => 'Add an origin',
				'sub_fields'   => array(
					array( 'image', 'image', 'Photo' ),
					array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
					array( 'text', 'label', 'Small title' ),
					array( 'text', 'heading', 'Heading' ),
					array( 'textarea', 'body', 'Text' ),
				),
			),
		),
	),
);
