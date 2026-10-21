<?php
/**
 * Fields of the shipping page title, with the links that jump down the page.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Shipping page title',
	'elements' => array(
		'eyebrow' => 'Small title',
		'heading' => 'Heading',
		'intro'   => 'Text',
	),
	'fields'   => array(
		array( 'text', 'eyebrow', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'intro', 'Text' ),
		array( 'text', 'jumps_label', 'Jump links, read by screen readers', array( 'default' => 'On this page' ) ),
		array(
			'repeater',
			'jumps',
			'Jump links',
			array(
				'instructions' => 'Link each one to a section further down by its anchor, for example #pickup. The anchor is set on the section, under Advanced.',
				'collapsed'    => 'link',
				'button_label' => 'Add a link',
				'sub_fields'   => array( array( 'link', 'link', 'Link' ) ),
			),
		),
		array( 'image', 'image', 'Photo', array( 'instructions' => 'The first picture on the page.' ) ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);
