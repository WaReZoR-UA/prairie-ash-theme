<?php
/**
 * Fields of the roast schedule: the text and a table of roast days.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Roast schedule',
	'elements' => array(
		'label'   => 'Small title',
		'heading' => 'Heading',
		'body'    => 'Text',
		'table'   => 'Table text',
	),
	'fields'   => array(
		array( 'text', 'label', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array( 'text', 'col_day', 'Table heading, day', array( 'default' => 'Day' ) ),
		array( 'text', 'col_time', 'Table heading, start', array( 'default' => 'Starts' ) ),
		array( 'text', 'col_coffees', 'Table heading, coffees', array( 'default' => 'On the drum' ) ),
		array(
			'repeater',
			'days',
			'Roast days',
			array(
				'instructions' => 'One row per roast day, any number.',
				'collapsed'    => 'day',
				'button_label' => 'Add a roast day',
				'sub_fields'   => array(
					array( 'text', 'day', 'Day' ),
					array( 'text', 'time', 'Starts' ),
					array( 'text', 'coffees', 'On the drum' ),
				),
			),
		),
	),
);
