<?php
/**
 * Blog presentation helpers: article excerpts, bylines and the shipped blog
 * chrome (hero, topic hubs, residency CTA) with its controls made real.
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Excerpt for a post, trimmed to a word count.
 *
 * @param int $post_id Post id.
 * @param int $words   Word count.
 * @return string
 */
function so_excerpt_for( $post_id, $words = 28 ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}
	$text = $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	return wp_trim_words( trim( preg_replace( '/\s+/', ' ', $text ) ), $words, '…' );
}

/**
 * Byline for an article.
 *
 * Articles are published by the institute rather than by a named individual
 * unless author meta is set, so the avatar is a monogram — never a stock face
 * attached to an invented person.
 *
 * @param int $post_id Post id.
 * @return array{name:string,role:string,avatar:string}
 */
function so_article_byline( $post_id ) {
	$name = (string) get_post_meta( $post_id, '_so_author_name', true );
	$role = (string) get_post_meta( $post_id, '_so_author_role', true );
	if ( '' === $name ) {
		$name = __( 'Skills Online Engineering Desk', 'skills-online' );
		$role = __( 'Reviewed by practising staff engineers', 'skills-online' );
	}
	$avatar = '<span class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-[13px]" aria-hidden="true">'
		. esc_html( strtoupper( substr( $name, 0, 1 ) ) ) . '</span>';
	return array( 'name' => $name, 'role' => $role, 'avatar' => $avatar );
}

/**
 * Render a shipped blog-chrome fragment with its controls made real.
 *
 * The exported blog screen contains a search input with no form, filter chips
 * with no handler, topic hubs pointing at #anchors, and a button that fires an
 * alert() — all corrected here rather than replaced.
 *
 * @param string $name hero|hubs|residency.
 * @return string
 */
function so_blog_fragment( $name ) {
	$path = get_template_directory() . '/inc/content/blog/' . sanitize_file_name( $name ) . '.html';
	if ( ! file_exists( $path ) ) {
		return '';
	}
	$html = (string) file_get_contents( $path );

	if ( 'hero' === $name ) {
		$form = '<form class="relative w-full" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">'
			. '<label class="so-visually-hidden" for="so-article-search">' . esc_html__( 'Search articles', 'skills-online' ) . '</label>'
			. '<input class="w-full pl-12 pr-28 py-3 bg-surface-container-low text-on-surface placeholder:text-on-surface-variant/70 rounded-lg font-body-md text-body-md focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary-container outline-none transition-all"'
			. ' id="so-article-search" data-so-search name="s" type="search" value="' . esc_attr( get_search_query() ) . '"'
			. ' placeholder="' . esc_attr__( 'Search architecture guides, benchmark reports or salary breakdowns…', 'skills-online' ) . '">'
			. '<button type="submit" class="absolute right-3.5 top-1/2 -translate-y-1/2 inline-flex items-center gap-1 px-2 py-1 rounded bg-surface-container text-on-surface-variant hover:text-primary font-label-caps text-label-caps" aria-label="' . esc_attr__( 'Submit search', 'skills-online' ) . '">'
			. '<span class="hidden sm:inline">' . esc_html__( '⌘K', 'skills-online' ) . '</span>'
			. '<span class="material-symbols-outlined text-[16px]" aria-hidden="true">search</span></button></form>';
		$html = preg_replace( '#<div class="relative w-full flex items-center">.*?</div>\s*(?=<!-- Filter Chips -->)#s', $form, $html, 1 );

		// Filter chips become real topic links.
		$terms   = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true ) );
		$current = is_category() ? (int) get_queried_object_id() : 0;
		$chips   = '<div class="flex items-center gap-2 overflow-x-auto pb-1 so-scroll-x" role="list">';
		$all_cls = $current ? 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface'
			: 'bg-primary-container text-on-primary shadow-sm';
		$chips  .= '<a role="listitem" class="px-3.5 py-1.5 rounded-full text-label-md font-label-md whitespace-nowrap transition-all ' . $all_cls . '" href="'
			. esc_url( home_url( '/blog-and-insights/' ) ) . '">' . esc_html__( 'All Topics', 'skills-online' ) . '</a>';
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$on      = ( $current === (int) $term->term_id );
				$cls     = $on ? 'bg-primary-container text-on-primary shadow-sm'
					: 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface';
				$chips  .= '<a role="listitem" class="px-3.5 py-1.5 rounded-full text-label-md font-label-md whitespace-nowrap transition-all ' . $cls . '" href="'
					. esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
			}
		}
		$chips .= '</div>';
		$html   = preg_replace( '#<div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" id="topic-filter-bar">.*?</div>\s*(?=</div>)#s', $chips, $html, 1 );
	}

	if ( 'hubs' === $name ) {
		$terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true, 'number' => 8 ) );
		$index = 0;
		$html  = preg_replace_callback(
			'#<a class="(bg-surface-container-lowest p-space-lg[^"]*)" href="#[^"]*">(.*?)</a>#s',
			function ( $m ) use ( $terms, &$index ) {
				if ( is_wp_error( $terms ) || ! isset( $terms[ $index ] ) ) {
					++$index;
					return '';
				}
				$term = $terms[ $index ];
				++$index;
				$body = preg_replace(
					'#<span>\d+ articles</span>#',
					'<span>' . esc_html( sprintf( /* translators: %d: article count. */ _n( '%d article', '%d articles', (int) $term->count, 'skills-online' ), (int) $term->count ) ) . '</span>',
					$m[2]
				);
				return '<a class="' . $m[1] . '" href="' . esc_url( get_term_link( $term ) ) . '">' . $body . '</a>';
			},
			$html
		);
		$total = (int) wp_count_posts()->publish;
		$html  = preg_replace(
			'#<a class="inline-flex items-center gap-1 font-label-lg[^"]*" href="#all-hubs">.*?</a>#s',
			'<a class="inline-flex items-center gap-1 font-label-lg text-label-lg text-primary hover:text-on-primary-fixed-variant transition-colors font-semibold" href="' . esc_url( home_url( '/blog-and-insights/' ) ) . '">'
			. esc_html( sprintf( /* translators: %d: number of published articles. */ _n( 'Browse all %d article', 'Browse all %d articles', $total, 'skills-online' ), $total ) )
			. '<span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span></a>',
			$html,
			1
		);
	}

	if ( 'residency' === $name ) {
		$html = preg_replace(
			'#<button[^>]*onclick="[^"]*"[^>]*>.*?</button>#s',
			'<a class="px-space-lg py-3 rounded-lg bg-primary text-on-primary font-label-lg text-label-lg font-bold shadow hover:bg-on-primary-fixed-variant transition-all text-center" href="' . esc_url( home_url( '/contact/' ) ) . '">'
			. esc_html__( 'Submit an Article Pitch', 'skills-online' ) . '</a>',
			$html,
			1
		);
		$html = str_replace( 'href="#editorial-standards"', 'href="' . esc_url( home_url( '/blog-and-insights/#editorial-standards' ) ) . '"', $html );
		$html = str_replace(
			'We pay competitive honorariums ($600 - $1,200 per published playbook) and pair you with a professional technical editor.',
			'We pay an honorarium for every published playbook and pair you with a technical editor. The editorial standards are published below.',
			$html
		);
	}

	return $html;
}

/**
 * Editorial standards block, anchored so the blog CTA link resolves.
 *
 * @return string
 */
function so_editorial_standards() {
	$items = array(
		__( 'Claims that depend on a number cite a source the reader can open — or the number is removed.', 'skills-online' ),
		__( 'Code samples are run against the version stated in the article, and the version is stated.', 'skills-online' ),
		__( 'No fabricated benchmarks, customer names, or "case studies" that are actually invented.', 'skills-online' ),
		__( 'Corrections are published in place with a dated note rather than silently edited.', 'skills-online' ),
	);
	$list = '';
	foreach ( $items as $item ) {
		$list .= '<li>' . esc_html( $item ) . '</li>';
	}
	return '<section id="editorial-standards" class="w-full px-margin-mobile md:px-margin py-space-xl">'
		. '<div class="max-w-3xl mx-auto so-prose">'
		. '<h2>' . esc_html__( 'Editorial standards', 'skills-online' ) . '</h2>'
		. '<p>' . esc_html__( 'Articles published here are written or reviewed by practising engineers. Before publication every piece is checked for the following:', 'skills-online' ) . '</p>'
		. '<ul>' . $list . '</ul>'
		. '<p>' . esc_html__( 'Pitch by contacting admissions with an outline, the intended reader, and what the reader will be able to do afterwards.', 'skills-online' ) . '</p>'
		. '</div></section>';
}

/**
 * The home page's "Latest analysis" band, rendered from real articles.
 *
 * The exported section carried three hand-written teaser cards with invented
 * read counts and a dead "Read all articles" anchor; this renders the design's
 * own card markup from the newest posts instead.
 *
 * @param int $count How many posts.
 * @return string
 */
function so_latest_posts_block( $count = 3 ) {
	$query = new WP_Query(
		array(
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
		)
	);
	if ( ! $query->have_posts() ) {
		return '';
	}
	$cards = '';
	while ( $query->have_posts() ) {
		$query->the_post();
		$cats = get_the_category();
		$cat  = $cats ? $cats[0] : null;
		$cards .= '<article class="bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden flex flex-col justify-between">'
			. '<div class="p-space-lg space-y-space-sm">'
			. '<div class="flex items-center justify-between text-body-sm text-on-surface-variant">'
			. '<span class="bg-primary/10 text-primary font-label-caps text-label-caps px-2 py-0.5 rounded">' . esc_html( $cat ? strtoupper( $cat->name ) : strtoupper( __( 'Insights', 'skills-online' ) ) ) . '</span>'
			. '<span>' . esc_html( so_read_time( get_the_ID() ) ) . '</span>'
			. '</div>'
			. '<h3 class="font-headline-sm text-headline-sm text-on-surface font-bold hover:text-primary transition-colors">'
			. '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>'
			. '<p class="font-body-sm text-body-sm text-on-surface-variant">' . esc_html( so_excerpt_for( get_the_ID(), 24 ) ) . '</p>'
			. '</div>'
			. '<div class="px-space-lg pb-space-lg pt-0">'
			. '<a class="text-primary font-label-md text-label-md font-semibold inline-flex items-center gap-1" href="' . esc_url( get_permalink() ) . '">'
			. esc_html__( 'Read the playbook', 'skills-online' )
			. '<span class="material-symbols-outlined text-xs" aria-hidden="true">arrow_forward</span></a>'
			. '</div></article>';
	}
	wp_reset_postdata();

	return '<section class="w-full bg-background py-space-xl">'
		. '<div class="max-w-7xl mx-auto px-margin-mobile md:px-margin space-y-space-lg">'
		. '<div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md">'
		. '<div class="space-y-space-xs">'
		. '<span class="font-label-caps text-label-caps text-primary bg-primary/10 px-space-sm py-1 rounded-full">' . esc_html__( 'INSIGHTS & CAREER PLAYBOOKS', 'skills-online' ) . '</span>'
		. '<h2 class="font-headline-lg text-headline-lg text-on-surface font-extrabold">' . esc_html__( 'Latest analysis, engineering deep-dives & guides', 'skills-online' ) . '</h2>'
		. '</div>'
		. '<a class="inline-flex items-center gap-space-xs text-primary font-label-lg text-label-lg font-bold hover:text-on-primary-fixed-variant transition-colors group" href="' . esc_url( home_url( '/blog-and-insights/' ) ) . '">'
		. '<span>' . esc_html__( 'Read all articles', 'skills-online' ) . '</span>'
		. '<span class="material-symbols-outlined text-base group-hover:translate-x-1 transition-transform" aria-hidden="true">arrow_forward</span></a>'
		. '</div>'
		. '<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">' . $cards . '</div>'
		. '</div></section>';
}

/**
 * Resolve every render-time marker in stored content.
 *
 * Forms need a nonce and a live request, and the home page's insight band needs
 * the real post loop — neither can be stored, so both are markers.
 *
 * @param string $content Content.
 * @return string
 */
function so_render_markers( $content ) {
	if ( false !== strpos( $content, '<!--SO_FORM:' ) ) {
		$content = preg_replace_callback(
			'/<!--SO_FORM:([a-z_]+)-->/',
			function ( $m ) {
				return so_form( $m[1] );
			},
			$content
		);
	}
	if ( false !== strpos( $content, '<!--SO_LATEST_POSTS-->' ) ) {
		$content = str_replace( '<!--SO_LATEST_POSTS-->', so_latest_posts_block( 3 ), $content );
	}
	return $content;
}
