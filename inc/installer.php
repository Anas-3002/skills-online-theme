<?php
/**
 * TEMPORARY provisioning endpoint (removed before hand-over).
 *
 * Hostinger's token for this account cannot create websites, register installs
 * or mint an auto-login link, so the build provisions itself from inside the
 * theme: plugins, options, pages, posts, categories and menus.
 *
 * Every stage is idempotent (pages and posts match on slug and are updated), so
 * re-running is safe. The token is deliberately not a secret — this file is
 * deleted from the shipped theme, and the restore commit is noted in the repo.
 *
 * Usage: /?so_install=<token>&stage=all|plugins|content|report|sync
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SO_INSTALL_TOKEN', 'so-build-2f9c41ab7e' );

/**
 * Route the installer.
 */
function so_install_router() {
	if ( ! isset( $_GET['so_install'] ) || SO_INSTALL_TOKEN !== sanitize_text_field( wp_unslash( $_GET['so_install'] ) ) ) {
		return;
	}
	$stage = isset( $_GET['stage'] ) ? sanitize_key( wp_unslash( $_GET['stage'] ) ) : 'report';
	$out   = array( 'stage' => $stage );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );

	if ( in_array( $stage, array( 'all', 'plugins' ), true ) ) {
		$out['plugins'] = so_install_plugins();
	}
	if ( in_array( $stage, array( 'all', 'content', 'sync' ), true ) ) {
		$out['options'] = so_install_options();
		$out['terms']   = so_install_terms();
		$out['pages']   = so_install_pages();
		$out['posts']   = so_install_posts();
		$out['menus']   = so_install_menus();
	}
	if ( in_array( $stage, array( 'all', 'report' ), true ) ) {
		$out['report'] = so_install_report();
	}

	echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;
}
add_action( 'init', 'so_install_router', 1 );

/**
 * Install and activate the plugins the build needs.
 *
 * Elementor Free only. No Pro, no add-ons, no caching plugin (Hostinger's stack
 * already provides page caching).
 *
 * @return array
 */
function so_install_plugins() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$wanted = array(
		'elementor/elementor.php' => 'elementor',
	);
	$result = array();

	foreach ( $wanted as $file => $slug ) {
		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $file ) ) {
			$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
			$install  = $upgrader->install( 'https://downloads.wordpress.org/plugin/' . $slug . '.latest-stable.zip' );
			$result[ $slug ]['install'] = is_wp_error( $install ) ? $install->get_error_message() : (bool) $install;
			// Re-resolve the plugin's main file after install.
			$found = so_find_plugin_file( $slug );
			if ( $found ) {
				$file = $found;
			}
		}
		$active = is_plugin_active( $file );
		if ( ! $active ) {
			$activate = activate_plugin( $file );
			$result[ $slug ]['activate'] = is_wp_error( $activate ) ? $activate->get_error_message() : true;
		} else {
			$result[ $slug ]['activate'] = 'already active';
		}
		$result[ $slug ]['version'] = defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : 'unknown';
	}

	// Elementor: turn on the atomic (v4) elements and the editor opt-in, and the
	// MCP module the build drives. These are the documented experiment switches.
	$experiments = array(
		'e_atomic_elements' => 'active',
		'e_opt_in_v4'       => 'active',
		'container'         => 'active',
	);
	foreach ( $experiments as $name => $state ) {
		update_option( 'elementor_experiment-' . $name, $state );
	}
	update_option( 'elementor_disable_color_schemes', 'yes' );
	update_option( 'elementor_disable_typography_schemes', 'yes' );
	update_option( 'elementor_css_print_method', 'internal' );
	update_option( 'elementor_experiment-e_editor_v2', 'active' );

	return $result;
}

/**
 * Locate an installed plugin's main file.
 *
 * @param string $slug Plugin slug.
 * @return string|false
 */
function so_find_plugin_file( $slug ) {
	$dir = WP_PLUGIN_DIR . '/' . $slug;
	if ( ! is_dir( $dir ) ) {
		return false;
	}
	$candidates = glob( $dir . '/*.php' );
	if ( ! $candidates ) {
		return false;
	}
	foreach ( $candidates as $file ) {
		$data = get_file_data( $file, array( 'Name' => 'Plugin Name' ) );
		if ( ! empty( $data['Name'] ) ) {
			return $slug . '/' . basename( $file );
		}
	}
	return false;
}

/**
 * Site options: identity, permalinks, front page, posts page, cohort date.
 *
 * @return array
 */
function so_install_options() {
	$tz = 'Asia/Karachi';
	update_option( 'timezone_string', $tz );
	update_option( 'blogname', 'Skills Online' );
	update_option( 'blogdescription', 'Cohort-based engineering tracks with weekly 1-on-1 staff-engineer mentorship and reviewed production work.' );
	update_option( 'admin_email', 'mohiyuddinoalamgir@gmail.com' );
	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'blog_public', 1 );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'posts_per_page', 9 );
	update_option( 'show_avatars', 0 );
	update_option( 'rss_use_excerpt', 1 );

	// Real cohort deadline: last Monday of the coming month, 23:59 site time.
	$now   = new DateTimeImmutable( 'now', new DateTimeZone( $tz ) );
	$first = $now->modify( 'first day of next month' );
	$last  = $first->modify( 'last day of this month' );
	$last  = $last->modify( sprintf( '%+d days', 1 - (int) $last->format( 'N' ) ) )->setTime( 23, 59, 0 );
	update_option( 'so_cohort_deadline', $last->format( 'Y-m-d H:i:s' ) );

	// Front page / posts page.
	$home  = get_page_by_path( 'home' );
	$blog  = get_page_by_path( 'blog-and-insights' );
	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
	}
	if ( $blog ) {
		update_option( 'page_for_posts', $blog->ID );
	}

	flush_rewrite_rules( false );

	return array(
		'blogname'        => get_option( 'blogname' ),
		'permalink'       => get_option( 'permalink_structure' ),
		'page_on_front'   => get_option( 'page_on_front' ),
		'page_for_posts'  => get_option( 'page_for_posts' ),
		'cohort_deadline' => get_option( 'so_cohort_deadline' ),
	);
}

/**
 * Categories used by the knowledge base.
 *
 * @return array
 */
function so_install_terms() {
	$categories = array(
		'AI & LLM Systems'               => 'Embeddings, retrieval, fine-tuning, inference optimisation and the operational reality of running model-backed services.',
		'Full-Stack & Cloud Architecture' => 'Service design, data modelling, caching, distributed systems and the trade-offs behind them.',
		'DevOps & Kubernetes'            => 'Platform engineering, infrastructure as code, delivery pipelines, observability and reliability practice.',
		'Leadership & Staff Path'        => 'Written decisions, architecture review, mentoring and the scope of staff-level engineering work.',
		'Hiring Insights'                => 'How engineering hiring actually works: interview loops, portfolios, compensation research and the signals that matter.',
		'Career Strategy'                => 'Choosing a specialisation, sequencing your learning, and building a body of work that speaks for you.',
	);
	$out = array();
	foreach ( $categories as $name => $description ) {
		$term = term_exists( $name, 'category' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'category', array( 'description' => $description ) );
		} else {
			wp_update_term( (int) $term['term_id'], 'category', array( 'description' => $description ) );
		}
		$out[ $name ] = is_wp_error( $term ) ? $term->get_error_message() : (int) $term['term_id'];
	}
	// Remove the default category once real ones exist.
	$default = (int) get_option( 'default_category' );
	if ( $default && ! in_array( 'Uncategorized', array_keys( $categories ), true ) ) {
		$uncat = get_term_by( 'slug', 'uncategorized', 'category' );
		if ( $uncat && (int) $uncat->term_id !== $default ) {
			// leave it; WordPress requires a default category to exist
		}
	}
	return $out;
}

/**
 * Create or update the design pages from inc/content/site.json.
 *
 * @return array
 */
function so_install_pages() {
	$manifest = json_decode( (string) file_get_contents( get_template_directory() . '/inc/content/site.json' ), true );
	if ( ! is_array( $manifest ) ) {
		return array( 'error' => 'site.json unreadable' );
	}
	$created = array();
	$order   = 0;

	foreach ( $manifest as $page ) {
		$slug = $page['slug'];
		$path = get_template_directory() . '/inc/content/pages/' . $slug . '.html';
		$body = file_exists( $path ) ? (string) file_get_contents( $path ) : '';
		++$order;

		$existing = get_page_by_path( $slug );
		$args     = array(
			'post_title'   => $page['title'],
			'post_name'    => $slug,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => $body,
			'menu_order'   => $order,
		);
		if ( $existing ) {
			$args['ID'] = $existing->ID;
			$id         = wp_update_post( $args, true );
		} else {
			$id = wp_insert_post( $args, true );
		}
		if ( is_wp_error( $id ) ) {
			$created[ $slug ] = $id->get_error_message();
			continue;
		}

		update_post_meta( $id, '_so_managed', 1 );
		update_post_meta( $id, '_so_meta_description', $page['meta_description'] );
		if ( ! empty( $page['faq'] ) ) {
			update_post_meta( $id, '_so_faq', wp_json_encode( $page['faq'] ) );
		} else {
			delete_post_meta( $id, '_so_faq' );
		}
		if ( ! empty( $page['posts_page'] ) ) {
			update_option( 'page_for_posts', $id );
		}
		$created[ $slug ] = $id;
	}
	return $created;
}

/**
 * Create or update the articles from inc/content/posts.json.
 *
 * @return array
 */
function so_install_posts() {
	$manifest = json_decode( (string) file_get_contents( get_template_directory() . '/inc/content/posts.json' ), true );
	if ( ! is_array( $manifest ) ) {
		return array( 'error' => 'posts.json unreadable' );
	}
	$out   = array();
	$sticky = array();

	foreach ( $manifest as $post ) {
		$path = get_template_directory() . '/inc/content/posts/' . $post['slug'] . '.html';
		$body = file_exists( $path ) ? (string) file_get_contents( $path ) : '';
		$term = get_term_by( 'name', $post['category'], 'category' );

		$existing = get_page_by_path( $post['slug'], OBJECT, 'post' );
		$args     = array(
			'post_title'   => $post['title'],
			'post_name'    => $post['slug'],
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_content' => $body,
			'post_excerpt' => $post['excerpt'],
			'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( count( $out ) * 3 + 2 ) . ' days' ) ),
		);
		if ( $existing ) {
			$args['ID'] = $existing->ID;
			$id         = wp_update_post( $args, true );
		} else {
			$id = wp_insert_post( $args, true );
		}
		if ( is_wp_error( $id ) ) {
			$out[ $post['slug'] ] = $id->get_error_message();
			continue;
		}
		if ( $term && ! is_wp_error( $term ) ) {
			wp_set_post_terms( $id, array( (int) $term->term_id ), 'category' );
		}
		if ( ! empty( $post['tags'] ) ) {
			wp_set_post_terms( $id, $post['tags'], 'post_tag' );
		}
		update_post_meta( $id, '_so_meta_description', $post['meta_description'] );
		update_post_meta( $id, '_so_sources', wp_json_encode( $post['sources'] ) );
		if ( ! empty( $post['sticky'] ) ) {
			$sticky[] = $id;
		}
		$out[ $post['slug'] ] = $id;
	}
	if ( $sticky ) {
		update_option( 'sticky_posts', $sticky );
	}
	return $out;
}

/**
 * Build the primary menu from the design's navigation.
 *
 * @return array
 */
function so_install_menus() {
	$items = array(
		'courses-and-tracks'   => 'Courses & Tracks',
		'enterprise'           => 'Enterprise',
		'pillars-and-outcomes' => 'Pillars & Outcomes',
		'pricing'              => 'Pricing',
		'blog-and-insights'    => 'Blog & Insights',
		'reviews'              => 'Reviews',
	);
	$menu_name = 'Primary';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
	} else {
		$menu_id = $menu->term_id;
		// Clear existing items so the menu matches the design after a re-run.
		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
			wp_delete_post( $item->ID, true );
		}
	}
	if ( is_wp_error( $menu_id ) ) {
		return array( 'error' => $menu_id->get_error_message() );
	}
	$added = array();
	foreach ( $items as $slug => $label ) {
		$page = get_page_by_path( $slug );
		$id   = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $label,
				'menu-item-object'    => $page ? 'page' : 'custom',
				'menu-item-object-id' => $page ? $page->ID : 0,
				'menu-item-type'      => $page ? 'post_type' : 'custom',
				'menu-item-url'       => $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' ),
				'menu-item-status'    => 'publish',
			)
		);
		$added[ $slug ] = is_wp_error( $id ) ? $id->get_error_message() : $id;
	}
	$locations            = (array) get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	return $added;
}

/**
 * Current state, for verification.
 *
 * @return array
 */
function so_install_report() {
	$pages = get_posts( array( 'post_type' => 'page', 'numberposts' => -1, 'post_status' => 'publish' ) );
	$posts = get_posts( array( 'post_type' => 'post', 'numberposts' => -1, 'post_status' => 'publish' ) );
	$el    = array();
	foreach ( $pages as $p ) {
		$el[ $p->post_name ] = get_post_meta( $p->ID, '_elementor_edit_mode', true ) ?: '-';
	}
	return array(
		'theme'        => get_option( 'stylesheet' ),
		'home_url'     => home_url( '/' ),
		'front_page'   => get_option( 'page_on_front' ),
		'posts_page'   => get_option( 'page_for_posts' ),
		'pages'        => count( $pages ),
		'posts'        => count( $posts ),
		'categories'   => wp_list_pluck( get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) ), 'name' ),
		'elementor'    => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : 'not active',
		'atomic'       => get_option( 'elementor_experiment-e_atomic_elements' ),
		'elementor_on' => $el,
		'menu'         => wp_get_nav_menu_items( 'Primary' ) ? 'ok' : 'missing',
		'submissions'  => (int) wp_count_posts( 'so_submission' )->private,
	);
}
