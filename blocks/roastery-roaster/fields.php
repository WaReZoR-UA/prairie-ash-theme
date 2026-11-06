<?php
/**
 * Fields of the roaster scene on the roastery page.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'The roaster behind the glass',
	'elements' => array(
		'label'   => 'Small title',
		'heading' => 'Heading',
		'body'    => 'Text',
		'caption' => 'Chart caption',
	),
	'fields'   => array(
		array( 'tab', 'tab_scene', 'Scene' ),
		array( 'text', 'label', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text', array( 'instructions' => 'Leave a blank line between paragraphs.' ) ),
		array( 'image', 'image', 'Photo' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
		array( 'tab', 'tab_chart', 'Roast chart' ),
		array(
			'message',
			'chart_note',
			'The chart',
			array( 'message' => 'The curve is a fixed drawing of one roast: charged at 200 °C, yellowing at 5:00, first crack at 9:10, dropped at 11:40 and 212 °C. The words on its marks and the caption below it can be changed, the times and temperatures belong to the drawing.' ),
		),
		array( 'text', 'mark_charge', 'Mark at the start', array( 'default' => 'Charge' ) ),
		array( 'text', 'mark_yellow', 'Mark at 5:00', array( 'default' => 'Yellow' ) ),
		array( 'text', 'mark_crack', 'Mark at 9:10', array( 'default' => 'Crack' ) ),
		array( 'text', 'mark_drop', 'Mark at 11:40, also the last stage on the readout', array( 'default' => 'Drop' ) ),
		array(
			'textarea',
			'caption',
			'Caption',
			array(
				'default'      => 'A Huila roast, minute by minute: charged at 200 °C, first crack at 9:10, dropped at 11:40 and 212 °C. The gold line is this roast, the dotted line last Tuesday\'s.',
				'instructions' => 'Read by screen readers in place of the chart. When the coffee on the drum changes, name it here.',
			),
		),
	),
);
