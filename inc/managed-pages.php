<?php
/**
 * Managed design pages: rendering and editor protection.
 *
 * The Stitch design pages are authored markup, version-controlled in the theme
 * under inc/content/. WordPress editors and page builders re-serialize and
 * sanitise whatever they save, which strips the design's classes and injects
 * <p> tags — that silently destroys the layout.
 *
 * These pages therefore render from the shipped fragment rather than from the
 * database, so no editor, plugin or page builder can change the live output.
 * The stored post_content is kept in sync for search and admin previews.
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute path to the fragment that backs a managed post.
 *
 * @param string $slug      Post slug.
 * @param string $post_type Post type.
 * @return string Empty when there is no fragment.
 */
function so_fragment_path( $slug, $post_type = 'page' ) {
	$dir  = 'post' === $post_type ? 'posts' : 'pages';
	$path = get_template_directory() . '/inc/content/' . $dir . '/' . sanitize_file_name( $slug ) . '.html';
	return file_exists( $path ) ? $path : '';
}

/**
 * True when the post carries a usable Elementor document.
 *
 * A page builder flagged as `builder` but holding no document renders a blank
 * canvas, which is what breaks the layout — so an empty document never counts.
 *
 * @param int $post_id Post id.
 * @return bool
 */
function so_has_elementor_document( $post_id ) {
	if ( 'builder' !== get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
		return false;
	}
	$data = json_decode( (string) get_post_meta( $post_id, '_elementor_data', true ), true );
	return is_array( $data ) && count( $data ) > 0;
}

/**
 * Render managed content.
 *
 * If the page is a real Elementor document, Elementor renders it and this filter
 * stays out of the way. Otherwise the page renders from the version-controlled
 * fragment, so no editor, plugin or builder can ever blank it.
 *
 * Runs late so it also overrides a page builder's own the_content filter.
 *
 * @param string $content Post content.
 * @return string
 */
function so_render_managed_fragment( $content ) {
	$post = get_post();
	if ( ! $post || ! get_post_meta( $post->ID, '_so_managed', true ) ) {
		return $content;
	}
	if ( so_has_elementor_document( $post->ID ) ) {
		return $content;
	}
	$path = so_fragment_path( $post->post_name, $post->post_type );
	if ( ! $path ) {
		return $content;
	}
	$html = (string) file_get_contents( $path );
	// This filter is last, so anything the earlier content filters placed in the
	// fragment (the form embeds) must be resolved here too.
	return so_render_form_markers( $html );
}
add_filter( 'the_content', 'so_render_managed_fragment', 99 );

/**
 * Never leave a managed page flagged as a builder page without a document.
 *
 * Deleting every element in Elementor is a legitimate action, but it would leave
 * a blank canvas on a page whose design is fixed — so the flag is dropped and the
 * theme fragment renders again.
 *
 * @param int     $post_id Post id.
 * @param WP_Post $post    Post object.
 */
function so_guard_managed_elementor( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || ! $post instanceof WP_Post ) {
		return;
	}
	if ( ! get_post_meta( $post_id, '_so_managed', true ) ) {
		return;
	}
	// Only ever act on a page already flagged as a builder page. A builder writes
	// `_elementor_data` and `_elementor_edit_mode` in separate steps, so deleting
	// data whenever the flag looks absent destroys documents mid-write.
	if ( 'builder' !== get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
		return;
	}
	if ( so_has_elementor_document( $post_id ) ) {
		return;
	}
	// Flagged as a builder page but holding no document: drop the flag only, so the
	// page falls back to the theme fragment instead of rendering a blank canvas.
	// The data itself is left alone.
	delete_post_meta( $post_id, '_elementor_edit_mode' );
	delete_post_meta( $post_id, '_elementor_template_type' );
}
add_action( 'save_post', 'so_guard_managed_elementor', 10, 2 );

/**
 * Keep the block editor away from managed pages.
 *
 * The block editor re-serialises content and applies kses/autop, which is what
 * mangles the design markup.
 *
 * @param bool    $use       Whether to use the block editor.
 * @param WP_Post $post_type Post object.
 * @return bool
 */
function so_no_block_editor_for_managed( $use, $post = null ) {
	if ( $post instanceof WP_Post && get_post_meta( $post->ID, '_so_managed', true ) ) {
		return false;
	}
	return $use;
}
add_filter( 'use_block_editor_for_post', 'so_no_block_editor_for_managed', 10, 2 );

/**
 * Tell the administrator, on the edit screen, where the copy actually lives.
 */
function so_managed_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->base, array( 'post' ), true ) ) {
		return;
	}
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! get_post_meta( $post_id, '_so_managed', true ) ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p><strong>%s</strong> %s</p><p>%s</p></div>',
		esc_html__( 'This page is built in Elementor.', 'skills-online' ),
		esc_html__( 'Each design section is one Elementor container holding a single HTML widget. Use “Edit with Elementor” to open it: you can edit a section’s markup inside its HTML widget, reorder or hide sections, and add your own sections and widgets around them.', 'skills-online' ),
		esc_html__( 'The design’s styling comes from the theme, so keep the existing class names inside a widget if you edit its markup. Deleting every section reverts the page to the original design rather than publishing a blank page.', 'skills-online' )
	);
}
add_action( 'admin_notices', 'so_managed_admin_notice' );
