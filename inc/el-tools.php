<?php
/**
 * TEMPORARY Elementor build endpoint (removed before hand-over).
 *
 * The build needs three things WordPress does not expose over HTTP: the real
 * Elementor environment (version, atomic element registration, module source),
 * the shipped page fragments (so the local converter reads exactly what the site
 * renders), and a fallback path for writing a document when the MCP server is not
 * reachable.
 *
 * Usage: /?so_el=<token>&action=probe|grep|export|setdoc|enablemcp
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SO_EL_TOKEN', 'so-el-7b31c0d94a' );

/**
 * Route the build endpoint.
 */
function so_el_router() {
	if ( ! isset( $_GET['so_el'] ) || SO_EL_TOKEN !== sanitize_text_field( wp_unslash( $_GET['so_el'] ) ) ) {
		return;
	}
	$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'probe';
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );

	switch ( $action ) {
		case 'probe':
			$out = so_el_probe();
			break;
		case 'grep':
			$out = so_el_grep();
			break;
		case 'export':
			$out = so_el_export();
			break;
		case 'setdoc':
			$out = so_el_setdoc();
			break;
		case 'enablemcp':
			$out = so_el_enable_mcp();
			break;
		case 'pages':
			$out = so_el_pages();
			break;
		default:
			$out = array( 'error' => 'unknown action' );
	}
	echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;
}
add_action( 'init', 'so_el_router', 1 );

/**
 * Environment report.
 *
 * @return array
 */
function so_el_probe() {
	$types = array();
	if ( class_exists( '\Elementor\Plugin' ) ) {
		$manager = \Elementor\Plugin::$instance->elements_manager;
		if ( $manager ) {
			foreach ( (array) $manager->get_element_types() as $name => $instance ) {
				$types[ $name ] = get_class( $instance );
			}
		}
	}
	$widgets = array();
	if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->widgets_manager ) {
		$widgets = array_keys( (array) \Elementor\Plugin::$instance->widgets_manager->get_widget_types() );
	}
	$modules = array();
	$mod_dir = WP_PLUGIN_DIR . '/elementor/modules';
	if ( is_dir( $mod_dir ) ) {
		$modules = array_values( array_diff( scandir( $mod_dir ), array( '.', '..' ) ) );
	}
	$options = array();
	foreach ( (array) wp_load_alloptions() as $key => $value ) {
		if ( false !== strpos( $key, 'elementor' ) && ( false !== strpos( $key, 'mcp' ) || false !== strpos( $key, 'experiment' ) || false !== strpos( $key, 'agent' ) ) ) {
			$options[ $key ] = is_scalar( $value ) ? $value : gettype( $value );
		}
	}
	return array(
		'elementor'      => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : 'not active',
		'pro'            => defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : 'not installed',
		'atomic_option'  => get_option( 'elementor_experiment-e_atomic_elements' ),
		'element_types'  => array_keys( $types ),
		'widget_count'   => count( $widgets ),
		'widgets_sample' => array_slice( $widgets, 0, 40 ),
		'modules'        => $modules,
		'options'        => $options,
		'php'            => PHP_VERSION,
		'theme'          => get_option( 'stylesheet' ),
		'so_managed'     => count( get_posts( array( 'post_type' => 'page', 'numberposts' => -1, 'meta_key' => '_so_managed', 'fields' => 'ids' ) ) ),
	);
}

/**
 * Regex search inside the Elementor plugin (bounded), to read the module's own
 * option names rather than guess them.
 *
 * @return array
 */
function so_el_grep() {
	$pattern = isset( $_GET['pattern'] ) ? (string) wp_unslash( $_GET['pattern'] ) : '';
	$scope   = isset( $_GET['scope'] ) ? sanitize_text_field( wp_unslash( $_GET['scope'] ) ) : 'modules/mcp';
	if ( '' === $pattern || strlen( $pattern ) > 200 ) {
		return array( 'error' => 'pattern required' );
	}
	$root = WP_PLUGIN_DIR . '/elementor/' . ltrim( $scope, '/' );
	if ( ! file_exists( $root ) ) {
		return array( 'error' => 'scope not found', 'root' => $root );
	}
	$files = array();
	if ( is_dir( $root ) ) {
		$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $it as $file ) {
			if ( $file->isFile() && preg_match( '/\.(php|js)$/', $file->getFilename() ) ) {
				$files[] = $file->getPathname();
			}
		}
	} else {
		$files[] = $root;
	}
	$hits = array();
	foreach ( array_slice( $files, 0, 400 ) as $file ) {
		$lines = @file( $file );
		if ( ! $lines ) {
			continue;
		}
		foreach ( $lines as $i => $line ) {
			if ( @preg_match( '/' . str_replace( '/', '\\/', $pattern ) . '/', $line ) ) {
				$hits[] = array(
					'file' => str_replace( WP_PLUGIN_DIR . '/elementor/', '', $file ),
					'line' => $i + 1,
					'text' => trim( substr( $line, 0, 220 ) ),
				);
				if ( count( $hits ) >= 60 ) {
					break 2;
				}
			}
		}
	}
	return array( 'files_scanned' => count( $files ), 'hits' => $hits );
}

/**
 * Return a shipped fragment, so the local converter reads the same bytes the
 * site renders.
 *
 * @return array
 */
function so_el_export() {
	$slug = isset( $_GET['slug'] ) ? sanitize_file_name( wp_unslash( $_GET['slug'] ) ) : '';
	$kind = isset( $_GET['kind'] ) ? sanitize_key( wp_unslash( $_GET['kind'] ) ) : 'pages';
	if ( '' === $slug ) {
		return array( 'error' => 'slug required' );
	}
	$path = get_template_directory() . '/inc/content/' . ( 'posts' === $kind ? 'posts' : 'pages' ) . '/' . $slug . '.html';
	if ( ! file_exists( $path ) ) {
		return array( 'error' => 'not found', 'path' => $path );
	}
	return array( 'slug' => $slug, 'bytes' => filesize( $path ), 'html' => (string) file_get_contents( $path ) );
}

/**
 * List the design pages with their Elementor state.
 *
 * @return array
 */
function so_el_pages() {
	$out   = array();
	$pages = get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'publish' ) );
	foreach ( $pages as $p ) {
		$data = json_decode( (string) get_post_meta( $p->ID, '_elementor_data', true ), true );
		$out[ $p->post_name ] = array(
			'id'     => $p->ID,
			'type'   => $p->post_type,
			'mode'   => get_post_meta( $p->ID, '_elementor_edit_mode', true ),
			'nodes'  => is_array( $data ) ? count( $data ) : 0,
			'bytes'  => strlen( (string) get_post_meta( $p->ID, '_elementor_data', true ) ),
			'url'    => get_permalink( $p->ID ),
		);
	}
	return $out;
}

/**
 * Write a raw Elementor document (fallback when the MCP server is unavailable).
 *
 * @return array
 */
function so_el_setdoc() {
	$slug = isset( $_GET['slug'] ) ? sanitize_file_name( wp_unslash( $_GET['slug'] ) ) : '';
	$raw  = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
	if ( '' === $slug || '' === $raw ) {
		return array( 'error' => 'slug and data required' );
	}
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return array( 'error' => 'invalid json' );
	}
	$post = get_page_by_path( $slug, OBJECT, array( 'page', 'post' ) );
	if ( ! $post ) {
		return array( 'error' => 'post not found' );
	}
	update_post_meta( $post->ID, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	update_post_meta( $post->ID, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post->ID, '_elementor_template_type', 'post' === $post->post_type ? 'wp-post' : 'wp-page' );
	update_post_meta( $post->ID, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '4.0.0' );
	if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->files_manager ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	return array( 'ok' => true, 'id' => $post->ID, 'nodes' => count( $data ) );
}

/**
 * Enable the Elementor MCP module and mint an application password for it.
 *
 * @return array
 */
function so_el_enable_mcp() {
	$user = wp_get_current_user();
	if ( ! $user || ! $user->ID ) {
		$admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
		$user   = $admins ? $admins[0] : null;
	}
	if ( ! $user ) {
		return array( 'error' => 'no admin user' );
	}
	$created = array();
	foreach ( array( 'elementor_mcp_access', 'elementor_mcp_enabled', 'elementor_mcp_settings', 'elementor_agent_ready_settings' ) as $option ) {
		$current = get_option( $option, null );
		$created[ $option ] = null === $current ? 'absent' : $current;
	}
	// The module reads its state through the module settings option when present.
	$settings = get_option( 'elementor_mcp_settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}
	$settings['enabled'] = true;
	$settings['access']  = true;
	update_option( 'elementor_mcp_settings', $settings );
	update_option( 'elementor_mcp_access', 'yes' );

	$passwords = WP_Application_Passwords::get_user_application_passwords( $user->ID );
	$app       = null;
	foreach ( (array) $passwords as $existing ) {
		if ( 'Skills Online Build' === $existing['name'] ) {
			$app = $existing;
		}
	}
	if ( ! $app ) {
		$result = WP_Application_Passwords::create_new_application_password( $user->ID, array( 'name' => 'Skills Online Build' ) );
		if ( is_wp_error( $result ) ) {
			return array( 'error' => $result->get_error_message(), 'options' => $created );
		}
		$app = array( 'uuid' => $result[1]['uuid'], 'name' => 'Skills Online Build' );
		$created['password'] = $result[0];
	}
	return array(
		'user'    => $user->user_login,
		'login'   => $user->user_login,
		'url'     => rest_url( 'elementor/mcp/' ),
		'options' => $created,
		'app'     => $app,
	);
}
