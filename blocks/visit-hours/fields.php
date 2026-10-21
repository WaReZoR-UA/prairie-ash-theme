<?php
/**
 * Fields of the address and opening hours band of the visit page.
 *
 * The address, phone, email and hours come from Site settings.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Address and opening hours',
	'elements' => array(
		'heading' => 'Heading',
		'labels'  => 'Small titles',
		'street'  => 'Street address',
		'city'    => 'City line',
		'note'    => 'Note',
	),
	'fields'   => array(
		array( 'message', 'source', 'Where the values come from', array( 'message' => 'The address, the phone number, the email address and the opening hours are set once for the whole site under Site settings.' ) ),
		array( 'text', 'heading', 'Heading' ),
		array( 'text', 'address_label', 'Small title above the address', array( 'default' => 'Address' ) ),
		array( 'text', 'phone_label', 'Phone label', array( 'default' => 'Phone' ) ),
		array( 'text', 'email_label', 'Email label', array( 'default' => 'Email' ) ),
		array( 'link', 'button', 'Button under the address' ),
		array( 'text', 'hours_label', 'Small title above the hours', array( 'default' => 'Opening hours' ) ),
		array( 'textarea', 'note', 'Note under the hours' ),
	),
);
