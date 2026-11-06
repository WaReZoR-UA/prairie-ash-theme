<?php
/**
 * Fields of the home roastery band: the window scene, the wall advert and the
 * label comparison.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Roastery behind the glass',
	'elements' => array(
		'label'   => 'Small titles',
		'heading' => 'Heading',
		'body'    => 'Text',
		'advert'  => 'Wall advert',
		'caption' => 'Photo caption',
		'compare' => 'Comparison heading',
		'facts'   => 'Facts on the bag',
	),
	'fields'   => array(
		array( 'tab', 'tab_window', 'Window' ),
		array( 'text', 'label', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array( 'link', 'link', 'Link' ),
		array( 'image', 'image', 'Photo' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
		array( 'tab', 'tab_advert', 'Wall advert' ),
		array( 'text', 'ad_1_big', 'Line 1, large' ),
		array( 'text', 'ad_1_note', 'Line 1, note after' ),
		array( 'text', 'ad_2_label', 'Line 2, label before' ),
		array( 'text', 'ad_2_big', 'Line 2, large' ),
		array( 'text', 'ad_3_note', 'Line 3, note before' ),
		array( 'text', 'ad_3_medium', 'Line 3, medium' ),
		array( 'tab', 'tab_proof', 'People and the bag' ),
		array( 'image', 'team_image', 'Photo of the bar and the roaster', array( 'instructions' => 'Its description for screen readers is the Alternative Text in the media library. The caption below is read as the caption, so it is not repeated as the description.' ) ),
		array( 'true_false', 'team_image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
		array( 'textarea', 'team_caption', 'Photo caption' ),
		array( 'text', 'compare_title', 'Comparison heading' ),
		array( 'text', 'compare_1_label', 'First column, small title' ),
		array( 'textarea', 'compare_1_body', 'First column, text' ),
		array( 'text', 'compare_2_label', 'Second column, small title' ),
		array(
			'repeater',
			'compare_2_facts',
			'Second column, facts',
			array(
				'collapsed'    => 'fact',
				'button_label' => 'Add a fact',
				'sub_fields'   => array( array( 'text', 'fact', 'Fact' ) ),
			),
		),
		array( 'textarea', 'compare_2_body', 'Second column, text' ),
	),
);
