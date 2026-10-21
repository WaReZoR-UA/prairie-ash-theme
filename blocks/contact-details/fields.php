<?php
/**
 * Fields of the contact details band. The phone number, the email address and
 * the address come from Site settings and are linked where the text names them.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Contact details',
	'elements' => array(
		'heading' => 'Heading',
		'body'    => 'Text',
		'prose'   => 'Contact sentence',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array(
			'textarea',
			'prose',
			'Contact sentence',
			array(
				'default'      => 'Call the counter on [phone], write to [email], or come by [address].',
				'instructions' => 'Write [phone], [email] and [address] where the phone number, the email address and the address from Site settings belong. The phone number and the email address become links.',
			),
		),
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
	),
);
