<?php
/**
 * Template Name: Volunteer Form
 *
 * Volunteer application page. Renders the page title + content from
 * the WP editor on top, then the volunteer application form below.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<main id="primary" class="site-main wcu-app-page">

		<header class="wcu-app-page__header">
			<div class="container container--narrow">
				<a class="wcu-event-hero__back" href="<?php echo esc_url( home_url( '/get-involved/' ) ); ?>">
					<?php wcu_svg_icon( 'arrow-left', array( 'width' => 18, 'height' => 18 ) ); ?>
					<?php esc_html_e( 'Get involved', 'wcuganda' ); ?>
				</a>

				<p class="section-eyebrow"><?php esc_html_e( 'Step up', 'wcuganda' ); ?></p>
				<h1 class="wcu-app-page__title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="wcu-app-page__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php else : ?>
					<p class="wcu-app-page__lead">
						<?php esc_html_e( 'Fill in the form below — an organizer will reach out to talk through where you fit and what is coming up.', 'wcuganda' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</header>

		<div class="container container--narrow wcu-app-page__body">

			<?php if ( get_the_content() ) : ?>
				<div class="wcu-app-page__intro">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/forms/application-form', null, array( 'type' => 'volunteer' ) ); ?>

		</div>

	</main>

	<?php
endwhile;

get_footer();
