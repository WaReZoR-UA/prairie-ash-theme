<?php
/**
 * A coffee: breadcrumb, summary with the add-to-cart form, where it comes from
 * and how Mara brews it.
 *
 * The form posts to WooCommerce's own add-to-cart handler. The bag size and
 * the grind are the product's variation attributes, drawn as the design's
 * option buttons, and the variation is found from them on the server, so the
 * form works with JavaScript off.
 *
 * Overrides WooCommerce's single-product.php.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$prairie_ash_product = wc_get_product( get_the_ID() );
	if ( ! $prairie_ash_product ) {
		continue;
	}
	$prairie_ash_coffee  = prairie_ash_coffee( $prairie_ash_product );
	$prairie_ash_id      = $prairie_ash_product->get_id();
	$prairie_ash_facts   = (array) get_field( 'facts', $prairie_ash_id );
	$prairie_ash_gallery = $prairie_ash_product->get_gallery_image_ids();
	$prairie_ash_story   = (string) get_field( 'story_body', $prairie_ash_id );
	$prairie_ash_recipe  = (array) get_field( 'brewing', $prairie_ash_id );
	$prairie_ash_stock   = $prairie_ash_product->get_stock_quantity();
	$prairie_ash_shop_id = wc_get_page_id( 'shop' );
	$prairie_ash_options = $prairie_ash_product->is_type( 'variable' ) ? $prairie_ash_product->get_variation_attributes() : array();
	$prairie_ash_default = $prairie_ash_product->get_default_attributes();
	$prairie_ash_origin  = (string) get_field( 'page_origin', $prairie_ash_id );
	if ( '' === $prairie_ash_origin ) {
		$prairie_ash_origin = $prairie_ash_coffee['origin'];
	}
	// The design's summary replaces woocommerce_single_product_summary, where WooCommerce
	// collects the Product structured data it prints in the footer, so it is collected here
	// unless something took it off that hook.
	if ( isset( WC()->structured_data ) && has_action( 'woocommerce_single_product_summary', array( WC()->structured_data, 'generate_product_data' ) ) ) {
		WC()->structured_data->generate_product_data( $prairie_ash_product );
	}
	?>
		<main class="page" id="main" tabindex="-1" data-motion-scope data-motion-scrub>
			<?php do_action( 'woocommerce_before_single_product' ); ?>
			<nav class="breadcrumb" aria-label="<?php echo esc_attr( prairie_ash_wording( 'breadcrumb_label' ) ); ?>" data-motion="product-crumb">
				<div class="breadcrumb__container">
					<ol class="breadcrumb__list">
						<li class="breadcrumb__item"><a class="text-link text-link--on-paper" href="<?php echo esc_url( get_permalink( $prairie_ash_shop_id ) ); ?>"><?php echo esc_html( prairie_ash_wording( 'breadcrumb_shop' ) ); ?></a></li>
						<li class="breadcrumb__item"><span class="breadcrumb__current" aria-current="page"><?php echo esc_html( $prairie_ash_coffee['name'] ); ?></span></li>
					</ol>
				</div>
			</nav>

			<section class="product-summary" aria-labelledby="product-title">
				<div class="product-summary__container">
					<div class="product-summary__gallery" data-motion="product-shelf">
						<?php if ( $prairie_ash_coffee['image'] ) : ?>
							<div class="product-summary__media media-ibg">
								<?php
								// lcp-image: the bag photo paints the Largest Contentful Paint of a product page.
								prairie_ash_lcp_image( $prairie_ash_coffee['image'], prairie_ash_slot_sizes( 'product-summary__media' ), $prairie_ash_coffee['name'] );
								?>
							</div>
						<?php endif; ?>
						<?php if ( $prairie_ash_gallery ) : ?>
							<div class="product-summary__details-media">
								<?php foreach ( $prairie_ash_gallery as $prairie_ash_image ) : ?>
									<div class="product-summary__detail media-ibg">
										<?php prairie_ash_image( $prairie_ash_image, prairie_ash_slot_sizes( 'product-summary__detail' ), $prairie_ash_coffee['name'] ); ?>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="product-summary__details">
						<div class="product-summary__top" data-motion="product-sign">
							<?php if ( $prairie_ash_origin ) : ?>
								<p class="product-summary__origin"><?php echo esc_html( $prairie_ash_origin ); ?></p>
							<?php endif; ?>
							<h1 class="product-summary__title" id="product-title"><?php echo esc_html( $prairie_ash_coffee['name'] ); ?></h1>
							<?php if ( $prairie_ash_coffee['notes'] ) : ?>
								<p class="product-summary__notes"><?php echo esc_html( $prairie_ash_coffee['notes'] ); ?></p>
							<?php endif; ?>
							<p class="product-summary__price" data-motion="price"><?php echo esc_html( $prairie_ash_coffee['price'] ); ?></p>
						</div>
						<div class="product-summary__rest">
							<dl class="lv lv--fact lv--stack" data-motion="product-board">
								<?php foreach ( $prairie_ash_facts as $prairie_ash_fact ) : ?>
									<?php if ( ! empty( $prairie_ash_fact['label'] ) ) : ?>
										<div class="lv__row"><dt class="lv__label"><?php echo esc_html( $prairie_ash_fact['label'] ); ?></dt><dd class="lv__value"><?php echo esc_html( $prairie_ash_fact['value'] ); ?></dd></div>
									<?php endif; ?>
								<?php endforeach; ?>
								<div class="lv__row"><dt class="lv__label"><?php echo esc_html( prairie_ash_wording( 'stock_label' ) ); ?></dt><dd class="lv__value"<?php echo $prairie_ash_coffee['sold_out'] ? ' data-product-struck' : ''; ?>><?php echo esc_html( $prairie_ash_coffee['sold_out'] ? $prairie_ash_coffee['sold_label'] : prairie_ash_wording( 'in_stock' ) ); ?></dd></div>
							</dl>
							<form class="product-summary__form" data-motion="product-form" action="<?php echo esc_url( get_permalink() ); ?>" method="post">
								<?php foreach ( $prairie_ash_options as $prairie_ash_taxonomy => $prairie_ash_values ) : ?>
									<fieldset class="product-summary__option">
										<legend class="product-summary__legend"><?php echo esc_html( wc_attribute_label( $prairie_ash_taxonomy, $prairie_ash_product ) ); ?></legend>
										<div class="options">
											<?php
											// A global attribute posts its term slug and prints the term's name. A
											// product's own attribute has no terms: it posts and prints its option text.
											$prairie_ash_choices = array();
											if ( taxonomy_exists( $prairie_ash_taxonomy ) ) {
												foreach ( wc_get_product_terms( $prairie_ash_id, $prairie_ash_taxonomy, array( 'fields' => 'all' ) ) as $prairie_ash_term ) {
													if ( in_array( $prairie_ash_term->slug, $prairie_ash_values, true ) ) {
														$prairie_ash_choices[ $prairie_ash_term->slug ] = $prairie_ash_term->name;
													}
												}
											} else {
												foreach ( $prairie_ash_values as $prairie_ash_value ) {
													$prairie_ash_choices[ (string) $prairie_ash_value ] = (string) $prairie_ash_value;
												}
											}
											$prairie_ash_attr_key = sanitize_title( $prairie_ash_taxonomy );
											$prairie_ash_first    = true;
											foreach ( $prairie_ash_choices as $prairie_ash_value => $prairie_ash_text ) :
												$prairie_ash_value   = (string) $prairie_ash_value;
												$prairie_ash_checked = isset( $prairie_ash_default[ $prairie_ash_attr_key ] ) ? (string) $prairie_ash_default[ $prairie_ash_attr_key ] === $prairie_ash_value : $prairie_ash_first;
												$prairie_ash_first   = false;
												$prairie_ash_input   = sanitize_html_class( str_replace( 'pa_', '', $prairie_ash_attr_key ) . '-' . sanitize_title( $prairie_ash_value ) );
												?>
												<div class="options__item">
													<input id="<?php echo esc_attr( $prairie_ash_input ); ?>" class="options__input" type="radio" name="<?php echo esc_attr( 'attribute_' . $prairie_ash_attr_key ); ?>" value="<?php echo esc_attr( $prairie_ash_value ); ?>"<?php checked( $prairie_ash_checked ); ?>>
													<label for="<?php echo esc_attr( $prairie_ash_input ); ?>" class="options__label"><span class="options__text"><?php echo esc_html( prairie_ash_nbsp_units( $prairie_ash_text ) ); ?></span></label>
												</div>
											<?php endforeach; ?>
										</div>
									</fieldset>
								<?php endforeach; ?>
								<div class="product-summary__buy">
									<div class="field product-summary__quantity">
										<label class="field__label" for="product-quantity"><?php echo esc_html( prairie_ash_wording( 'quantity' ) ); ?></label>
										<input class="input input--code" id="product-quantity" type="number" name="quantity" value="1" min="1"<?php echo ( $prairie_ash_stock && ! $prairie_ash_coffee['sold_out'] ) ? ' max="' . absint( $prairie_ash_stock ) . '"' : ''; ?> inputmode="numeric"<?php disabled( $prairie_ash_coffee['sold_out'] ); ?>>
									</div>
									<?php if ( $prairie_ash_coffee['sold_out'] ) : ?>
										<button class="button button--primary" type="button" disabled><?php echo esc_html( $prairie_ash_coffee['sold_label'] ); ?></button>
									<?php else : ?>
										<button class="button button--primary" type="submit" name="add-to-cart" value="<?php echo esc_attr( $prairie_ash_id ); ?>"><?php echo esc_html( prairie_ash_wording( 'add_to_cart' ) ); ?></button>
									<?php endif; ?>
								</div>
							</form>
							<?php if ( prairie_ash_wording( 'product_delivery' ) ) : ?>
								<p class="product-summary__delivery"><?php echo esc_html( prairie_ash_wording( 'product_delivery' ) ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</section>

			<?php if ( $prairie_ash_story ) : ?>
				<section class="product-story band band--deep" aria-labelledby="product-story-title">
					<div class="product-story__container">
						<div class="product-story__text">
							<h2 class="product-story__title" id="product-story-title"><?php echo esc_html( prairie_ash_product_text( 'story_title', 'story_title' ) ); ?></h2>
							<?php foreach ( prairie_ash_paragraphs( $prairie_ash_story ) as $prairie_ash_paragraph ) : ?>
								<p class="product-story__body"><?php echo esc_html( $prairie_ash_paragraph ); ?></p>
							<?php endforeach; ?>
						</div>
						<?php if ( get_field( 'story_image', $prairie_ash_id ) ) : ?>
							<div class="product-story__media media-ibg" data-motion="product-origin">
								<?php prairie_ash_image( get_field( 'story_image', $prairie_ash_id ), prairie_ash_slot_sizes( 'product-story__media' ), prairie_ash_product_text( 'story_title', 'story_title' ) ); ?>
							</div>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $prairie_ash_recipe ) : ?>
				<section class="product-brewing band band--paper" aria-labelledby="product-brewing-title">
					<div class="product-brewing__container">
						<?php if ( get_field( 'brewing_image', $prairie_ash_id ) ) : ?>
							<div class="product-brewing__media media-ibg">
								<?php prairie_ash_image( get_field( 'brewing_image', $prairie_ash_id ), prairie_ash_slot_sizes( 'product-brewing__media' ), prairie_ash_product_text( 'brewing_title', 'brewing_title' ) ); ?>
							</div>
						<?php endif; ?>
						<div class="product-brewing__text">
							<h2 class="product-brewing__title" id="product-brewing-title"><?php echo esc_html( prairie_ash_product_text( 'brewing_title', 'brewing_title' ) ); ?></h2>
							<dl class="lv lv--fact lv--stack" data-motion="product-board">
								<?php foreach ( $prairie_ash_recipe as $prairie_ash_row ) : ?>
									<div class="lv__row"><dt class="lv__label"><?php echo esc_html( $prairie_ash_row['label'] ); ?></dt><dd class="lv__value"><?php echo esc_html( $prairie_ash_row['value'] ); ?></dd></div>
								<?php endforeach; ?>
							</dl>
						</div>
					</div>
				</section>
			<?php endif; ?>
			<?php do_action( 'woocommerce_after_single_product' ); ?>
		</main>
	<?php
endwhile;

get_footer();
