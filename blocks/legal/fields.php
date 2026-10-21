<?php
/**
 * Fields of a legal page body: the contents list and the parts it links to.
 *
 * The contents list is built from the parts, so a part added, renamed or moved
 * changes the list with it.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Legal page body',
	'elements' => array(
		'contents' => 'Contents heading',
		'heading'  => 'Part headings',
		'text'     => 'Text',
		'labels'   => 'Row labels',
		'values'   => 'Row text',
	),
	'fields'   => array(
		array( 'text', 'label', 'Section name, read by screen readers', array( 'default' => 'Policy' ) ),
		array( 'text', 'contents_title', 'Contents heading', array( 'default' => 'On this page' ) ),
		array(
			'repeater',
			'parts',
			'Parts',
			array(
				'instructions' => 'One part per heading, any number. In the text, leave a blank line between paragraphs, and write [phone], [email] or [address] where the values from Site settings belong.',
				'collapsed'    => 'heading',
				'button_label' => 'Add a part',
				'sub_fields'   => array(
					array( 'text', 'heading', 'Heading' ),
					array( 'text', 'anchor', 'Anchor', array( 'instructions' => 'The end of the link to this part. Left empty, it is made from the heading.' ) ),
					array( 'textarea', 'text_before', 'Text before the rows', array( 'rows' => 4 ) ),
					array(
						'repeater',
						'rows',
						'Rows',
						array(
							'collapsed'    => 'label',
							'button_label' => 'Add a row',
							'sub_fields'   => array(
								array( 'text', 'label', 'Label' ),
								array( 'textarea', 'value', 'Text', array( 'rows' => 2 ) ),
							),
						),
					),
					array( 'textarea', 'text_after', 'Text after the rows', array( 'rows' => 3 ) ),
				),
			),
		),
	),
);
