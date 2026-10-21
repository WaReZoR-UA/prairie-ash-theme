<?php
/**
 * Fields of the opening hours and address band of the home page.
 *
 * The address and the hours themselves come from Site settings, so they are
 * changed once for the whole site.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Opening hours and address',
	'elements' => array(
		'heading' => 'Heading',
		'labels'  => 'Small titles',
		'street'  => 'Street address',
		'city'    => 'City line',
		'days'    => 'Days',
		'times'   => 'Times',
	),
	'fields'   => array(
		array( 'message', 'source', 'Where the values come from', array( 'message' => 'The address and the opening hours are set once for the whole site under Site settings.' ) ),
		array( 'text', 'heading', 'Heading', array( 'default' => 'Opening hours and address' ) ),
		array( 'text', 'find_label', 'Small title above the address', array( 'default' => 'Find us' ) ),
		array( 'link', 'link', 'Link under the address' ),
		array( 'text', 'hours_label', 'Small title above the hours', array( 'default' => 'Opening hours' ) ),
		array(
			'true_false',
			'show_roast_days',
			'Show the roast days row',
			array(
				'default_value' => 1,
				'ui'            => 1,
			),
		),
	),
);
