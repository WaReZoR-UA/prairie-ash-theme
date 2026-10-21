<?php
/**
 * Fields of the team band: one card per person, then a button and a link.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'About, the team',
	'elements' => array(
		'heading' => 'Heading',
		'name'    => 'Names',
		'bio'     => 'Bios',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array(
			'repeater',
			'people',
			'People',
			array(
				'instructions' => 'One card per person, any number. The cards sit side by side and wrap.',
				'collapsed'    => 'name',
				'button_label' => 'Add a person',
				'sub_fields'   => array(
					array( 'image', 'image', 'Photo', array( 'instructions' => 'Printed beside the name. Without alternative text in the media library it is skipped by screen readers, because the name is read right after it.' ) ),
					array( 'text', 'name', 'Name and role' ),
					array( 'textarea', 'bio', 'Bio' ),
				),
			),
		),
		array( 'link', 'button', 'Button' ),
		array( 'link', 'link', 'Link beside the button' ),
	),
);
