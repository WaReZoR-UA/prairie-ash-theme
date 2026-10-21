<?php
/**
 * Fields of the free public cupping bill.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Free public cupping',
	'elements' => array(
		'label'   => 'Small title',
		'heading' => 'Heading',
		'date'    => 'Date line',
		'venue'   => 'Venue and status',
		'body'    => 'Text',
	),
	'fields'   => array(
		array( 'text', 'label', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'text', 'date', 'Next date and time', array( 'instructions' => 'For example "Next cupping: Saturday, November 7, 2026, 10:00 to 11:30".' ) ),
		array( 'text', 'venue', 'Venue' ),
		array( 'text', 'status', 'Status', array( 'instructions' => 'For example "Going ahead as planned", or a change of plan.' ) ),
		array( 'textarea', 'body', 'Text' ),
		array( 'link', 'link', 'Link' ),
		array( 'image', 'image', 'Photo' ),
		array( 'image', 'image_wide', 'Photo for tablets, wider crop', array( 'instructions' => 'Shown between 768 and 991 pixels wide. Leave empty to use the photo above at every width.' ) ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);
