<?php
/**
 * Small view helpers shared by the templates.
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Material-symbols icon span (the icon font is subset to the icons in use).
 *
 * @param string $name  Icon name (see build/icons.json).
 * @param string $class Extra utility classes.
 * @param bool   $fill  Filled variant.
 * @return string
 */
function so_icon( $name, $class = '', $fill = false ) {
	return sprintf(
		'<span class="material-symbols-outlined %1$s%2$s" aria-hidden="true">%3$s</span>',
		esc_attr( $class ),
		$fill ? ' so-fill' : '',
		esc_html( $name )
	);
}

/**
 * Course-level badge — Beginner / Intermediate / Advanced, per the design system.
 *
 * @param string $level One of beginner|intermediate|advanced (case-insensitive).
 * @return string
 */
function so_level_badge( $level ) {
	$map = array(
		'beginner'     => array( 'bg-[#E8FBF4] text-[#118358]', 'Beginner' ),
		'intermediate' => array( 'bg-[#EEF0FD] text-[#5A5CE6]', 'Intermediate' ),
		'advanced'     => array( 'bg-[#FEF6EB] text-[#B26A0E]', 'Advanced' ),
	);
	$key = strtolower( trim( (string) $level ) );
	if ( ! isset( $map[ $key ] ) ) {
		return '';
	}
	return sprintf(
		'<span class="inline-flex items-center rounded-full px-[10px] py-[4px] font-label-caps text-label-caps uppercase %1$s">%2$s</span>',
		esc_attr( $map[ $key ][0] ),
		esc_html( $map[ $key ][1] )
	);
}

/**
 * Small labelled pill used inside content cards.
 *
 * @param string $text  Label.
 * @param string $tone  primary|amber|mint|neutral.
 * @param string $extra Extra utility classes.
 * @return string
 */
function so_pill( $text, $tone = 'primary', $extra = '' ) {
	$map = array(
		'primary' => 'bg-primary-container/12 text-primary',
		'amber'   => 'bg-secondary-container/20 text-on-secondary-container',
		'mint'    => 'bg-tertiary-fixed/35 text-tertiary',
		'neutral' => 'bg-surface-container-high text-on-surface-variant',
	);
	$tone = isset( $map[ $tone ] ) ? $tone : 'primary';
	return sprintf(
		'<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 %1$s font-label-caps text-label-caps uppercase %2$s">%3$s</span>',
		$map[ $tone ],
		esc_attr( $extra ),
		esc_html( $text )
	);
}

/**
 * Human "x min read" label for the current post.
 *
 * @param int|null $post_id Optional post id.
 * @return string
 */
function so_read_time( $post_id = null ) {
	$post = $post_id ? get_post( $post_id ) : get_post();
	if ( ! $post ) {
		return '';
	}
	$words = str_word_count( wp_strip_all_tags( $post->post_content ) );
	$mins  = max( 1, (int) ceil( $words / 220 ) );
	/* translators: %d: minutes. */
	return sprintf( _n( '%d min read', '%d min read', $mins, 'skills-online' ), $mins );
}

/**
 * Reusable eyebrow + heading + intro block (design's section header pattern).
 *
 * @param string $eyebrow Small uppercase label.
 * @param string $title   Heading (escaped HTML allowed).
 * @param string $intro   Supporting copy.
 * @param string $tag     Heading tag (h1|h2|h3).
 * @param bool   $center  Centre the block.
 * @return string
 */
function so_section_head( $eyebrow, $title, $intro = '', $tag = 'h2', $center = true ) {
	$tag = in_array( $tag, array( 'h1', 'h2', 'h3' ), true ) ? $tag : 'h2';
	$out = '<div class="' . ( $center ? 'text-center' : '' ) . ' max-w-3xl ' . ( $center ? 'mx-auto' : '' ) . ' mb-space-xl">';
	if ( $eyebrow ) {
		$out .= '<span class="font-label-caps text-label-caps text-primary uppercase tracking-widest">' . esc_html( $eyebrow ) . '</span>';
	}
	$out .= sprintf(
		'<%1$s class="font-headline-lg text-headline-lg md:text-display-xl md:font-display-xl text-on-surface tracking-tight mt-2 mb-space-sm">%2$s</%1$s>',
		$tag,
		$title
	);
	if ( $intro ) {
		$out .= '<p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">' . $intro . '</p>';
	}
	$out .= '</div>';
	return $out;
}

/**
 * Primary navigation, from the WordPress menu when one is assigned.
 *
 * @return array<int,array{label:string,url:string,current:bool}>
 */
function so_primary_nav() {
	$fallback = array(
		'/courses-and-tracks/'   => __( 'Courses &amp; Tracks', 'skills-online' ),
		'/enterprise/'           => __( 'Enterprise', 'skills-online' ),
		'/pillars-and-outcomes/' => __( 'Pillars &amp; Outcomes', 'skills-online' ),
		'/pricing/'              => __( 'Pricing', 'skills-online' ),
		'/blog-and-insights/'    => __( 'Blog &amp; Insights', 'skills-online' ),
		'/reviews/'              => __( 'Reviews', 'skills-online' ),
	);

	$menu_items = array();
	$locations  = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) ) {
		foreach ( (array) wp_get_nav_menu_items( $locations['primary'] ) as $item ) {
			if ( $item && 'publish' === get_post_status( $item->ID ) ) {
				$menu_items[] = array(
					'label' => wp_specialchars_decode( $item->title, ENT_QUOTES ),
					'url'   => $item->url,
				);
			}
		}
	}

	$items = array();
	if ( $menu_items ) {
		foreach ( $menu_items as $item ) {
			$items[] = array(
				'label'   => $item['label'],
				'url'     => $item['url'],
				'current' => untrailingslashit( $item['url'] ) === untrailingslashit( so_current_absolute_url() ),
			);
		}
		return $items;
	}

	foreach ( $fallback as $path => $label ) {
		$items[] = array(
			'label'   => html_entity_decode( $label, ENT_QUOTES, 'UTF-8' ),
			'url'     => home_url( $path ),
			'current' => so_is_current( $path ),
		);
	}
	return $items;
}

/**
 * True when the current request matches a design route.
 *
 * @param string $path Root-relative path with trailing slash.
 * @return bool
 */
function so_is_current( $path ) {
	$current = trailingslashit( (string) wp_parse_url( so_current_url(), PHP_URL_PATH ) );
	if ( '/' === $current ) {
		return '/' === $path;
	}
	if ( $current === $path ) {
		return true;
	}
	// A child page keeps its section URLs active; the blog archive covers posts.
	if ( '/blog-and-insights/' === $path && ( is_singular( 'post' ) || is_category() || is_tag() ) ) {
		return true;
	}
	return false;
}

/**
 * Current request path (no query string).
 *
 * @return string
 */
function so_current_url() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	return (string) strtok( $uri, '?' );
}

/**
 * Absolute URL of the current request, for menu highlighting.
 *
 * @return string
 */
function so_current_absolute_url() {
	return home_url( so_current_url() );
}

/**
 * Where the signed-in learner lands.
 *
 * @return string
 */
function so_portal_url() {
	return is_user_logged_in() ? admin_url( 'profile.php' ) : home_url( '/sign-in/' );
}

/**
 * The real date the current cohort's enrollment closes.
 *
 * A countdown is only honest if it counts to a date that exists, so this is a
 * stored date (option `so_cohort_deadline`) rather than a hard-coded "48H".
 *
 * @return string MySQL date (Y-m-d H:i:s) in site time.
 */
function so_cohort_deadline() {
	$stored = (string) get_option( 'so_cohort_deadline', '' );
	if ( $stored && strtotime( $stored ) ) {
		return gmdate( 'Y-m-d\TH:i:s\Z', (int) strtotime( $stored ) );
	}
	// Default: the last Monday of the coming month, 23:59 site time.
	$tz   = wp_timezone();
	$now  = new DateTimeImmutable( 'now', $tz );
	$next = $now->modify( 'first day of next month' );
	$last = $next->modify( 'last day of this month' );
	$dow  = (int) $last->format( 'N' );
	$last = $last->modify( sprintf( '%+d days', 1 - $dow ) )->setTime( 23, 59, 0 );
	return $last->format( 'Y-m-d\TH:i:sP' );
}

/**
 * Cohort label, e.g. "FALL 2026" / "Q4 2026", derived from the deadline.
 *
 * @return string
 */
function so_cohort_label() {
	$ts = (int) strtotime( (string) get_option( 'so_cohort_deadline', '' ) );
	if ( ! $ts ) {
		$ts = (int) strtotime( 'first day of next month' );
	}
	$tz = wp_timezone();
	$dt = new DateTimeImmutable( '@' . $ts );
	$dt = $dt->setTimezone( $tz );
	return strtoupper( $dt->format( 'F Y' ) ) . ' COHORT';
}

/**
 * Server-rendered countdown label; the JS keeps it live.
 *
 * @param string $deadline ISO date.
 * @return string
 */
function so_cohort_countdown_text( $deadline ) {
	$ts = (int) strtotime( $deadline );
	if ( ! $ts ) {
		return __( 'ENROLLING NOW', 'skills-online' );
	}
	$left = $ts - time();
	if ( $left <= 0 ) {
		return __( 'ENROLLMENT OPEN', 'skills-online' );
	}
	$days = (int) floor( $left / DAY_IN_SECONDS );
	$hrs  = (int) floor( ( $left % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
	/* translators: 1: days, 2: hours. */
	return sprintf( __( 'CLOSES IN %1$dD %2$02dH', 'skills-online' ), $days, $hrs );
}
