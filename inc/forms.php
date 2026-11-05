<?php
/**
 * Formidable Forms in the design's markup.
 *
 * Each form's fields carry the design's markup in their Customize HTML boxes,
 * edited under Formidable, Forms. This file adds the design's classes to the
 * three wrappers Formidable prints around every form, so the form panel lays
 * out as the markup draws it, and prints a form inside a block.
 *
 * Formidable's own styling is switched off in its Global Settings ("Don't use
 * Formidable Styling on any page"), so every rule comes from the theme.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * The design classes of each form, keyed by the form's key in Formidable.
 *
 * @since 1.0.0
 *
 * The motion entry names the scene the design's motion layer plays on the form
 * panel, printed as the panel's data-motion attribute.
 *
 * @return array<string, array{form: string, panel: string, motion: string}>
 */
function prairie_ash_form_classes() {
	return array(
		'roast-notes' => array(
			'form'   => 'signup__form',
			'panel'  => '',
			'motion' => '',
		),
		'contact'     => array(
			'form'   => 'contact-message__form',
			'panel'  => 'form-panel form-panel--fill',
			'motion' => 'contact-note',
		),
		'wholesale'   => array(
			'form'   => 'wholesale-inquiry__form',
			'panel'  => 'form-panel',
			'motion' => 'wholesale-ticket',
		),
	);
}

/**
 * Print the design's form class on the form tag.
 *
 * Frm_form_classes is an action that echoes, as the vendor documents it.
 *
 * @since 1.0.0
 *
 * @param object $form The form.
 * @return void
 */
function prairie_ash_frm_form_classes( $form ) {
	$classes = prairie_ash_form_classes();

	if ( isset( $form->form_key, $classes[ $form->form_key ] ) ) {
		echo ' ' . esc_attr( $classes[ $form->form_key ]['form'] ) . ' pa-form';
	}
}
add_action( 'frm_form_classes', 'prairie_ash_frm_form_classes' );

/**
 * Put the design's panel classes on the wrapper that holds the fields.
 *
 * @since 1.0.0
 *
 * The values Formidable passes carry the form's options and its fields, not
 * its key, so the form is found through the first field's form_id.
 *
 * @param string $classes Classes.
 * @param array  $values  Form values: the form options and the fields.
 * @return string
 */
function prairie_ash_frm_fields_class( $classes, $values ) {
	$design = prairie_ash_form_classes();
	$key    = '';

	if ( ! empty( $values['fields'][0]['form_id'] ) && class_exists( 'FrmForm' ) ) {
		$form = FrmForm::getOne( (int) $values['fields'][0]['form_id'] );
		$key  = $form ? $form->form_key : '';
	}

	if ( isset( $design[ $key ] ) && '' !== $design[ $key ]['panel'] ) {
		$classes .= ' ' . $design[ $key ]['panel'];
	}

	return $classes;
}
add_filter( 'frm_form_fields_class', 'prairie_ash_frm_fields_class', 10, 2 );

/**
 * Put the design's motion scene on the wrapper that holds the fields.
 *
 * Formidable escapes the class filter above and offers no attribute filter for
 * that wrapper, so the attribute is added to the rendered form. The wrapper is
 * matched with the form_key field Formidable prints inside it, so a page with
 * several forms gives each panel its own scene.
 *
 * @since 1.1.0
 *
 * @param string $html The rendered form.
 * @return string
 */
function prairie_ash_frm_panel_motion( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, 'frm_form_fields' ) ) {
		return $html;
	}

	$design = prairie_ash_form_classes();

	return preg_replace_callback(
		'#<div class="(frm_form_fields [^"]*)">(.*?name="form_key" value="([^"]*)")#s',
		static function ( $found ) use ( $design ) {
			$key = $found[3];

			if ( empty( $design[ $key ]['motion'] ) ) {
				return $found[0];
			}

			return '<div class="' . $found[1] . '" data-motion="' . esc_attr( $design[ $key ]['motion'] ) . '">' . $found[2];
		},
		$html
	);
}
add_filter( 'frm_filter_final_form', 'prairie_ash_frm_panel_motion' );

/**
 * Leave the invalid state of a design form's fields to the design's script.
 *
 * Formidable marks every field aria-invalid="false" on a fresh form. The
 * design's script shows an error without touching that attribute, so a field
 * in error would still be announced as valid. The design's markup carries no
 * aria-invalid on a fresh form, so the false marks are dropped; a true mark
 * from a send Formidable rejected on the server stays.
 *
 * @since 1.1.0
 *
 * @param string $html The rendered form.
 * @return string
 */
function prairie_ash_frm_drop_valid_marks( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, 'aria-invalid="false"' ) ) {
		return $html;
	}

	return preg_replace_callback(
		'#<form\b[^>]*\bclass="[^"]*\bpa-form\b[^"]*"[^>]*>.*?</form>#s',
		static function ( $found ) {
			return str_replace( ' aria-invalid="false"', '', $found[0] );
		},
		$html
	);
}
add_filter( 'frm_filter_final_form', 'prairie_ash_frm_drop_valid_marks' );

/**
 * Whether a form is one of the design's forms.
 *
 * @since 1.0.0
 *
 * @param object|array|null $form Form object or values.
 * @return bool
 */
function prairie_ash_is_design_form( $form ) {
	$key = '';

	if ( is_object( $form ) && isset( $form->form_key ) ) {
		$key = $form->form_key;
	} elseif ( is_array( $form ) && isset( $form['form_key'] ) ) {
		$key = $form['form_key'];
	}

	return array_key_exists( $key, prairie_ash_form_classes() );
}

/**
 * Hand the design's validation to the design's forms.
 *
 * The design's script checks the fields and builds the error summary, so the
 * browser's own bubbles are switched off with novalidate, and data-error-summary
 * tells the script this form carries a summary box.
 *
 * @since 1.0.0
 *
 * @param string $attributes Extra attributes for the form tag.
 * @param object $form       The form.
 * @return string
 */
function prairie_ash_frm_form_attributes( $attributes, $form ) {
	if ( prairie_ash_is_design_form( $form ) ) {
		$attributes .= ' novalidate data-error-summary';
	}

	return $attributes;
}
add_filter( 'frm_form_attributes', 'prairie_ash_frm_form_attributes', 10, 2 );

/**
 * Draw the choices of a radio or checkbox field the way the design draws them.
 *
 * The design styles a choice with input + label, a sibling pair. Formidable
 * wraps each input in its own div and, unless told label=0 in the field's
 * Customize HTML, puts the input inside its label. With label=0, this filter
 * replaces each wrapper with the design's pair and takes the label text from
 * the choices set on the field, so the client still edits them in Formidable.
 *
 * @since 1.0.0
 *
 * @param string $html  Field HTML with its shortcodes replaced.
 * @param array  $field The field.
 * @param array  $atts  Render arguments, with the form under form.
 * @return string
 */
function prairie_ash_frm_choice_html( $html, $field, $atts ) {
	$type = isset( $field['type'] ) ? $field['type'] : '';
	$form = isset( $atts['form'] ) ? $atts['form'] : null;

	if ( ! in_array( $type, array( 'radio', 'checkbox' ), true ) || ! prairie_ash_is_design_form( $form ) ) {
		return $html;
	}

	$labels = array();
	foreach ( (array) $field['options'] as $option ) {
		$labels[] = is_array( $option ) ? ( isset( $option['label'] ) ? $option['label'] : '' ) : $option;
	}

	$index = 0;

	return preg_replace_callback(
		'#<div class="frm_(?:radio|checkbox)" id="[^"]*">\s*(<input[^>]*\bid="([^"]+)"[^>]*>)\s*</div>#',
		static function ( $found ) use ( $labels, $type, &$index ) {
			$text = isset( $labels[ $index ] ) ? $labels[ $index ] : '';
			++$index;
			// Formidable also prints the label=0 option of the shortcode onto the input as an attribute.
			$found[1] = str_replace( ' label="0"', '', $found[1] );

			if ( 'checkbox' === $type ) {
				return $found[1] . '<label class="checkbox__label" for="' . esc_attr( $found[2] ) . '"><span class="checkbox__text">' . esc_html( $text ) . '</span></label>';
			}

			return '<div class="options__item">' . $found[1] . '<label class="options__label" for="' . esc_attr( $found[2] ) . '"><span class="options__text">' . esc_html( $text ) . '</span></label></div>';
		},
		$html
	);
}
add_filter( 'frm_replace_shortcodes', 'prairie_ash_frm_choice_html', 10, 3 );

/**
 * Remember whether the form Formidable is about to render is one of the design's.
 *
 * Formidable hands its final form filter the markup alone, so the form is
 * noted here, just before its markup is built.
 *
 * @since 1.2.1
 *
 * @param object|null $form The form about to render, or null to only read.
 * @return bool Whether the form last noted is one of the design's.
 */
function prairie_ash_frm_rendering_design_form( $form = null ) {
	static $design = false;

	if ( null !== $form ) {
		$design = prairie_ash_is_design_form( $form );
	}

	return $design;
}

/**
 * Note the form Formidable is about to render.
 *
 * @since 1.2.1
 *
 * @param object $form The form.
 * @return void
 */
function prairie_ash_frm_note_form( $form ) {
	prairie_ash_frm_rendering_design_form( is_object( $form ) ? $form : new stdClass() );
}
add_action( 'frm_pre_get_form', 'prairie_ash_frm_note_form' );

/**
 * Fill the business placeholders in a form's own wording.
 *
 * A form's text, such as the sender line under the roast notes signup, can
 * write [name], [street], [address] or [website] where the values from Site
 * settings belong, the same placeholders the Shop wording takes, so the
 * business details are kept in one place. Only the design's forms are filled,
 * and never what a visitor typed: the value of every field and the text of
 * every text area are left as they are.
 *
 * @since 1.0.0
 * @since 1.2.1 Only the design's forms, and never a visitor's value.
 *
 * @param string $html The rendered form.
 * @return string
 */
function prairie_ash_frm_placeholders( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, '[' ) || ! prairie_ash_frm_rendering_design_form() ) {
		return $html;
	}

	$kept = array();
	$keep = static function ( $found ) use ( &$kept ) {
		$token          = "\u{E001}" . count( $kept ) . "\u{E001}";
		$kept[ $token ] = $found[0];
		return $token;
	};

	$html = (string) preg_replace_callback( '/<textarea\b[^>]*>.*?<\/textarea>/is', $keep, $html );
	$html = (string) preg_replace_callback( '/\svalue=("[^"]*"|\'[^\']*\')/i', $keep, $html );

	$html = strtr(
		$html,
		array(
			'[name]'    => esc_html( (string) prairie_ash_setting( 'business_name' ) ),
			'[street]'  => esc_html( (string) prairie_ash_setting( 'street' ) ),
			'[address]' => esc_html( prairie_ash_nbsp_postcode( (string) prairie_ash_setting( 'address_line' ) ) ),
			'[website]' => esc_html( (string) prairie_ash_setting( 'website_label' ) ),
		)
	);

	return strtr( $html, $kept );
}
add_filter( 'frm_filter_final_form', 'prairie_ash_frm_placeholders' );

/**
 * Print a Formidable form, by ID, inside a section block.
 *
 * In the editor preview Formidable believes it is on an admin screen and draws
 * its entry view, with no submit button and an extra Entry Key field, so it is
 * told otherwise for this one render.
 *
 * @since 1.0.0
 *
 * @param int|string $form_id    Form ID chosen in the block.
 * @param bool       $is_preview Whether this render is the editor preview.
 * @return void
 */
function prairie_ash_form( $form_id, $is_preview = false ) {
	$form_id = absint( $form_id );

	if ( ! $form_id || ! class_exists( 'FrmFormsController' ) ) {
		prairie_ash_editor_hint( $is_preview, __( 'Choose a form for this section.', 'prairie-ash' ) );
		return;
	}

	if ( $is_preview ) {
		add_filter( 'frm_is_admin', '__return_false' );
	}

	echo prairie_ash_form_html( $form_id );

	if ( $is_preview ) {
		remove_filter( 'frm_is_admin', '__return_false' );
	}
}

/**
 * A Formidable form's markup, as Formidable renders and escapes it.
 *
 * Listed in phpcs.xml.dist as pre-escaped: it returns the plugin's own form
 * output, which is markup by design and cannot pass through an escaper.
 *
 * @since 1.0.0
 *
 * @param int $form_id Form ID.
 * @return string
 */
function prairie_ash_form_html( $form_id ) {
	return (string) FrmFormsController::get_form_shortcode( array( 'id' => absint( $form_id ) ) );
}

/**
 * Offer the site's Formidable forms in a block's form select.
 *
 * @since 1.0.0
 *
 * @param array $field Field settings.
 * @return array
 */
function prairie_ash_form_choices( $field ) {
	$field['choices'] = array();

	if ( class_exists( 'FrmForm' ) ) {
		foreach ( (array) FrmForm::get_published_forms() as $form ) {
			$field['choices'][ $form->id ] = $form->name;
		}
	}

	return $field;
}
add_filter( 'acf/load_field/name=form', 'prairie_ash_form_choices' );
