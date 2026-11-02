<?php
/**
 * Render template for the prairie-ash/roastery-roaster block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'roastery-roaster' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'roastery-roaster', 'band', 'band--deep' ) ); ?> aria-labelledby="roastery-roaster-title">
	<div class="roastery-roaster__container" data-motion="roast-scene">
		<?php if ( $f['image'] ) : ?>
			<div class="roastery-roaster__media media-ibg">
				<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'roastery-roaster__media' ), $f['heading'], $f['image_decorative'] ); ?>
			</div>
		<?php endif; ?>
		<div class="roastery-roaster__text">
			<?php if ( $f['label'] ) : ?>
				<p class="roastery-roaster__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['label'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h2 class="roastery-roaster__heading" id="roastery-roaster-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php foreach ( prairie_ash_paragraphs( $f['body'] ) as $paragraph ) : ?>
				<p class="roastery-roaster__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $paragraph ); ?></p>
			<?php endforeach; ?>
			<figure class="roast-curve" data-motion="roast-curve">
				<svg class="roast-curve__chart" viewBox="0 0 640 290" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
					<g class="roast-curve__grid">
						<path d="M48 220.7H620M48 147.3H620M48 74H620"/>
					</g>
					<path class="roast-curve__axis" d="M48 250H620"/>
					<g class="roast-curve__ticks">
						<text x="40" y="225.7" text-anchor="end">100°</text>
						<text x="40" y="152.3" text-anchor="end">150°</text>
						<text x="40" y="79" text-anchor="end">200°</text>
						<text x="48" y="280" text-anchor="middle">0</text>
						<text x="191" y="280" text-anchor="middle">3</text>
						<text x="334" y="280" text-anchor="middle">6</text>
						<text x="477" y="280" text-anchor="middle">9</text>
						<text x="620" y="280" text-anchor="end">12 min</text>
					</g>
					<path class="roast-curve__ghost" d="M48 76.9C52 92.1 61.5 142 71.8 167.9C82.2 193.8 98.1 223.6 110 232.4C121.9 241.2 129.8 226.5 143.3 220.7C156.8 214.8 175.1 205 191 197.2C206.9 189.4 222.8 181.3 238.7 173.7C254.6 166.2 270.5 158.6 286.4 151.7C302.2 144.9 318.1 138.5 334 132.7C349.9 126.8 365.8 121.7 381.7 116.5C397.6 111.4 413.5 106.3 429.4 101.9C445.3 97.5 461.1 94 477 90.1C492.9 86.2 508.8 82.1 524.7 78.4C540.6 74.7 557.8 71.1 572.4 68.1C586.9 65.2 605.3 62 611.9 60.8"/>
					<g class="roast-curve__mark" data-curve-at="0">
						<circle cx="48" cy="74" r="5"/>
						<text x="58" y="62">Charge</text>
					</g>
					<g class="roast-curve__mark" data-curve-at="0.43">
						<path d="M286.4 150V250"/>
						<circle cx="286.4" cy="144.4" r="5"/>
						<text x="294" y="176">Yellow 5:00</text>
					</g>
					<g class="roast-curve__mark" data-curve-at="0.8">
						<path d="M485.1 86V250"/>
						<circle cx="485.1" cy="79.9" r="5"/>
						<text x="493" y="106">Crack 9:10</text>
					</g>
					<g class="roast-curve__mark" data-curve-at="0.999">
						<path d="M604.3 62V250"/>
						<circle cx="604.3" cy="56.4" r="5"/>
						<text x="612" y="38" text-anchor="end">Drop 11:40</text>
					</g>
					<path class="roast-curve__live" d="M48 74C52 88.7 61.9 136.3 71.8 162C81.8 187.7 95.7 219.4 107.6 228C119.5 236.6 129.4 219.9 143.3 213.3C157.2 206.7 175.1 196.5 191 188.4C206.9 180.3 222.8 172.3 238.7 164.9C254.6 157.6 270.5 151 286.4 144.4C302.2 137.8 318.1 131.2 334 125.3C349.9 119.5 365.8 114.3 381.7 109.2C397.6 104.1 413.5 98.9 429.4 94.5C445.3 90.1 467.7 85.2 477 82.8C486.3 80.4 477.2 81.8 485.1 79.9C493.1 77.9 510.2 74 524.7 71.1C539.2 68.1 559.1 64.7 572.4 62.3C585.6 59.8 599 57.4 604.3 56.4"/>
					<circle class="roast-curve__dot" cx="604.3" cy="56.4" r="7"/>
					<g class="roast-curve__readout">
						<g class="roast-curve__drum">
							<circle cx="150" cy="44" r="21"/>
							<path d="M150 23V65M129 44H171M135.2 29.2L164.8 58.8M164.8 29.2L135.2 58.8"/>
						</g>
						<text class="roast-curve__time" x="186" y="62">11:40</text>
						<text class="roast-curve__temp" x="290" y="62">212°C</text>
						<text class="roast-curve__stage" x="186" y="90">Drop</text>
						<ellipse class="roast-curve__bean" cx="404" cy="44" rx="12" ry="17" transform="rotate(24 404 44)" fill="#4e2a17"/>
						<path class="roast-curve__crease" d="M400 30C409 38 399 49 408 58"/>
					</g>
				</svg>
				<figcaption class="roast-curve__caption"><?php echo esc_html__( 'A Huila roast, minute by minute: charged at 200 °C, first crack at 9:10, dropped at 11:40 and 212 °C. The gold line is this roast, the dotted line last Tuesday\'s.', 'prairie-ash' ); ?></figcaption>
			</figure>
		</div>
	</div>
</section>
