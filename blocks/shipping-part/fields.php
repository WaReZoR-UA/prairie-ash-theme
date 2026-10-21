<?php
/**
 * Fields of one topic band on the shipping, pickup and returns page.
 *
 * The design draws this band two ways: a text column beside the facts, or a
 * photo beside the facts with the text above them. The arrangement is a field,
 * because it decides whether the photo is used. The band colour is a block
 * style, chosen in the Styles panel.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Shipping topic',
	'elements' => array(
		'heading' => 'Heading',
		'body'    => 'Text',
		'labels'  => 'Row labels',
		'values'  => 'Row text',
	),
	'fields'   => array(
		array(
			'button_group',
			'arrangement',
			'Arrangement',
			array(
				'choices'       => array(
					'text-beside'  => 'Text beside the facts',
					'photo-beside' => 'Photo beside the facts',
				),
				'default_value' => 'text-beside',
			),
		),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array(
			'image',
			'image',
			'Photo',
			array(
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_pa_shipping_part_arrangement',
							'operator' => '==',
							'value'    => 'photo-beside',
						),
					),
				),
			),
		),
		array(
			'repeater',
			'facts',
			'Facts',
			array(
				'instructions' => 'One row per fact, any number. Write [phone], [email] or [address] where the values from Site settings belong.',
				'collapsed'    => 'label',
				'button_label' => 'Add a fact',
				'sub_fields'   => array(
					array( 'text', 'label', 'Label' ),
					array( 'textarea', 'value', 'Text', array( 'rows' => 2 ) ),
				),
			),
		),
		array(
			'true_false',
			'show_hours',
			'Add the opening hours after the first fact',
			array(
				'ui'           => 1,
				'instructions' => 'The hours from Site settings.',
			),
		),
		array( 'link', 'link', 'Link' ),
	),
);
