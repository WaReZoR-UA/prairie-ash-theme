<?php
/**
 * The fallback template: search results, archives and the posts page.
 *
 * Required in every classic theme. The design draws none of these pages, so
 * they are built from its global parts: a wall band with the page's sign,
 * then the entries on paper, each title an inline link. 404.php draws the
 * missing page.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

if ( is_search() ) {
	/* translators: %s: the words searched for. */
	$prairie_ash_heading = sprintf( __( 'Search results for "%s"', 'prairie-ash' ), get_search_query( false ) );
} elseif ( is_home() && ! is_front_page() && get_option( 'page_for_posts' ) ) {
	$prairie_ash_heading = get_the_title( (int) get_option( 'page_for_posts' ) );
} elseif ( is_archive() ) {
	$prairie_ash_heading = wp_strip_all_tags( get_the_archive_title() );
} else {
	$prairie_ash_heading = __( 'Latest entries', 'prairie-ash' );
}

get_header();
?>
		<main class="page" id="main" tabindex="-1">
			<section class="results-title band band--wall" aria-labelledby="results-title">
				<div class="results-title__container">
					<h1 class="results-title__heading" id="results-title"><?php echo esc_html( $prairie_ash_heading ); ?></h1>
				</div>
			</section>
			<section class="results-list band band--paper">
				<div class="results-list__container">
					<?php if ( have_posts() ) : ?>
						<ol class="results-list__items">
							<?php
							while ( have_posts() ) :
								the_post();
								$prairie_ash_type    = get_post_type_object( get_post_type() );
								$prairie_ash_excerpt = has_excerpt() || ! has_blocks() ? wp_strip_all_tags( get_the_excerpt() ) : '';
								?>
								<li <?php post_class( 'results-list__entry' ); ?>>
									<?php if ( $prairie_ash_type && is_search() ) : ?>
										<p class="results-list__entry-type"><?php echo esc_html( $prairie_ash_type->labels->singular_name ); ?></p>
									<?php endif; ?>
									<h2 class="results-list__entry-title">
										<a class="inline-link" href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( wp_strip_all_tags( get_the_title() ) ); ?></a>
									</h2>
									<?php if ( '' !== $prairie_ash_excerpt ) : ?>
										<p class="results-list__entry-excerpt"><?php echo esc_html( $prairie_ash_excerpt ); ?></p>
									<?php endif; ?>
								</li>
								<?php
							endwhile;
							?>
						</ol>
						<?php
						the_posts_pagination(
							array(
								'class'     => 'results-list__pages',
								'prev_text' => __( 'Previous', 'prairie-ash' ),
								'next_text' => __( 'Next', 'prairie-ash' ),
							)
						);
						?>
					<?php else : ?>
						<p class="results-list__empty"><?php echo esc_html( is_search() ? __( 'Nothing matches these words. Other words, or the menu above, may find it.', 'prairie-ash' ) : __( 'Nothing has been published here yet.', 'prairie-ash' ) ); ?></p>
						<div class="results-list__links">
							<a class="text-link text-link--on-paper" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the home page', 'prairie-ash' ); ?></a>
						</div>
					<?php endif; ?>
				</div>
			</section>
		</main>
<?php
get_footer();
