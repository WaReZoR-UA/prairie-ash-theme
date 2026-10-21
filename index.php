<?php
/**
 * The fallback template.
 *
 * Required in every classic theme. WordPress falls back to this file whenever
 * the template hierarchy finds nothing more specific, so it must render
 * something sensible rather than being a stub.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="page">
	<div class="page__container">

		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'page__entry' ); ?>>
					<h2 class="page__entry-title">
						<a href="<?php echo esc_url( get_permalink() ); ?>">
							<?php the_title(); ?>
						</a>
					</h2>
					<div class="page__entry-excerpt">
						<?php the_excerpt(); ?>
					</div>
				</article>
				<?php
			endwhile;

			the_posts_pagination();
			?>
		<?php else : ?>
			<p class="page__empty"><?php esc_html_e( 'Nothing has been published here yet.', 'prairie-ash' ); ?></p>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
