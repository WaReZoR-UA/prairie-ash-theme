<?php
/**
 * Fields of the cupping band on the roastery page, with the date plate.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Cupping, in full',
	'elements' => array(
		'label'   => 'Small title',
		'heading' => 'Heading',
		'body'    => 'Text',
		'next'    => 'Next date text',
	),
	'fields'   => array(
		array( 'text', 'label', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array(
			'date_picker',
			'next_date',
			'Date of the next cupping',
			array(
				'display_format' => 'F j, Y',
				'return_format'  => 'Ymd',
				'first_day'      => 0,
				'instructions'   => 'Drawn on the date plate. The plate is left out when this is empty.',
			),
		),
		array( 'textarea', 'next_text', 'Text beside the date plate' ),
		array( 'link', 'link', 'Link' ),
		array( 'image', 'image', 'Photo' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);
