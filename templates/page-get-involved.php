<?php
/**
 * Template Name: Get Involved Overview
 *
 * Hub page introducing the four ways to get involved with the
 * community. Use a Page in WP and assign this template under
 * Page Attributes → Template.
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

	<main id="primary" class="site-main wcu-get-involved">

		<header class="wcu-get-involved__header">
			<div class="container container--narrow">
				<p class="section-eyebrow"><?php esc_html_e( 'Be part of it', 'wcuganda' ); ?></p>
				<h1 class="wcu-get-involved__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="wcu-get-involved__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php else : ?>
					<p class="wcu-get-involved__lead">
						<?php esc_html_e( 'Whether you want to organize, speak, sponsor, or just show up — there is a path. Pick the one that fits you below.', 'wcuganda' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</header>

		<div class="container">

			<?php if ( get_the_content() ) : ?>
				<div class="container container--narrow wcu-get-involved__intro">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<section class="wcu-get-involved__paths" aria-label="<?php esc_attr_e( 'Ways to get involved', 'wcuganda' ); ?>">
				<ul class="wcu-grid wcu-grid--2 wcu-get-involved__list">

					<li>
						<a class="wcu-card wcu-get-involved__card" href="<?php echo esc_url( home_url( '/volunteer/' ) ); ?>">
							<div class="wcu-card__body">
								<p class="wcu-card__eyebrow"><?php esc_html_e( 'Step up', 'wcuganda' ); ?></p>
								<h2 class="wcu-card__title"><?php esc_html_e( 'Volunteer', 'wcuganda' ); ?></h2>
								<p class="wcu-card__excerpt">
									<?php esc_html_e( 'Help organize meetups, coordinate WordCamps, run workshops, or back the team behind the scenes.', 'wcuganda' ); ?>
								</p>
								<p class="wcu-blog-card__meta">
									<?php esc_html_e( 'Apply to volunteer', 'wcuganda' ); ?>
									<?php wcu_svg_icon( 'arrow-right', array( 'width' => 12, 'height' => 12 ) ); ?>
								</p>
							</div>
						</a>
					</li>

					<li>
						<a class="wcu-card wcu-get-involved__card" href="<?php echo esc_url( home_url( '/speak/' ) ); ?>">
							<div class="wcu-card__body">
								<p class="wcu-card__eyebrow"><?php esc_html_e( 'On stage', 'wcuganda' ); ?></p>
								<h2 class="wcu-card__title"><?php esc_html_e( 'Speak at an event', 'wcuganda' ); ?></h2>
								<p class="wcu-card__excerpt">
									<?php esc_html_e( 'Pitch a talk for an upcoming meetup or WordCamp. First-time speakers welcome — we will help you prepare.', 'wcuganda' ); ?>
								</p>
								<p class="wcu-blog-card__meta">
									<?php esc_html_e( 'Submit a talk', 'wcuganda' ); ?>
									<?php wcu_svg_icon( 'arrow-right', array( 'width' => 12, 'height' => 12 ) ); ?>
								</p>
							</div>
						</a>
					</li>

					<li>
						<a class="wcu-card wcu-get-involved__card" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_sponsor' ) ?: home_url( '/sponsors/' ) ); ?>">
							<div class="wcu-card__body">
								<p class="wcu-card__eyebrow"><?php esc_html_e( 'Back the work', 'wcuganda' ); ?></p>
								<h2 class="wcu-card__title"><?php esc_html_e( 'Sponsor an event', 'wcuganda' ); ?></h2>
								<p class="wcu-card__excerpt">
									<?php esc_html_e( 'Reach the WordPress builders, contributors, and decision-makers in Uganda. Packages from a single meetup to a year-long partnership.', 'wcuganda' ); ?>
								</p>
								<p class="wcu-blog-card__meta">
									<?php esc_html_e( 'See sponsorship', 'wcuganda' ); ?>
									<?php wcu_svg_icon( 'arrow-right', array( 'width' => 12, 'height' => 12 ) ); ?>
								</p>
							</div>
						</a>
					</li>

					<li>
						<a class="wcu-card wcu-get-involved__card" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_chapter' ) ?: home_url( '/chapters/' ) ); ?>">
							<div class="wcu-card__body">
								<p class="wcu-card__eyebrow"><?php esc_html_e( 'Show up', 'wcuganda' ); ?></p>
								<h2 class="wcu-card__title"><?php esc_html_e( 'Join a local chapter', 'wcuganda' ); ?></h2>
								<p class="wcu-card__excerpt">
									<?php esc_html_e( 'Find the chapter closest to you on Meetup.com — meetups are free and open to everyone.', 'wcuganda' ); ?>
								</p>
								<p class="wcu-blog-card__meta">
									<?php esc_html_e( 'Browse chapters', 'wcuganda' ); ?>
									<?php wcu_svg_icon( 'arrow-right', array( 'width' => 12, 'height' => 12 ) ); ?>
								</p>
							</div>
						</a>
					</li>

				</ul>
			</section>

		</div>

	</main>

	<?php
endwhile;

get_footer();
