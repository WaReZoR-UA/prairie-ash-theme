<?php
/**
 * Fields of the shop search and brew filter.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Shop search and filter',
	'elements' => array(
		'labels' => 'Labels',
	),
	'fields'   => array(
		array( 'text', 'label', 'Section name, read by screen readers', array( 'default' => 'Search and filter the coffees' ) ),
		array( 'text', 'search_label', 'Search label', array( 'default' => 'Search our coffees' ) ),
		array( 'text', 'filter_legend', 'Filter label', array( 'default' => 'Show coffees for' ) ),
		array( 'text', 'filter_filter', 'Button, filter brewing', array( 'default' => 'Filter brewing' ) ),
		array( 'text', 'filter_espresso', 'Button, espresso', array( 'default' => 'Espresso' ) ),
		array( 'text', 'filter_all', 'Button, all coffees', array( 'default' => 'All coffees' ) ),
	),
);
