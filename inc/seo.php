<?php
/**
 * Technical / on-page SEO: description, canonical, Open Graph, structured data.
 *
 * Intentionally dependency-free (no SEO plugin) so the head stays minimal.
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the description for the current view.
 *
 * @return string
 */
function so_meta_description() {
	if ( is_singular() ) {
		$post = get_post();
		if ( $post ) {
			$meta = get_post_meta( $post->ID, '_so_meta_description', true );
			if ( $meta ) {
				return $meta;
			}
			$text = wp_strip_all_tags( strip_shortcodes( $post->post_excerpt ? $post->post_excerpt : $post->post_content ) );
			$text = trim( preg_replace( '/\s+/', ' ', $text ) );
			if ( $text ) {
				return wp_trim_words( $text, 30, '' );
			}
		}
	}
	if ( is_home() ) {
		return __( 'Engineering career insights, architecture deep-dives and hiring analysis written and reviewed by practising staff engineers.', 'skills-online' );
	}
	if ( is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( $term && ! empty( $term->description ) ) {
			return wp_strip_all_tags( $term->description );
		}
		$name  = $term ? $term->name : __( 'Insights', 'skills-online' );
		$count = $term ? (int) $term->count : 0;
		/* translators: 1: number of articles, 2: topic name. */
		return sprintf(
			_n(
				'%1$d Skills Online article filed under %2$s: production engineering notes, architecture trade-offs and career guidance.',
				'%1$d Skills Online articles filed under %2$s: production engineering notes, architecture trade-offs and career guidance.',
				max( 1, $count ),
				'skills-online'
			),
			$count,
			$name
		);
	}
	if ( is_search() ) {
		/* translators: %s: search query. */
		return sprintf( __( 'Search results for “%s” across Skills Online insights and programmes.', 'skills-online' ), get_search_query() );
	}
	return get_bloginfo( 'description', 'display' );
}

/**
 * Canonical URL for the current view.
 *
 * @return string
 */
function so_canonical() {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_home() || is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_category() || is_tag() ) {
		return get_term_link( get_queried_object() );
	}
	return home_url( add_query_arg( array(), so_current_url() ) );
}

/**
 * Whether the current view should be indexed.
 *
 * @return bool
 */
function so_is_indexable() {
	if ( is_search() || is_404() || is_paged() ) {
		return false;
	}
	// Tag archives are thin at this size; keep the links but keep them out of the index.
	if ( is_tag() ) {
		return false;
	}
	return (bool) get_option( 'blog_public' );
}

/**
 * Emit head meta.
 */
function so_head_meta() {
	$desc      = so_meta_description();
	$canonical = so_canonical();
	$index     = so_is_indexable();
	$title     = wp_get_document_title();
	$image     = get_template_directory_uri() . '/assets/img/logo-mark.png';

	if ( $desc ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $desc ) );
	}
	printf( '<meta name="robots" content="%s" />' . "\n", $index ? 'index,follow,max-image-preview:large' : 'noindex,follow' );
	printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canonical ) );

	printf( '<meta property="og:type" content="%s" />' . "\n", is_singular( 'post' ) ? 'article' : 'website' );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $canonical ) );
	printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
	printf( '<meta name="twitter:card" content="summary_large_image" />' . "\n" );
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $desc ) );
	printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image ) );
}
add_action( 'wp_head', 'so_head_meta', 2 );

/**
 * Structured data graph.
 */
function so_json_ld() {
	$graph = array();

	$graph[] = array(
		'@type'       => 'EducationalOrganization',
		'@id'         => home_url( '/#organization' ),
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'description' => get_bloginfo( 'description', 'display' ),
		'logo'        => array(
			'@type' => 'ImageObject',
			'url'   => get_template_directory_uri() . '/assets/img/logo-mark.png',
		),
		'areaServed'  => 'Worldwide',
		'knowsAbout'  => array( 'Software engineering', 'Cloud and DevOps', 'Applied AI and machine learning', 'Engineering leadership', 'System design' ),
	);

	$graph[] = array(
		'@type'       => 'WebSite',
		'@id'         => home_url( '/#website' ),
		'url'         => home_url( '/' ),
		'name'        => get_bloginfo( 'name' ),
		'publisher'   => array( '@id' => home_url( '/#organization' ) ),
		'inLanguage'  => get_bloginfo( 'language' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	if ( is_singular( 'post' ) ) {
		$post      = get_post();
		$graph[]   = array(
			'@type'            => 'BlogPosting',
			'@id'              => get_permalink() . '#article',
			'headline'         => get_the_title(),
			'description'      => so_meta_description(),
			'datePublished'    => get_the_date( DATE_W3C ),
			'dateModified'     => get_the_modified_date( DATE_W3C ),
			'mainEntityOfPage' => get_permalink(),
			'inLanguage'       => get_bloginfo( 'language' ),
			'author'           => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
			'publisher'        => array( '@id' => home_url( '/#organization' ) ),
		);
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => get_permalink() . '#breadcrumb',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Insights', 'item' => home_url( '/blog-and-insights/' ) ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title(), 'item' => get_permalink() ),
			),
		);
	}

	if ( is_page() ) {
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => get_permalink() . '#breadcrumb',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => get_the_title(), 'item' => get_permalink() ),
			),
		);
	}

	$faq = so_faq_pairs();
	if ( $faq ) {
		$items = array();
		foreach ( $faq as $pair ) {
			$items[] = array(
				'@type'          => 'Question',
				'name'           => $pair['q'],
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $pair['a'] ),
			);
		}
		$graph[] = array(
			'@type'      => 'FAQPage',
			'@id'        => so_canonical() . '#faq',
			'mainEntity' => $items,
		);
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)
	);
}
add_action( 'wp_head', 'so_json_ld', 3 );

/**
 * FAQ pairs stored at install time so the schema can never drift from the markup.
 *
 * @return array<int,array{q:string,a:string}>
 */
function so_faq_pairs() {
	if ( ! is_singular() ) {
		return array();
	}
	$raw = get_post_meta( get_the_ID(), '_so_faq', true );
	if ( ! $raw ) {
		return array();
	}
	$data = json_decode( $raw, true );
	return is_array( $data ) ? $data : array();
}
