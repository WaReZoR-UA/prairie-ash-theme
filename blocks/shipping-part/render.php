<?php
/**
 * Render template for the prairie-ash/shipping-part block.
 *
 * Two variants, both drawn by the design. The band colour is a block style
 * (deep, wall, paper) mapped to the design's own band modifier, with the link
 * colour that belongs to that band. The arrangement is a field: with a photo,
 * the text moves into the main column above the facts, as the pickup band is
 * drawn. The motion field names the way the band comes in, and the template
 * marks the element that motion reads: the facts list on the rate board, the
 * main column on a note, the whole band on a correction note, and the photo
 * whenever there is one.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f          = prairie_ash_fields( 'shipping-part' );
$band       = prairie_ash_block_style( $block, array( 'deep', 'wall', 'paper' ) );
$photo      = 'photo-beside' === $f['arrangement'] && $f['image'];
$link_class = 'paper' === $band ? 'text-link text-link--on-paper' : 'text-link text-link--on-wall';
$anchor     = sanitize_title( (string) $f['heading'] );
$facts      = $f['facts'];
$motion     = in_array( $f['motion'], array( 'board', 'note', 'correction' ), true ) ? $f['motion'] : 'board';
$allowed    = array(
	'a'    => array(
		'class' => true,
		'href'  => true,
	),
	'span' => array( 'class' => true ),
);

if ( $f['show_hours'] ) {
	$hours = array();
	foreach ( prairie_ash_hours() as $row ) {
		$hours[] = array(
			'label' => $row['label'],
			'value' => $row['time'],
		);
	}
	array_splice( $facts, min( 1, count( $facts ) ), 0, $hours );
}
$title_id = prairie_ash_heading_id( ( '' === $anchor ? 'shipping-part' : $anchor ) . '-heading', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'shipping-part', 'band', 'band--' . $band ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="shipping-part__container"<?php echo 'correction' === $motion ? ' data-motion="shipping-correction"' : ''; ?>>
		<?php if ( $photo ) : ?>
			<div class="shipping-part__media media-ibg" data-motion="shipping-photo">
				<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'shipping-part__media' ), $f['heading'] ); ?>
			</div>
		<?php else : ?>
			<div class="shipping-part__intro">
				<?php if ( $f['heading'] ) : ?>
					<h2 class="shipping-part__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $f['body'] ) : ?>
					<p class="shipping-part__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="shipping-part__main"<?php echo 'note' === $motion ? ' data-motion="shipping-note"' : ''; ?>>
			<?php if ( $photo ) : ?>
				<div class="shipping-part__lead">
					<?php if ( $f['heading'] ) : ?>
						<h2 class="shipping-part__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
					<?php endif; ?>
					<?php if ( $f['body'] ) : ?>
						<p class="shipping-part__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $facts ) : ?>
				<dl class="lv lv--fact lv--roomy lv--stack"<?php echo 'board' === $motion ? ' data-motion="shipping-board"' : ''; ?>>
					<?php foreach ( $facts as $fact ) : ?>
						<?php
						ob_start();
						prairie_ash_contact_text( $fact['value'] );
						$value = (string) ob_get_clean();
						if ( 'board' === $motion ) {
							// The rate board stamps each amount, so every amount is its own element, as the design marks it.
							$value = (string) preg_replace( '/\$\d[\d,]*(?:\.\d+)?/', '<span class="shipping-rate">$0</span>', $value );
						}
						?>
						<div class="lv__row"><dt class="lv__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $fact['label'] ); ?></dt><dd class="lv__value"<?php prairie_ash_el( $block, 'values' ); ?>><?php echo wp_kses( $value, $allowed ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
			<?php prairie_ash_link_field( $f['link'], $link_class ); ?>
		</div>
	</div>
</section>
