<?php
/**
 * Members archive filter chips: chapter + role + skill.
 *
 * Reads the corresponding query vars from the URL to highlight the active
 * chip. Plain anchor links so the URLs are deep-linkable + crawlable.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_archive_url = get_post_type_archive_link( 'wcu_member' );
if ( empty( $wcu_archive_url ) ) {
	return;
}

$wcu_active_chapter = isset( $_GET['chapter'] ) ? sanitize_title( wp_unslash( $_GET['chapter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$wcu_active_role    = isset( $_GET['role'] ) ? sanitize_title( wp_unslash( $_GET['role'] ) ) : '';      // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$wcu_active_skill   = isset( $_GET['skill'] ) ? sanitize_title( wp_unslash( $_GET['skill'] ) ) : '';    // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$wcu_chapters = get_terms( array( 'taxonomy' => 'wcu_chapter_tax', 'hide_empty' => false ) );
$wcu_roles    = get_terms( array( 'taxonomy' => 'wcu_member_role', 'hide_empty' => false ) );
$wcu_skills   = get_terms( array( 'taxonomy' => 'wcu_skills', 'hide_empty' => false ) );

/**
 * Build a URL while preserving the *other* active filters.
 *
 * @param string $param Query var key being set.
 * @param string $slug  Term slug, or empty for "all".
 * @return string
 */
$wcu_build_url = function ( $param, $slug ) use ( $wcu_archive_url, $wcu_active_chapter, $wcu_active_role, $wcu_active_skill ) {
	$args = array(
		'chapter' => $wcu_active_chapter,
		'role'    => $wcu_active_role,
		'skill'   => $wcu_active_skill,
	);
	$args[ $param ] = $slug;
	$args = array_filter(
		$args,
		static function ( $value ) {
			return '' !== $value;
		}
	);
	return empty( $args ) ? $wcu_archive_url : add_query_arg( $args, $wcu_archive_url );
};

/**
 * Render one filter group.
 *
 * @param string $label  Visible label.
 * @param string $param  Query var key.
 * @param array  $terms  Terms.
 * @param string $active Active slug.
 * @return void
 */
$wcu_render_group = function ( $label, $param, $terms, $active ) use ( $wcu_build_url ) {
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;
	}
	?>
	<div class="wcu-event-filters__group">
		<span class="wcu-event-filters__label"><?php echo esc_html( $label ); ?></span>
		<a class="wcu-event-filters__chip<?php echo '' === $active ? ' is-active' : ''; ?>"
			href="<?php echo esc_url( $wcu_build_url( $param, '' ) ); ?>">
			<?php esc_html_e( 'All', 'wcuganda' ); ?>
		</a>
		<?php foreach ( $terms as $wcu_term ) : ?>
			<a class="wcu-event-filters__chip<?php echo $active === $wcu_term->slug ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( $wcu_build_url( $param, $wcu_term->slug ) ); ?>">
				<?php echo esc_html( $wcu_term->name ); ?>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
};
?>

<div class="wcu-event-filters" role="group" aria-label="<?php esc_attr_e( 'Filter members', 'wcuganda' ); ?>">
	<?php $wcu_render_group( __( 'Chapter:', 'wcuganda' ), 'chapter', $wcu_chapters, $wcu_active_chapter ); ?>
	<?php $wcu_render_group( __( 'Role:', 'wcuganda' ), 'role', $wcu_roles, $wcu_active_role ); ?>
	<?php $wcu_render_group( __( 'Skill:', 'wcuganda' ), 'skill', $wcu_skills, $wcu_active_skill ); ?>
</div>
