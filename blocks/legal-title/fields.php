<?php
/**
 * Fields of a legal page title: the date it was last updated, the heading and
 * the intro, and which legal page of the design the title opens.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Legal page title',
	'elements' => array(
		'eyebrow' => 'Date line',
		'heading' => 'Heading',
		'intro'   => 'Text',
	),
	'fields'   => array(
		array(
			'select',
			'scene',
			'Page',
			array(
				'choices'       => array(
					'privacy'       => 'Privacy policy',
					'accessibility' => 'Accessibility statement',
				),
				'default_value' => 'privacy',
				'allow_null'    => 0,
				'ui'            => 0,
				'instructions'  => 'The legal page this title opens. It picks the way the title is lettered on load.',
			),
		),
		array( 'text', 'date_label', 'Words before the date', array( 'default' => 'Last updated' ) ),
		array(
			'date_picker',
			'date',
			'Date',
			array(
				'display_format' => 'F j, Y',
				'return_format'  => 'Ymd',
				'instructions'   => 'The date line is left out when this is empty.',
			),
		),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'intro', 'Text', array( 'instructions' => 'Write [phone], [email] or [address] where the values from Site settings belong. The phone number and the email address become links.' ) ),
	),
);
