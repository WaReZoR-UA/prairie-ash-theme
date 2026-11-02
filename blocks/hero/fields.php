<?php
/**
 * Fields of the home hero: the sign painted on the photographed brick wall,
 * its two facts, the text and actions below it, and the art-directed photo.
 *
 * Each entry is array( type, name, label, extra ). A 'default' in extra is the
 * design's own wording, printed when the field is empty.
 *
 * The small title and the label are kept for the content saved with the
 * earlier design of this section. The current design does not show them.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Hero',
	'elements' => array(
		'title'   => 'Heading',
		'facts'   => 'Facts',
		'lead'    => 'Text',
		'caption' => 'Photo caption',
	),
	'fields'   => array(
		array( 'text', 'title_line_1', 'Heading, first line' ),
		array( 'text', 'title_line_2', 'Heading, second line', array( 'instructions' => 'Drawn larger on mobile and tablet.' ) ),
		array( 'text', 'eyebrow', 'Small title (not shown)', array( 'instructions' => 'Kept from the earlier design of this section. The current design does not show it.' ) ),
		array( 'text', 'label', 'Label (not shown)', array( 'instructions' => 'Kept from the earlier design of this section. The current design does not show it.' ) ),
		array(
			'true_false',
			'show_today',
			'Show the "Open today" fact',
			array(
				'default_value' => 1,
				'ui'            => 1,
				'instructions'  => 'Reads today\'s opening hours from Site settings.',
			),
		),
		array( 'text', 'fact_term', 'Second fact, label', array( 'default' => 'Roast days' ) ),
		array( 'text', 'fact_value', 'Second fact, value', array( 'default' => 'Tuesday and Friday' ) ),
		array( 'textarea', 'lead', 'Text' ),
		array( 'link', 'primary_link', 'Button' ),
		array( 'link', 'secondary_link', 'Link beside the button' ),
		array( 'image', 'image', 'Photo, mobile', array( 'instructions' => 'The first picture visitors see, behind the sign. Shown below 768 pixels wide, and at every width when the photos below are empty. The alternative text set in the media library is read by screen readers.' ) ),
		array( 'image', 'image_tablet', 'Photo, tablet', array( 'instructions' => 'Shown from 768 to 991 pixels wide. Leave empty to use the mobile photo.' ) ),
		array( 'image', 'image_desktop', 'Photo, desktop', array( 'instructions' => 'Shown from 992 pixels wide. Leave empty to use the tablet or mobile photo.' ) ),
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
