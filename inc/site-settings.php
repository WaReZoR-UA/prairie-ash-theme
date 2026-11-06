<?php
/**
 * Site settings: the business details, opening hours and the wording of the
 * header, the footer and the analytics notice.
 *
 * One options page, because these values appear on every page and the client
 * changes them in one place. Every wording field carries the design's own text
 * as its default and shows it as its placeholder, so an empty field prints the
 * default and the screen says what that default is.
 *
 * Contact details have NO default here. They are the client's, supplied in
 * writing, and are entered on this screen rather than written into a file.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * The settings registry: name, label, type, default and instructions.
 *
 * Keys are permanent once content exists. Labels can change freely.
 *
 * @since 1.0.0
 *
 * @return array<string, array> Settings keyed by field name, grouped by tab.
 */
function prairie_ash_settings_registry() {
	return array(
		'Business details' => array(
			'business_name'   => array( 'Business name', 'text', 'Prairie Ash Coffee & Roastery', '' ),
			'street'          => array( 'Street address', 'text', '', 'The first line, as printed large in the opening hours sections.' ),
			'locality'        => array( 'District, city, province and postal code', 'text', '', 'The second line under the street address.' ),
			'address_line'    => array( 'Address on one line', 'text', '', 'Used in the footer and in the sender line of the roast notes consent.' ),
			'phone_display'   => array( 'Phone number as shown', 'text', '', '' ),
			'phone_dial'      => array( 'Phone number for dialling', 'text', '', 'Digits with the country code, for example +12045550100. Visitors on a phone tap the number to call it.' ),
			'email'           => array( 'Email address', 'email', '', '' ),
			'website_label'   => array( 'Web address as printed', 'text', '', 'Printed in the sender line of the roast notes consent.' ),
			'map_url'         => array( 'Map link', 'url', '', 'Where the map pictures and the map links lead.' ),
			'instagram_url'   => array( 'Instagram link', 'url', '', 'The link is left out of the footer when this is empty.' ),
			'instagram_label' => array( 'Instagram link text', 'text', 'Prairie Ash on Instagram', '' ),
		),
		'Opening hours'    => array(
			'hours'            => array( 'Opening hours', 'hours', '', 'One row per group of days. The days decide which row the "Open today" line reads on each day of the week.' ),
			'hours_joiner'     => array( 'Word between opening and closing time', 'text', 'to', '' ),
			'roast_days_label' => array( 'Roast days label', 'text', 'Roast days', '' ),
			'roast_days'       => array( 'Roast days', 'text', 'Tuesday and Friday', '' ),
			'cupping_label'    => array( 'Cupping label, footer', 'text', 'Cupping', '' ),
			'cupping_when'     => array( 'Cupping time, footer', 'text', 'First Saturday, 10:00 to 11:30', '' ),
			'today_open'       => array( 'Today line, open', 'text', 'Open today', 'Followed by today\'s hours.' ),
			'today_closed'     => array( 'Today line, closed', 'text', 'Closed today, open', 'Followed by the next open day and its hours.' ),
			'today_tomorrow'   => array( 'Today line, the word for tomorrow', 'text', 'tomorrow', '' ),
		),
		'Header'           => array(
			'skip_link_label'          => array( 'Skip link', 'text', 'Skip to main content', 'Read by screen readers and shown on keyboard focus.' ),
			'wordmark_name'            => array( 'Wordmark, first line', 'text', 'Prairie Ash', '' ),
			'wordmark_line'            => array( 'Wordmark, second line', 'text', 'Coffee & Roastery', '' ),
			'wordmark_label'           => array( 'Wordmark, read by screen readers', 'text', 'Prairie Ash Coffee & Roastery, home', '' ),
			'directions_label'         => array( 'Directions button text', 'text', 'Get directions', '' ),
			'directions_link'          => array( 'Directions button link', 'page_link', '', 'Usually the Visit page. The button is left out when this is empty.' ),
			'cart_label'               => array( 'Cart link text', 'text', 'Cart', 'Followed by the number of items.' ),
			'cart_item_one'            => array( 'Cart, one item', 'text', 'item', '' ),
			'cart_item_many'           => array( 'Cart, several items', 'text', 'items', '' ),
			'nav_label'                => array( 'Main navigation, read by screen readers', 'text', 'Main', '' ),
			'pages_toggle_label'       => array( 'Page list button on phones', 'text', 'All pages', '' ),
			'pages_toggle_close_label' => array( 'Page list button on phones, open', 'text', 'Close page list', '' ),
		),
		'Footer'           => array(
			'footer_hours_heading' => array( 'Hours heading', 'text', 'Opening hours', '' ),
			'footer_touch_heading' => array( 'Links heading', 'text', 'Keep in touch', '' ),
			'roast_notes_label'    => array( 'Roast notes link text', 'text', 'Get our monthly roast notes', '' ),
			'roast_notes_link'     => array( 'Roast notes link', 'text', '', 'The page and the section of the signup form, for example the home page address followed by #signup. Left out when empty.' ),
			'analytics_link_label' => array( 'Analytics settings link text', 'text', 'Analytics settings', '' ),
			'copyright'            => array( 'Copyright line', 'text', 'Copyright 2026 Prairie Ash Coffee & Roastery', '' ),
		),
		'Search results'   => array(
			'order_search_title'       => array( 'Order confirmation, title in search results', 'text', 'Order confirmed, Prairie Ash Coffee & Roastery', 'Pages and coffees set theirs on their own edit screen. The order confirmation is the checkout page with an order attached, so its title is set here.' ),
			'order_search_description' => array( 'Order confirmation, description in search results', 'textarea', 'Your order is confirmed. What happens next, with the receipt for your coffee.', '' ),
		),
		'Analytics notice' => array(
			'analytics_title'      => array( 'Heading', 'text', 'This site counts visits without cookies', '' ),
			'analytics_text'       => array( 'Text', 'textarea', 'We count visits with a cookieless analytics tool. It sets no cookies, shows no ads and keeps no profile of you. Turn counting off now or later under Analytics settings in the footer.', '' ),
			'analytics_off_text'   => array( 'Text after counting is turned off', 'textarea', 'Counting is off on this browser. You can turn it back on under Analytics settings.', '' ),
			'analytics_on_button'  => array( 'Button, keep counting', 'text', 'Keep counting on', '' ),
			'analytics_off_button' => array( 'Button, turn counting off', 'text', 'Turn counting off', '' ),
		),
		'Moving pictures'  => array(
			'motion_pause_label' => array( 'Pause button of the moving roastery on Home', 'text', 'Pause the roastery', 'Shown while the roastery behind the window moves.' ),
			'motion_play_label'  => array( 'The same button while paused', 'text', 'Play the roastery', '' ),
			'roast_stage_names'  => array( 'Roast chart, stage names', 'textarea', "Charge\nTurning point\nDrying\nYellowing\nBrowning\nFirst crack\nDevelopment\nDrop", 'One line per stage, eight lines in this order. The roast chart on the Roastery page names the stage it has reached with them.' ),
		),
		'Site search'      => array(
			'search_heading'  => array( 'Search results, heading', 'text', 'Search results for "%s"', '%s is replaced by the words searched for.' ),
			'search_empty'    => array( 'Search results, nothing found', 'textarea', 'Nothing matches these words. Other words, or the menu above, may find it.', '' ),
			'entries_heading' => array( 'List of entries, heading', 'text', 'Latest entries', 'Shown on a list of entries that has no title of its own.' ),
			'entries_empty'   => array( 'List of entries, nothing published', 'textarea', 'Nothing has been published here yet.', '' ),
			'pages_previous'  => array( 'Page numbers, previous page', 'text', 'Previous', '' ),
			'pages_next'      => array( 'Page numbers, next page', 'text', 'Next', '' ),
			'home_link_label' => array( 'Link back to the home page', 'text', 'Back to the home page', 'Shown under an empty search and on the missing page.' ),
			'shop_link_label' => array( 'Link to the coffees', 'text', 'Browse the coffees', 'Shown under an empty search and on the missing page.' ),
		),
		'Missing page'     => array(
			'not_found_heading' => array( 'Heading', 'text', 'Page not found', 'The page shown for an address where nothing lives.' ),
			'not_found_text'    => array( 'Text', 'textarea', 'Nothing lives at this address. The page may have moved, or the address may be mistyped.', '' ),
		),
	);
}

/**
 * Read one site setting, falling back to the design's default when empty.
 *
 * @since 1.0.0
 *
 * @param string $name Field name from the registry.
 * @return mixed The stored value, or the default.
 */
function prairie_ash_setting( $name ) {
	static $defaults = null;

	if ( null === $defaults ) {
		$defaults = array();
		foreach ( prairie_ash_settings_registry() as $fields ) {
			foreach ( $fields as $key => $field ) {
				$defaults[ $key ] = $field[2];
			}
		}
	}

	$value = function_exists( 'get_field' ) ? get_field( $name, 'option' ) : '';

	if ( is_array( $value ) ) {
		return $value;
	}

	if ( null === $value || '' === trim( (string) $value ) ) {
		return isset( $defaults[ $name ] ) ? $defaults[ $name ] : '';
	}

	return $value;
}

/**
 * Register the options page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_register_settings_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => 'Site settings',
			'menu_title' => 'Site settings',
			'menu_slug'  => 'prairie-ash-settings',
			'capability' => 'edit_theme_options',
			'position'   => 59,
			'icon_url'   => 'dashicons-store',
			'redirect'   => false,
		)
	);
}
add_action( 'acf/init', 'prairie_ash_register_settings_page' );

/**
 * Register the site settings fields from the registry.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_register_settings_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$fields = array();

	foreach ( prairie_ash_settings_registry() as $tab => $group ) {
		$fields[] = array(
			'key'   => 'field_pa_settings_tab_' . sanitize_key( $tab ),
			'label' => $tab,
			'name'  => '',
			'type'  => 'tab',
		);

		foreach ( $group as $name => $field ) {
			$fields[] = prairie_ash_settings_field( $name, $field );
		}
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_pa_site_settings',
			'title'    => 'Site settings',
			'fields'   => $fields,
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'prairie-ash-settings',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'prairie_ash_register_settings_fields' );

/**
 * Build one settings field array from a registry entry.
 *
 * @since 1.0.0
 *
 * @param string $name  Field name.
 * @param array  $field Registry entry: label, type, default, instructions.
 * @return array Field definition.
 */
function prairie_ash_settings_field( $name, $field ) {
	list( $label, $type, $default, $instructions ) = $field;

	$base = array(
		'key'          => 'field_pa_settings_' . $name,
		'label'        => $label,
		'name'         => $name,
		'instructions' => $instructions,
	);

	if ( 'hours' === $type ) {
		return $base + array(
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add a row of days',
			'collapsed'    => 'field_pa_settings_hours_days_label',
			'sub_fields'   => array(
				array(
					'key'   => 'field_pa_settings_hours_days_label',
					'label' => 'Days, as printed',
					'name'  => 'days_label',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_pa_settings_hours_days',
					'label'        => 'Days this row covers',
					'name'         => 'days',
					'type'         => 'checkbox',
					'layout'       => 'horizontal',
					'choices'      => array(
						'1' => 'Monday',
						'2' => 'Tuesday',
						'3' => 'Wednesday',
						'4' => 'Thursday',
						'5' => 'Friday',
						'6' => 'Saturday',
						'0' => 'Sunday',
					),
					'instructions' => 'A day in no row reads as closed in the "Open today" line.',
				),
				array(
					'key'         => 'field_pa_settings_hours_open',
					'label'       => 'Opens',
					'name'        => 'open',
					'type'        => 'text',
					'placeholder' => '7:00',
				),
				array(
					'key'         => 'field_pa_settings_hours_close',
					'label'       => 'Closes',
					'name'        => 'close',
					'type'        => 'text',
					'placeholder' => '18:00',
				),
			),
		);
	}

	if ( 'page_link' === $type ) {
		return $base + array(
			'type'           => 'page_link',
			'post_type'      => array( 'page' ),
			'allow_archives' => 0,
			'allow_null'     => 1,
		);
	}

	return $base + array(
		'type'          => $type,
		'default_value' => '',
		'placeholder'   => $default,
		'rows'          => 'textarea' === $type ? 3 : null,
		'new_lines'     => '',
	);
}

/**
 * The opening hours rows, normalised for printing.
 *
 * @since 1.0.0
 *
 * @return array<int, array{label: string, days: string, open: string, close: string, time: string}>
 */
function prairie_ash_hours() {
	$rows   = prairie_ash_setting( 'hours' );
	$joiner = prairie_ash_setting( 'hours_joiner' );
	$out    = array();

	if ( ! is_array( $rows ) ) {
		return $out;
	}

	foreach ( $rows as $row ) {
		$label = isset( $row['days_label'] ) ? trim( (string) $row['days_label'] ) : '';
		$open  = isset( $row['open'] ) ? trim( (string) $row['open'] ) : '';
		$close = isset( $row['close'] ) ? trim( (string) $row['close'] ) : '';

		if ( '' === $label || '' === $open || '' === $close ) {
			continue;
		}

		$days  = isset( $row['days'] ) ? array_map( 'absint', (array) $row['days'] ) : array();
		$out[] = array(
			'label' => $label,
			'days'  => implode( ',', $days ),
			'open'  => $open,
			'close' => $close,
			'time'  => $open . ' ' . $joiner . ' ' . $close,
		);
	}

	return $out;
}

/**
 * The phone link target, from the dialling number.
 *
 * @since 1.0.0
 *
 * @return string A tel: URL, or an empty string when no number is set.
 */
function prairie_ash_phone_href() {
	$dial = preg_replace( '/[^0-9+]/', '', (string) prairie_ash_setting( 'phone_dial' ) );

	return '' === $dial ? '' : 'tel:' . $dial;
}

/**
 * The "Open today" line, from the opening hours in the cafe's own time zone.
 *
 * Printed by the server so the line is right without JavaScript. The design's
 * script recomputes it in the browser from the same hours, so a cached page
 * still reads correctly on the next day.
 *
 * @since 1.0.0
 *
 * @return string Empty when no hours are set.
 */
function prairie_ash_today_line() {
	$week = array_fill( 0, 7, '' );

	foreach ( prairie_ash_hours() as $row ) {
		foreach ( array_filter( explode( ',', $row['days'] ), 'strlen' ) as $day ) {
			$week[ (int) $day ] = $row['time'];
		}
	}

	$now   = new DateTimeImmutable( 'now', wp_timezone() );
	$today = (int) $now->format( 'w' );

	if ( '' !== $week[ $today ] ) {
		return prairie_ash_setting( 'today_open' ) . ' ' . $week[ $today ];
	}

	for ( $step = 1; $step < 7; $step++ ) {
		$next = ( $today + $step ) % 7;
		if ( '' !== $week[ $next ] ) {
			$when = 1 === $step ? prairie_ash_setting( 'today_tomorrow' ) : wp_date( 'l', $now->modify( '+' . $step . ' days' )->getTimestamp() );

			return prairie_ash_setting( 'today_closed' ) . ' ' . $when . ' ' . $week[ $next ];
		}
	}

	return '';
}
