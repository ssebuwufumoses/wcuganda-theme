<?php
/**
 * Front page template — composes the WCUganda homepage sections.
 *
 * Used when "Your latest posts" is set as the homepage in Settings → Reading,
 * AND when a static page named "Front Page" is assigned without its own
 * template. Each section is a separate template-part for easy reordering.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main wcu-home">

	<?php
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/stats' );
	get_template_part( 'template-parts/home/events-preview' );
	get_template_part( 'template-parts/home/wordcamp-banner' );
	get_template_part( 'template-parts/home/sponsors' );
	get_template_part( 'template-parts/home/newsletter' );
	get_template_part( 'template-parts/home/map' );
	?>

</main>

<?php
get_footer();
