<?php
/**
 * Blog post card. Renders inside the loop.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_post_id    = get_the_ID();
$wcu_categories = get_the_category( $wcu_post_id );
$wcu_primary    = ! empty( $wcu_categories ) ? $wcu_categories[0] : null;
$wcu_author_id  = (int) get_post_field( 'post_author', $wcu_post_id );
?>

<article <?php post_class( 'wcu-card wcu-blog-card' ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<a class="wcu-card__media" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( 'wcu-card', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="wcu-card__body wcu-blog-card__body">

		<?php if ( $wcu_primary ) : ?>
			<p class="wcu-card__eyebrow">
				<a href="<?php echo esc_url( get_category_link( $wcu_primary->term_id ) ); ?>">
					<?php echo esc_html( $wcu_primary->name ); ?>
				</a>
			</p>
		<?php endif; ?>

		<h3 class="wcu-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( has_excerpt() || get_the_content() ) : ?>
			<p class="wcu-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<?php endif; ?>

		<p class="wcu-blog-card__meta">
			<?php
			printf(
				/* translators: 1: author name, 2: published date. */
				esc_html__( 'By %1$s · %2$s', 'wcuganda' ),
				esc_html( get_the_author_meta( 'display_name', $wcu_author_id ) ),
				esc_html( get_the_date( 'j M Y', $wcu_post_id ) )
			);
			?>
		</p>

	</div>

</article>
