<?php
/**
 * Skills Online theme bootstrap.
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SO_VERSION', '1.0.0' );
define( 'SO_CONTENT_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/blog.php';
require_once get_template_directory() . '/inc/shortcodes.php';
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/forms.php';
require_once get_template_directory() . '/inc/managed-pages.php';

/**
 * Render the form markers that live inside stored page content.
 *
 * Nonces and admin-post actions cannot be stored, so the fragment keeps a
 * marker and the form is rendered per request.
 *
 * @param string $content Post content.
 * @return string
 */
function so_render_form_markers( $content ) {
	return so_render_markers( $content );
}
add_filter( 'the_content', 'so_render_form_markers', 20 );

/**
 * True while Elementor is rendering its editor or preview.
 *
 * The design pages are Elementor documents now, so in the editor we let the
 * builder load its own assets (that is what the editor expects); on the public
 * site its framework is dropped because the design needs none of it.
 *
 * @return bool
 */
function so_is_elementor_editor_request() {
	if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}
	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		return true;
	}
	return false;
}

/**
 * Trim WordPress/plugin front-end CSS that this theme does not use.
 *
 * The design pages are raw design markup (no core blocks), so the block library,
 * classic theme styles and the merged global stylesheet are dead weight: measured
 * at 989 + 94 + 76 rules before this filter.
 */
function so_trim_frontend_css() {
	if ( is_admin() ) {
		return;
	}
	foreach ( array(
		'wp-block-library',
		'wp-block-library-theme',
		'classic-theme-styles',
		'global-styles',
		'wp-img-auto-sizes-contain',
		'core-block-supports',
	) as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}

	// Third-party plugin sheets that ship a global stylesheet on every page.
	global $wp_styles;
	if ( ! $wp_styles instanceof WP_Styles ) {
		return;
	}
	$keep_builder = so_is_elementor_editor_request();
	foreach ( (array) $wp_styles->registered as $handle => $style ) {
		$src = isset( $style->src ) ? (string) $style->src : '';
		if ( '' === $src ) {
			continue;
		}
		if ( $keep_builder && preg_match( '#(elementor-frontend|elementor-icons)#', $src ) ) {
			continue;
		}
		if ( preg_match( '#(hostinger-reach|/blocks/subscription|elementor-frontend|elementor-icons)#', $src ) ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'so_trim_frontend_css', 100 );

/**
 * Drop Elementor's front-end framework from the public site.
 *
 * Every design page is an Elementor document built from HTML widgets, so none of
 * Elementor's own CSS or JS is needed to render them — and all of it would fight
 * the design's compiled CSS. It is kept for the editor and preview, where the
 * builder expects its own assets.
 */
function so_dequeue_elementor_on_theme_pages() {
	if ( is_admin() && ! isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( so_is_elementor_editor_request() ) {
		return;
	}
	global $wp_styles, $wp_scripts;
	foreach ( array( $wp_styles, $wp_scripts ) as $collection ) {
		if ( ! $collection || empty( $collection->queue ) ) {
			continue;
		}
		foreach ( (array) $collection->queue as $handle ) {
			if ( 0 === strpos( (string) $handle, 'elementor' ) ) {
				wp_dequeue_style( $handle );
				wp_dequeue_script( $handle );
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'so_dequeue_elementor_on_theme_pages', 99 );

/**
 * Second, late pass over the builder's assets — on the public site only.
 *
 * Elementor registers some of its stylesheets (its generated base CSS, and the
 * Google Fonts the default kit asks for) after wp_enqueue_scripts has run, so a
 * prefix dequeue there misses them. Nothing here is used by the design, and the
 * fonts are self-hosted, so dropping them also removes several external requests.
 */
function so_strip_builder_assets_late() {
	if ( is_admin() && ! isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( so_is_elementor_editor_request() ) {
		return;
	}
	global $wp_styles;
	if ( ! $wp_styles instanceof WP_Styles || empty( $wp_styles->queue ) ) {
		return;
	}
	foreach ( (array) $wp_styles->queue as $handle ) {
		$handle = (string) $handle;
		$src    = isset( $wp_styles->registered[ $handle ]->src ) ? (string) $wp_styles->registered[ $handle ]->src : '';
		$drop   = 0 === strpos( $handle, 'elementor' )
			|| in_array( $handle, array( 'base-desktop', 'base-mobile', 'base-desktop-css', 'base-mobile-css' ), true )
			|| in_array( $handle, array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'wp-img-auto-sizes-contain', 'core-block-supports' ), true )
			|| false !== strpos( $src, 'fonts.googleapis.com' )
			|| preg_match( '#(hostinger-reach|/blocks/subscription)#', $src );
		if ( $drop ) {
			wp_dequeue_style( $handle );
		}
	}
}
add_action( 'wp_print_styles', 'so_strip_builder_assets_late', 1 );

/**
 * Final backstop: drop those stylesheets at the point the <link> is printed.
 *
 * Some plugins enqueue their block stylesheet during wp_head, after every
 * dequeue hook has run, so the queue is no longer the place to intercept them.
 *
 * @param string $tag    Link tag markup.
 * @param string $handle Style handle.
 * @param string $href   Style URL.
 * @return string
 */
function so_strip_stylesheet_tag( $tag, $handle, $href = '' ) {
	if ( is_admin() || so_is_elementor_editor_request() ) {
		return $tag;
	}
	$handle = (string) $handle;
	$href   = (string) $href;
	$drop   = 0 === strpos( $handle, 'elementor' )
		|| in_array( $handle, array( 'base-desktop', 'base-mobile', 'base-desktop-css', 'base-mobile-css', 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'wp-img-auto-sizes-contain', 'core-block-supports' ), true )
		|| preg_match( '#(fonts\.googleapis\.com|hostinger-reach|/blocks/subscription|elementor-frontend|elementor-icons|base-(desktop|mobile)\.css)#', $href );
	return $drop ? '' : $tag;
}
add_filter( 'style_loader_tag', 'so_strip_stylesheet_tag', 10, 3 );

/**
 * Drop the resource hint for the plugin CDN we no longer load anything from.
 *
 * @param array  $hints Hints keyed by relation type.
 * @param string $relation_type Relation type.
 * @return array
 */
function so_trim_resource_hints( $hints, $relation_type ) {
	if ( is_admin() || so_is_elementor_editor_request() ) {
		return $hints;
	}
	foreach ( (array) $hints as $key => $hint ) {
		$url = is_array( $hint ) && isset( $hint['href'] ) ? $hint['href'] : $hint;
		if ( is_string( $url ) && false !== strpos( $url, 'cdn-reach.hostinger.com' ) ) {
			unset( $hints[ $key ] );
		}
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'so_trim_resource_hints', 10, 2 );

/**
 * Theme supports.
 */
function so_setup() {
	load_theme_textdomain( 'skills-online', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array( 'height' => 40, 'width' => 40, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'skills-online' ),
			'footer'  => __( 'Footer Navigation', 'skills-online' ),
		)
	);
}
add_action( 'after_setup_theme', 'so_setup' );

/**
 * Content width.
 */
function so_content_width() {
	$GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'so_content_width', 0 );

/**
 * Front-end assets.
 */
function so_assets() {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	wp_enqueue_style( 'so-fonts', $uri . '/assets/css/fonts.css', array(), so_asset_version( '/assets/css/fonts.css' ) );
	wp_enqueue_style( 'so-theme', $uri . '/assets/css/theme.css', array( 'so-fonts' ), so_asset_version( '/assets/css/theme.css' ) );
	wp_enqueue_style( 'so-design', $uri . '/assets/css/design.css', array( 'so-theme' ), so_asset_version( '/assets/css/design.css' ) );
	// Generated: re-asserts the design's layout utilities that Elementor's own
	// sheets would otherwise outrank (see build/layout_overrides.py).
	wp_enqueue_style( 'so-layout', $uri . '/assets/css/layout.css', array( 'so-design' ), so_asset_version( '/assets/css/layout.css' ) );
	wp_enqueue_style( 'so-style', get_stylesheet_uri(), array( 'so-layout' ), so_asset_version( '/style.css' ) );

	wp_enqueue_script( 'so-theme', $uri . '/assets/js/theme.js', array(), so_asset_version( '/assets/js/theme.js' ), true );
	wp_script_add_data( 'so-theme', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'so_assets' );

/**
 * Cache-busting version from file mtime.
 *
 * @param string $rel Relative path inside the theme.
 * @return string
 */
function so_asset_version( $rel ) {
	$path = get_template_directory() . $rel;
	return file_exists( $path ) ? (string) filemtime( $path ) : SO_VERSION;
}

/**
 * Body classes so the design's base surface, font and selection colours apply.
 *
 * @param array $classes Body classes.
 * @return array
 */
function so_body_class( $classes ) {
	// Replays the Stitch shell exactly: the design's own body utilities decide the
	// base surface colour, body face and font smoothing for every page.
	$classes = array_merge(
		$classes,
		array( 'so-site', 'bg-surface', 'font-body-md', 'text-on-surface', 'antialiased' )
	);
	if ( is_front_page() ) {
		$classes[] = 'so-home';
	}
	return $classes;
}
add_filter( 'body_class', 'so_body_class' );

/**
 * Meta viewport + charset are core; keep the design's shell attributes.
 */
function so_language_attributes( $output ) {
	return $output . ' class="light"';
}
add_filter( 'language_attributes', 'so_language_attributes' );

/**
 * Never let wpautop reformat the managed design markup.
 */
function so_remove_autop() {
	if ( is_singular() ) {
		$post = get_post();
		if ( $post && get_post_meta( $post->ID, '_so_managed', true ) ) {
			remove_filter( 'the_content', 'wpautop', 10 );
			remove_filter( 'the_content', 'wptexturize', 10 );
			remove_filter( 'the_content', 'convert_smilies', 20 );
		}
	}
}
add_action( 'wp', 'so_remove_autop' );

/**
 * Excerpt length for insight cards.
 *
 * @return int
 */
function so_excerpt_length() {
	return 26;
}
add_filter( 'excerpt_length', 'so_excerpt_length' );

/**
 * Excerpt "read more" suffix.
 *
 * @return string
 */
function so_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'so_excerpt_more' );

/**
 * Register the insight post type category archive nicety: 12 posts per page.
 *
 * @param WP_Query $query Query.
 */
function so_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_search() ) {
		$query->set( 'posts_per_page', 9 );
	}
}
add_action( 'pre_get_posts', 'so_pre_get_posts' );

/**
 * Drop the emoji/shortlink head cruft — small performance win.
 */
function so_clean_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
}
add_action( 'init', 'so_clean_head' );
