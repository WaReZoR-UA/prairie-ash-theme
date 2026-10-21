<?php
/**
 * Fields of the roast notes signup band. The form itself is a Formidable form,
 * edited under Formidable, Forms.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Roast notes signup',
	'elements' => array(
		'heading' => 'Heading',
		'body'    => 'Text',
		'label'   => 'Sample, small title',
		'sample'  => 'Sample text',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array( 'text', 'sample_label', 'Sample, small title' ),
		array( 'textarea', 'sample_text', 'Sample from the latest notes' ),
		array(
			'select',
			'form',
			'Form',
			array(
				'choices'      => array(),
				'allow_null'   => 1,
				'ui'           => 0,
				'instructions' => 'The form is edited under Formidable, Forms. Its fields, wording and messages live there.',
			),
		),
	),
);
