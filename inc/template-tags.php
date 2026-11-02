<?php
/**
 * Content-slot helpers.
 *
 * One helper per carrier shape, so the empty-field contract is implemented once.
 * Every helper prints nothing at all when its value is empty.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a field with a default for an empty value.
 *
 * Wording a visitor reads carries the design's text as its default, so a block
 * saved before the field existed, or a field left empty, prints the design's
 * wording rather than nothing.
 *
 * @since 1.0.0
 *
 * @param string $name    Field name.
 * @param string $fallback The design's wording.
 * @return string
 */
function prairie_ash_field_or( $name, $fallback ) {
	$value = function_exists( 'get_field' ) ? get_field( $name ) : '';

	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return $fallback;
	}

	return $value;
}

/**
 * Build an attribute string from name and value pairs, escaped.
 *
 * @since 1.0.0
 *
 * @param array<string, string> $attrs Attribute values keyed by name. Empty values are dropped.
 * @return string Leading space included when anything is printed.
 */
function prairie_ash_attrs( $attrs ) {
	$out = '';

	foreach ( (array) $attrs as $name => $value ) {
		if ( '' === (string) $value ) {
			continue;
		}
		$out .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
	}

	return $out;
}

/**
 * Print a link whose label and destination are both required.
 *
 * Half a link is worse than none, so either half missing prints nothing. The
 * target is printed only when the field returned one, and only the value it
 * returned.
 *
 * @since 1.0.0
 *
 * @param string $label   Visible text.
 * @param string $url     Destination.
 * @param string $classes Class attribute.
 * @param string $target  Link target from the field, empty for none.
 * @param array  $attrs   Further attributes keyed by name, escaped here.
 * @return void
 */
function prairie_ash_link( $label, $url, $classes = '', $target = '', $attrs = array() ) {
	if ( empty( $label ) || empty( $url ) ) {
		return;
	}

	printf(
		'<a class="%1$s" href="%2$s"%3$s>%4$s</a>',
		esc_attr( $classes ),
		esc_url( $url ),
		prairie_ash_attrs( array_merge( array( 'target' => (string) $target ), (array) $attrs ) ), // Escaped in prairie_ash_attrs().
		esc_html( $label )
	);
}

/**
 * Print a link from a link field value.
 *
 * The link field always returns title, url and target, with target either an
 * empty string or _blank, so the guard is the two values a link cannot do
 * without.
 *
 * @since 1.0.0
 *
 * @param array|null $link    Link field value.
 * @param string     $classes Class attribute.
 * @param array      $attrs   Further attributes keyed by name.
 * @return void
 */
function prairie_ash_link_field( $link, $classes = '', $attrs = array() ) {
	if ( empty( $link['url'] ) || empty( $link['title'] ) ) {
		return;
	}

	prairie_ash_link( $link['title'], $link['url'], $classes, (string) $link['target'], $attrs );
}

/**
 * The attachment ID behind an image field value.
 *
 * @since 1.0.0
 *
 * @param int|string|array $image Field value in any return format.
 * @return int
 */
function prairie_ash_attachment_id( $image ) {
	if ( is_array( $image ) ) {
		if ( isset( $image['ID'] ) ) {
			return (int) $image['ID'];
		}

		return isset( $image['id'] ) ? (int) $image['id'] : 0;
	}

	return (int) $image;
}

/**
 * Resolve the alt text for an image slot.
 *
 * Three outcomes, chosen explicitly: empty when the caller declares the picture
 * decorative, the attachment's own alternative text when it has one, and the
 * heading of the section or row the picture belongs to when it has none. The
 * heading is handed in by the template that holds both, never searched for.
 *
 * Nothing is escaped here. Wp_get_attachment_image() escapes every attribute.
 *
 * @since 1.0.0
 *
 * @param int|string|array $image      Attachment ID or field value.
 * @param string           $heading    Heading of the section or row.
 * @param bool             $decorative Whether the picture carries no information.
 * @return string
 */
function prairie_ash_image_alt( $image, $heading = '', $decorative = false ) {
	if ( $decorative ) {
		return '';
	}

	$alt           = '';
	$attachment_id = prairie_ash_attachment_id( $image );

	if ( $attachment_id > 0 ) {
		$alt = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	}

	$alt = wp_strip_all_tags( $alt, true );

	if ( '' === $alt ) {
		$alt = wp_strip_all_tags( (string) $heading, true );
	}

	return $alt;
}

/**
 * Print an image from the media library as a picture: AVIF and WebP sources,
 * then the img in the upload's own format as the fallback.
 *
 * Srcset, sizes and the alt text are decided here, once. The browser takes the
 * first source whose type it can decode, so every format offers the same
 * widths. Loading is left to core, which the theme narrows in inc/images.php.
 * The class is empty by default, which the attributes filter turns into no
 * class attribute at all, as the markup draws it.
 *
 * @since 1.0.0
 * @since 1.1.0 The $picture_attr parameter.
 *
 * @param int|string|array $image      Attachment ID or field value.
 * @param string           $sizes      The sizes attribute, from prairie_ash_slot_sizes().
 * @param string           $heading    Heading of the section or row, the alt fallback.
 * @param bool             $decorative Whether the picture carries no information.
 * @param array            $attr       Further attributes merged into the img.
 * @param array            $art        Art-directed images shown first, each an array of
 *                                     'media', 'image' and 'sizes'.
 * @param array            $picture_attr Attributes printed on the picture element, name => value.
 * @return void
 */
function prairie_ash_image( $image, $sizes = '100vw', $heading = '', $decorative = false, $attr = array(), $art = array(), $picture_attr = array() ) {
	$attachment_id = prairie_ash_attachment_id( $image );

	if ( $attachment_id <= 0 ) {
		return;
	}

	echo '<picture';
	foreach ( (array) $picture_attr as $name => $value ) {
		echo ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
	}
	echo '>';

	foreach ( $art as $entry ) {
		$art_id = prairie_ash_attachment_id( $entry['image'] );

		if ( $art_id > 0 ) {
			prairie_ash_picture_sources( $art_id, $entry['sizes'], $entry['media'] );
		}
	}

	prairie_ash_picture_sources( $attachment_id, $sizes );

	$defaults = array(
		'class'         => '',
		'alt'           => prairie_ash_image_alt( $attachment_id, $heading, $decorative ),
		'sizes'         => $sizes,
		'fetchpriority' => false,
	);

	echo wp_get_attachment_image( $attachment_id, 'full', false, array_merge( $defaults, (array) $attr ) );
	echo '</picture>';
}

/**
 * Print the image that paints the Largest Contentful Paint.
 *
 * Lcp-image: one per template, never lazy, fetched ahead of the rest.
 *
 * @since 1.0.0
 * @since 1.1.0 The $attr, $art and $picture_attr parameters.
 *
 * @param int|string|array $image        Attachment ID or field value.
 * @param string           $sizes        The sizes attribute.
 * @param string           $heading      Heading of the section, the alt fallback.
 * @param bool             $decorative   Whether the picture carries no information.
 * @param array            $attr         Further attributes merged into the img. Loading
 *                                       and fetchpriority are always set here.
 * @param array            $art          Art-directed images shown first, as in prairie_ash_image().
 * @param array            $picture_attr Attributes printed on the picture element, name => value.
 * @return void
 */
function prairie_ash_lcp_image( $image, $sizes = '100vw', $heading = '', $decorative = false, $attr = array(), $art = array(), $picture_attr = array() ) {
	prairie_ash_image(
		$image,
		$sizes,
		$heading,
		$decorative,
		array_merge(
			(array) $attr,
			array(
				'loading'       => false,
				'fetchpriority' => 'high',
			)
		),
		$art,
		$picture_attr
	);
}

/**
 * Print the editor-only hint for an empty slot.
 *
 * A hint is admin chrome: drawn from the editor stylesheet, never from a design
 * class, and labelled as not shown on the site.
 *
 * @since 1.0.0
 *
 * @param bool   $is_preview Whether this render is the editor preview.
 * @param string $message    What is missing.
 * @return void
 */
function prairie_ash_editor_hint( $is_preview, $message ) {
	if ( ! $is_preview ) {
		return;
	}

	printf(
		'<p class="pa-editor-hint">%1$s <span>%2$s</span></p>',
		esc_html( $message ),
		esc_html__( '(This note is shown in the editor only.)', 'prairie-ash' )
	);
}

/**
 * Split a textarea into paragraphs on blank lines.
 *
 * For a body whose every paragraph carries the design's class, where wpautop
 * would print unclassed paragraphs.
 *
 * @since 1.0.0
 *
 * @param string $text Textarea value.
 * @return string[] Paragraph texts, trimmed, empty ones dropped.
 */
function prairie_ash_paragraphs( $text ) {
	$parts = preg_split( '/\R\s*\R/', trim( (string) $text ) );

	return array_values( array_filter( array_map( 'trim', (array) $parts ), 'strlen' ) );
}

/**
 * Print one paragraph of client text that may carry inline markup.
 *
 * A web address typed in full becomes a link, which is the documented route to
 * a link inside multi-paragraph block text without a rich-text field.
 *
 * @since 1.0.0
 *
 * @param string $text Paragraph text.
 * @return void
 */
function prairie_ash_inline_html( $text ) {
	echo wp_kses_post( make_clickable( $text ) );
}

/**
 * Keep a Canadian postal code on one line, as the design does.
 *
 * @since 1.0.0
 *
 * @param string $text Text that may hold a postal code such as R3B 1E4.
 * @return string
 */
function prairie_ash_nbsp_postcode( $text ) {
	return (string) preg_replace( '/\b([A-Z]\d[A-Z]) (\d[A-Z]\d)\b/', "$1\u{00a0}$2", (string) $text );
}

/**
 * Print text with each range of clock times kept on one line, as the design
 * does for the monthly tasting time in the footer ("First Saturday, 10:00 to 11:30").
 *
 * The text is escaped first and only the span around a range such as
 * "10:00 to 11:30" is markup, whatever word joins the two times.
 *
 * @since 1.1.0
 *
 * @param string $text Text that may hold a range of clock times.
 * @return void
 */
function prairie_ash_nowrap_times( $text ) {
	$html = (string) preg_replace( '/\b\d{1,2}:\d{2}\s+\S+\s+\d{1,2}:\d{2}\b/u', '<span class="nowrap">$0</span>', esc_html( $text ) );

	echo wp_kses( $html, array( 'span' => array( 'class' => true ) ) );
}

/**
 * Print client text in which [phone], [email], [address], [name], [street]
 * and [website] stand for the values in Site settings, with the phone number
 * and email address linked.
 *
 * The text is escaped first and only the placeholders become markup, built
 * from escaped values, so nothing the client typed is printed as markup.
 *
 * @since 1.0.0
 *
 * @param string $text Text with placeholders.
 * @return void
 */
function prairie_ash_contact_text( $text ) {
	$phone = prairie_ash_setting( 'phone_display' );
	$email = prairie_ash_setting( 'email' );
	$parts = array(
		'[phone]'   => $phone ? sprintf( '<a class="inline-link" href="%1$s">%2$s</a>', esc_url( prairie_ash_phone_href() ), esc_html( $phone ) ) : '',
		'[email]'   => $email ? sprintf( '<a class="inline-link" href="%1$s">%2$s</a>', esc_url( 'mailto:' . $email ), esc_html( $email ) ) : '',
		'[address]' => esc_html( prairie_ash_nbsp_postcode( prairie_ash_setting( 'address_line' ) ) ),
		'[name]'    => esc_html( prairie_ash_setting( 'business_name' ) ),
		'[street]'  => esc_html( prairie_ash_setting( 'street' ) ),
		'[website]' => esc_html( prairie_ash_setting( 'website_label' ) ),
	);

	echo wp_kses(
		strtr( esc_html( $text ), $parts ),
		array(
			'a' => array(
				'class' => true,
				'href'  => true,
			),
		)
	);
}

/**
 * What the visitor typed into the shop search, from the q parameter.
 *
 * @since 1.0.0
 *
 * @return string Sanitised, empty when there is no search.
 */
function prairie_ash_shop_search_term() {
	// A read-only search term, the same as core's own s parameter: no state changes, so no nonce.
	$term = filter_input( INPUT_GET, 'q', FILTER_UNSAFE_RAW );

	return is_string( $term ) ? sanitize_text_field( wp_unslash( $term ) ) : '';
}

/**
 * A small number written as a word with a capital, as the design writes counts.
 *
 * @since 1.0.0
 *
 * @param int $number Number.
 * @return string For example "Six". Numbers above twelve stay figures.
 */
function prairie_ash_number_word( $number ) {
	$words = array( 'No', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve' );

	return isset( $words[ $number ] ) ? $words[ $number ] : (string) $number;
}

/**
 * A page's content rendered as the_content() renders it.
 *
 * For a template that shows another page's sections, such as the shop showing
 * the Shop page. The content passes through core's the_content filter, which is
 * the same output the_content() prints.
 *
 * @since 1.0.0
 *
 * @param WP_Post $page The page.
 * @return string
 */
function prairie_ash_page_content( $page ) {
	return (string) apply_filters( 'the_content', $page->post_content );
}
