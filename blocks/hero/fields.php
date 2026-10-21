<?php
/**
 * Fields of the home hero: the shop sign, the two actions and the window photo.
 *
 * Each entry is array( type, name, label, extra ). A 'default' in extra is the
 * design's own wording, printed when the field is empty.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Hero',
	'elements' => array(
		'title'   => 'Heading',
		'eyebrow' => 'Small title',
		'labels'  => 'Labels',
		'lead'    => 'Text',
		'caption' => 'Photo caption',
	),
	'fields'   => array(
		array( 'text', 'title_line_1', 'Heading, first line', array( 'instructions' => 'Drawn smaller, above the second line.' ) ),
		array( 'text', 'title_line_2', 'Heading, second line', array( 'instructions' => 'Drawn larger.' ) ),
		array( 'text', 'eyebrow', 'Small title' ),
		array( 'text', 'label', 'Label above the opening hours line' ),
		array(
			'true_false',
			'show_today',
			'Show the "Open today" line',
			array(
				'default_value' => 1,
				'ui'            => 1,
				'instructions'  => 'Reads today\'s opening hours from Site settings.',
			),
		),
		array( 'textarea', 'lead', 'Text' ),
		array( 'link', 'primary_link', 'Button' ),
		array( 'link', 'secondary_link', 'Link beside the button' ),
		array( 'image', 'image', 'Photo', array( 'instructions' => 'The first picture visitors see. The alternative text set in the media library is read by screen readers.' ) ),
		array(
			'true_false',
			'image_decorative',
			'The photo is decoration only',
			array(
				'ui'           => 1,
				'instructions' => 'Tick only when the photo carries nothing a reader would miss. Screen readers then skip it.',
			),
		),
		array( 'textarea', 'caption', 'Photo caption' ),
	),
);
