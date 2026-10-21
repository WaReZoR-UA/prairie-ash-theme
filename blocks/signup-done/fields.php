<?php
/**
 * Fields of the signup confirmation page: the double opt-in step.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Signup confirmation',
	'elements' => array(
		'eyebrow' => 'Small title',
		'heading' => 'Heading',
		'lead'    => 'Text',
		'help'    => 'Help heading',
		'labels'  => 'Help labels',
		'values'  => 'Help text',
		'sender'  => 'Sender line',
	),
	'fields'   => array(
		array( 'text', 'eyebrow', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'lead', 'Text' ),
		array( 'text', 'help_title', 'Help heading' ),
		array(
			'repeater',
			'help',
			'Help',
			array(
				'collapsed'    => 'label',
				'button_label' => 'Add a row',
				'sub_fields'   => array(
					array( 'text', 'label', 'Label' ),
					array( 'textarea', 'value', 'Text' ),
				),
			),
		),
		array( 'textarea', 'sender', 'Sender line', array( 'instructions' => 'Write [name], [address] or [website] where the values from Site settings belong.' ) ),
		array(
			'repeater',
			'links',
			'Links',
			array(
				'collapsed'    => 'link',
				'button_label' => 'Add a link',
				'sub_fields'   => array( array( 'link', 'link', 'Link' ) ),
			),
		),
		array( 'image', 'image', 'Photo', array( 'instructions' => 'The first picture on the page.' ) ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);
