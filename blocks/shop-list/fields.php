<?php
/**
 * Fields of the shop list: the heading, the count line, the next-roast tile
 * and the wording shown when a search finds nothing.
 *
 * The coffees themselves are every published product in the shop, in the order
 * set in WooCommerce, with no limit.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Shop list',
	'elements' => array(
		'heading'     => 'Heading',
		'count'       => 'Count line',
		'card_origin' => 'Card small titles',
		'card_name'   => 'Card names',
		'card_text'   => 'Card notes and roast',
		'card_price'  => 'Card prices',
		'next_label'  => 'Next roast, small title',
		'next_name'   => 'Next roast, heading',
		'next_body'   => 'Next roast, text',
	),
	'fields'   => array(
		array( 'tab', 'tab_list', 'List' ),
		array( 'text', 'heading', 'Heading', array( 'default' => 'Roasting now' ) ),
		array(
			'text',
			'count_all',
			'Count line, all coffees',
			array(
				'default'      => '[count] coffees and a tasting set',
				'instructions' => '[count] is replaced by the number of coffees shown, written as a word.',
			),
		),
		array( 'text', 'count_filter', 'Count line, filter brewing', array( 'default' => '[count] coffees for filter brewing and a tasting set' ) ),
		array( 'text', 'count_espresso', 'Count line, espresso', array( 'default' => '[count] coffees for espresso and a tasting set' ) ),
		array( 'tab', 'tab_next', 'Next roast tile' ),
		array( 'text', 'next_label', 'Small title', array( 'instructions' => 'The tile after the last coffee. It is left out when the heading is empty.' ) ),
		array( 'text', 'next_heading', 'Heading' ),
		array( 'textarea', 'next_body', 'Text' ),
		array( 'image', 'next_image', 'Photo' ),
		array( 'tab', 'tab_empty', 'No results' ),
		array(
			'text',
			'empty_title',
			'Heading when a search finds nothing',
			array(
				'default'      => 'No coffee matches "[search]"',
				'instructions' => '[search] is replaced by what the visitor typed.',
			),
		),
		array( 'textarea', 'empty_body', 'Text when a search finds nothing', array( 'default' => 'Try a shorter search, or see all the coffees we are roasting now.' ) ),
		array( 'text', 'empty_link', 'Link text when a search finds nothing', array( 'default' => 'See all coffees' ) ),
	),
);
