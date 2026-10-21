<?php
/**
 * Fields of the message band: the intro, a photo and the contact form. The form
 * itself is edited under Formidable, Forms.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Send a message',
	'elements' => array(
		'heading' => 'Heading',
		'body'    => 'Text',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array( 'link', 'link', 'Link' ),
		array( 'image', 'image', 'Photo' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
		array(
			'select',
			'form',
			'Form',
			array(
				'choices'      => array(),
				'allow_null'   => 1,
				'instructions' => 'The form is edited under Formidable, Forms. Its fields, wording and messages live there.',
			),
		),
	),
);
