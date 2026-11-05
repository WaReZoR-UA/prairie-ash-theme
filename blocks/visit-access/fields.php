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
		array( 'image', 'image_entrance', 'Photo of the entrance', array( 'instructions' => 'Its description for screen readers is the Alternative Text in the media library, or the heading when that is empty.' ) ),
		array( 'true_false', 'image_entrance_decorative', 'The entrance photo is decoration only', array( 'ui' => 1 ) ),
		array( 'image', 'image_inside', 'Photo inside', array( 'instructions' => 'Its description for screen readers is the Alternative Text in the media library. The heading is used only when there is no entrance photo, so it is never read twice.' ) ),
		array( 'true_false', 'image_inside_decorative', 'The inside photo is decoration only', array( 'ui' => 1 ) ),
	),
);
