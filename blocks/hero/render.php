<?php
/**
 * Render template for the prairie-ash/hero block.
 *
 * The photographed brick wall runs up under the header, the sign is painted
 * on it, and the text and actions sit below. The photo is art-directed: one
 * export per layout, as picture sources. The hero scene keys its geometry by
 * the export's own width, so each source's sizes value is that width (see
 * prairie_ash_slot_sizes()).
 *
 * Available here, from the field plugin: $block, $content, $is_preview,
 * $post_id, $wp_block, $context.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f       = prairie_ash_fields( 'hero' );
$heading = trim( $f['title_line_1'] . ' ' . $f['title_line_2'] );

// The "Open today" fact: the label in the term, the hours in the value, split
// the way the design's script splits it when it recomputes the line.
$today_term  = '';
$today_value = '';
if ( $f['show_today'] ) {
	$today_value = prairie_ash_today_line();
	$today_leads = array(
		(string) prairie_ash_setting( 'today_open' ),
		(string) preg_replace( '/\s*\S+$/', '', (string) prairie_ash_setting( 'today_closed' ) ),
	);
	foreach ( $today_leads as $today_lead ) {
		if ( '' !== $today_lead && str_starts_with( $today_value, $today_lead . ' ' ) ) {
			$today_term  = rtrim( $today_lead, ',' );
			$today_value = substr( $today_value, strlen( $today_lead ) + 1 );
			break;
		}
	}
}
$has_fact = '' !== $f['fact_term'] || '' !== $f['fact_value'];

// Art direction, widest first, as the design orders its sources.
$art = array();
foreach ( array(
	'image_desktop' => '(min-width: 62em)',
	'image_tablet'  => '(min-width: 48em)',
) as $art_field => $art_media ) {
	if ( $f[ $art_field ] ) {
		$art[] = array(
			'media' => $art_media,
			'image' => $f[ $art_field ],
			'sizes' => prairie_ash_slot_sizes( 'image_desktop' === $art_field ? 'hero__media-desktop' : 'hero__media-tablet' ),
		);
	}
}
$title_id = prairie_ash_heading_id( 'hero-title', $heading );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'hero' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?> data-motion="hero">
	<?php
	if ( $f['image'] ) {
		// lcp-image: the hero photo paints the Largest Contentful Paint of the home page.
		prairie_ash_lcp_image(
			$f['image'],
			prairie_ash_slot_sizes( 'hero__media' ),
			$heading,
			$f['image_decorative'],
			array( 'class' => 'hero__image' ),
			$art,
			array( 'class' => 'hero__media' )
		);
	}
	?>
	<div class="hero__wall">
		<div class="hero__container">
			<div class="hero__sign" data-motion="sign">
				<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
				<?php if ( $heading ) : ?>
					<h1 class="hero__title" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'title' ); ?>>
						<?php if ( $f['title_line_1'] ) : ?>
							<span class="hero__title-line"><?php echo esc_html( $f['title_line_1'] ); ?></span>
						<?php endif; ?>
						<?php if ( $f['title_line_2'] ) : ?>
							<span class="hero__title-line hero__title-line--second"><?php echo esc_html( $f['title_line_2'] ); ?></span>
						<?php endif; ?>
					</h1>
				<?php endif; ?>
				<?php prairie_ash_editor_hint( $is_preview && ! $heading, __( 'Add the heading of the hero.', 'prairie-ash' ) ); ?>
				<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
			</div>
			<?php if ( '' !== $today_value || $has_fact ) : ?>
				<dl class="hero__facts"<?php prairie_ash_el( $block, 'facts' ); ?>>
					<?php if ( '' !== $today_value ) : ?>
						<div class="hero__fact" data-today>
							<dt class="hero__term" data-today-term><?php echo esc_html( $today_term ); ?></dt>
							<dd class="hero__value" data-today-value><?php echo esc_html( $today_value ); ?></dd>
						</div>
					<?php endif; ?>
					<?php if ( $has_fact ) : ?>
						<div class="hero__fact">
							<dt class="hero__term"><?php echo esc_html( $f['fact_term'] ); ?></dt>
							<dd class="hero__value"><?php echo esc_html( $f['fact_value'] ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>
			<?php endif; ?>
		</div>
	</div>
	<div class="hero__intro">
		<div class="hero__container">
			<?php if ( $f['lead'] ) : ?>
				<p class="hero__lead"<?php prairie_ash_el( $block, 'lead' ); ?>><?php echo esc_html( $f['lead'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $f['primary_link']['url'] ) || ! empty( $f['secondary_link']['url'] ) ) : ?>
				<div class="hero__actions">
					<?php prairie_ash_link_field( $f['primary_link'], 'button button--primary' ); ?>
					<?php prairie_ash_link_field( $f['secondary_link'], 'text-link text-link--on-wall' ); ?>
				</div>
			<?php endif; ?>
			<?php if ( $f['caption'] ) : ?>
				<p class="hero__caption"<?php prairie_ash_el( $block, 'caption' ); ?>><?php echo esc_html( $f['caption'] ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
