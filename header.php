<?php
/**
 * The site header: skip link, wordmark, cart, directions, main navigation and
 * the page list opened from "All pages" on a phone.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$prairie_ash_items      = prairie_ash_menu_items( 'primary' );
$prairie_ash_directions = prairie_ash_directions_url();
$prairie_ash_pages_cur  = false;
foreach ( $prairie_ash_items as $prairie_ash_item ) {
	if ( $prairie_ash_item['current'] && in_array( 'header__item--wide', $prairie_ash_item['classes'], true ) ) {
		$prairie_ash_pages_cur = true;
	}
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="format-detection" content="telephone=no">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php // Resolves window.paReveal when the cross-document view transition has finished: src/js/files/motion-loader.js waits for it. ?>
	<script>(function(){var w=window;w.paReveal=new Promise(function(r){if(!("onpagereveal" in w))return r();addEventListener("pagereveal",function(e){var t=e.viewTransition;t?t.finished.then(r,r):r()},{once:true})})})();</script>
	<?php // The first frame waits until the footer is parsed, so the view transition captures the whole new page. ?>
	<link rel="expect" href="#site-footer" blocking="render">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
	<div class="wrapper">
		<div class="skip">
			<div class="skip__container">
				<a class="text-link text-link--on-wall skip__link" href="#main"><?php echo esc_html( prairie_ash_setting( 'skip_link_label' ) ); ?></a>
			</div>
		</div>
		<header class="header" data-header>
			<div class="header__container">
				<div class="header__top">
					<a class="header__wordmark" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( prairie_ash_setting( 'wordmark_label' ) ); ?>">
						<span class="header__wordmark-name"><?php echo esc_html( prairie_ash_setting( 'wordmark_name' ) ); ?></span>
						<span class="header__wordmark-line"><?php echo esc_html( prairie_ash_setting( 'wordmark_line' ) ); ?></span>
					</a>
					<div class="header__actions">
						<?php prairie_ash_cart_link(); ?>
						<?php prairie_ash_link( prairie_ash_setting( 'directions_label' ), $prairie_ash_directions, 'button button--outline header__directions' ); ?>
					</div>
				</div>
				<?php if ( $prairie_ash_items ) : ?>
					<nav class="header__nav" aria-label="<?php echo esc_attr( prairie_ash_setting( 'nav_label' ) ); ?>">
						<ul class="header__list">
							<?php foreach ( $prairie_ash_items as $prairie_ash_item ) : ?>
								<li class="<?php echo esc_attr( implode( ' ', array_merge( array( 'header__item' ), $prairie_ash_item['classes'] ) ) ); ?>"><a class="nav-link" href="<?php echo esc_url( $prairie_ash_item['url'] ); ?>"<?php echo $prairie_ash_item['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $prairie_ash_item['label'] ); ?></a></li>
							<?php endforeach; ?>
							<li class="header__item header__item--pages">
								<button class="nav-link<?php echo $prairie_ash_pages_cur ? ' nav-link--current' : ''; ?>" type="button" aria-expanded="false" aria-controls="page-list" data-pages-toggle><?php echo esc_html( prairie_ash_setting( 'pages_toggle_label' ) ); ?></button>
							</li>
						</ul>
					</nav>
				<?php endif; ?>
			</div>
			<div class="page-list" id="page-list" data-pages-panel hidden>
				<div class="page-list__inner">
					<div class="page-list__container">
						<ul class="page-list__list">
							<?php foreach ( $prairie_ash_items as $prairie_ash_item ) : ?>
								<li><a class="nav-link" href="<?php echo esc_url( $prairie_ash_item['url'] ); ?>"<?php echo $prairie_ash_item['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $prairie_ash_item['label'] ); ?></a></li>
							<?php endforeach; ?>
							<li><?php prairie_ash_cart_link(); ?></li>
						</ul>
						<span class="pinstripe" aria-hidden="true" data-motion="stripe"></span>
						<?php prairie_ash_link( prairie_ash_setting( 'directions_label' ), $prairie_ash_directions, 'button button--primary' ); ?>
					</div>
				</div>
			</div>
		</header>
