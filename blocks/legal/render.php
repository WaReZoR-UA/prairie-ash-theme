<?php
/**
 * Render template for the prairie-ash/legal block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f     = prairie_ash_fields( 'legal' );
$scene = prairie_ash_motion_scene( 'prairie-ash/legal-title', (string) $f['scene'] );
$parts = array();
foreach ( $f['parts'] as $part ) {
	if ( '' === trim( (string) $part['heading'] ) ) {
		continue;
	}
	$part['anchor'] = sanitize_title( $part['anchor'] ? $part['anchor'] : $part['heading'] );
	$parts[]        = $part;
}
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'legal' ), array( 'aria-label' => $f['label'] ) ); ?>>
	<div class="legal__container">
		<?php if ( $parts ) : ?>
			<nav class="legal__contents" aria-labelledby="legal-contents-title" data-motion="<?php echo esc_attr( $scene . '-contents' ); ?>">
				<h2 class="legal__contents-title" id="legal-contents-title"<?php prairie_ash_el( $block, 'contents' ); ?>><?php echo esc_html( $f['contents_title'] ); ?></h2>
				<ul class="legal__contents-list">
					<?php foreach ( $parts as $part ) : ?>
						<li><a class="text-link text-link--on-paper" href="<?php echo esc_url( '#' . $part['anchor'] ); ?>"><?php echo esc_html( $part['heading'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
		<div class="legal__body">
			<?php foreach ( $parts as $part ) : ?>
				<div class="legal__part" id="<?php echo esc_attr( $part['anchor'] ); ?>" data-motion="<?php echo esc_attr( $scene . '-part' ); ?>">
					<h2 class="legal__heading"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $part['heading'] ); ?></h2>
					<?php foreach ( prairie_ash_paragraphs( $part['text_before'] ) as $paragraph ) : ?>
						<p class="legal__text"<?php prairie_ash_el( $block, 'text' ); ?>><?php prairie_ash_contact_text( $paragraph ); ?></p>
					<?php endforeach; ?>
					<?php if ( $part['rows'] ) : ?>
						<dl class="lv lv--fact lv--snug lv--stack">
							<?php foreach ( $part['rows'] as $row ) : ?>
								<div class="lv__row"><dt class="lv__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $row['label'] ); ?></dt><dd class="lv__value"<?php prairie_ash_el( $block, 'values' ); ?>><?php prairie_ash_contact_text( $row['value'] ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
					<?php foreach ( prairie_ash_paragraphs( $part['text_after'] ) as $paragraph ) : ?>
						<p class="legal__text"<?php prairie_ash_el( $block, 'text' ); ?>><?php prairie_ash_contact_text( $paragraph ); ?></p>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
