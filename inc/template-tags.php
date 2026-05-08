<?php
/**
 * Custom template tags called from theme template files.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wcu_posted_on' ) ) :
	/**
	 * Print the post date for the current post inside the loop.
	 *
	 * @return void
	 */
	function wcu_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="entry-date updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);

		printf(
			'<span class="posted-on">%1$s <a href="%2$s" rel="bookmark">%3$s</a></span>',
			esc_html_x( 'Posted on', 'post date', 'wcuganda' ),
			esc_url( get_permalink() ),
			$time_string // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped components above.
		);
	}
endif;

if ( ! function_exists( 'wcu_posted_by' ) ) :
	/**
	 * Print the post author for the current post inside the loop.
	 *
	 * @return void
	 */
	function wcu_posted_by() {
		printf(
			'<span class="byline">%1$s <span class="author vcard"><a class="url fn n" href="%2$s">%3$s</a></span></span>',
			esc_html_x( 'by', 'post author', 'wcuganda' ),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		);
	}
endif;

if ( ! function_exists( 'wcu_entry_footer' ) ) :
	/**
	 * Print categories, tags, and a comments link for the current post.
	 *
	 * @return void
	 */
	function wcu_entry_footer() {
		if ( 'post' === get_post_type() ) {
			$categories_list = get_the_category_list( esc_html__( ', ', 'wcuganda' ) );
			if ( $categories_list ) {
				printf(
					/* translators: 1: list of categories. */
					'<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'wcuganda' ) . '</span>',
					$categories_list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output of get_the_category_list() is safe.
				);
			}

			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'wcuganda' ) );
			if ( $tags_list ) {
				printf(
					/* translators: 1: list of tags. */
					'<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'wcuganda' ) . '</span>',
					$tags_list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output of get_the_tag_list() is safe.
				);
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title. */
						__( 'Leave a comment<span class="screen-reader-text"> on %s</span>', 'wcuganda' ),
						array( 'span' => array( 'class' => array() ) )
					),
					wp_kses_post( get_the_title() )
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: post title. */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'wcuganda' ),
					array( 'span' => array( 'class' => array() ) )
				),
				wp_kses_post( get_the_title() )
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
endif;

if ( ! function_exists( 'wcu_event_to_card_array' ) ) :
	/**
	 * Normalize a wcu_event post into the shape expected by template-parts/events/card.php.
	 *
	 * @param int|WP_Post $post Post ID or object.
	 * @return array<string,mixed>
	 */
	function wcu_event_to_card_array( $post ) {
		$post_id = is_object( $post ) ? $post->ID : (int) $post;

		return array(
			'title'    => get_the_title( $post_id ),
			'url'      => get_permalink( $post_id ),
			'date'     => get_post_meta( $post_id, '_wcu_event_date', true ),
			'venue'    => get_post_meta( $post_id, '_wcu_event_venue', true ),
			'image'    => get_the_post_thumbnail_url( $post_id, 'wcu-card' ),
			'is_local' => true,
		);
	}
endif;

if ( ! function_exists( 'wcu_wporg_event_to_card_array' ) ) :
	/**
	 * Normalize a WP.org Events API payload into the card shape.
	 *
	 * @param array $event WP.org event array (from WCU_WPOrg_Events::get_events).
	 * @return array<string,mixed>
	 */
	function wcu_wporg_event_to_card_array( $event ) {
		return array(
			'title'    => isset( $event['title'] ) ? $event['title'] : '',
			'url'      => isset( $event['url'] ) ? $event['url'] : '',
			'date'     => isset( $event['date'] ) ? $event['date'] : '',
			'venue'    => isset( $event['location'] ) ? $event['location'] : '',
			'image'    => '',
			'is_local' => false,
			'meetup'   => isset( $event['meetup'] ) ? $event['meetup'] : '',
		);
	}
endif;

if ( ! function_exists( 'wcu_post_thumbnail' ) ) :
	/**
	 * Output the featured image for the current post in a responsive wrapper.
	 *
	 * @return void
	 */
	function wcu_post_thumbnail() {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>
			<figure class="post-thumbnail">
				<?php the_post_thumbnail( 'wcu-hero', array( 'loading' => 'eager' ) ); ?>
			</figure>
			<?php
		else :
			?>
			<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php
				the_post_thumbnail(
					'wcu-card',
					array(
						'alt' => the_title_attribute(
							array(
								'echo' => false,
							)
						),
					)
				);
				?>
			</a>
			<?php
		endif;
	}
endif;
