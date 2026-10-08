<?php
/**
 * TEMPORARY build tool: introspects this Elementor install and provisions the
 * credentials needed to drive its official MCP server. Removed once the native
 * element conversion is done.
 *
 *   /?so_el=types&so_token=…
 *   /?so_el=creds&so_token=…      (creates one application password, returns it once)
 *   /?so_el=revoke&so_token=…     (revokes every application password named here)
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CT_ELP_TOKEN' ) ) {
	define( 'CT_ELP_TOKEN', 'so-elp-3f81c47ad902' );
}

if ( ! defined( 'CT_ELP_APP' ) ) {
	define( 'CT_ELP_APP', 'hermes-el-build' );
}

/**
 * Router.
 */
function so_elp_router() {
	if ( empty( $_GET['so_el'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$token = isset( $_GET['so_token'] ) ? (string) wp_unslash( $_GET['so_token'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! hash_equals( CT_ELP_TOKEN, $token ) ) {
		status_header( 403 );
		exit( 'forbidden' );
	}
	@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$action = sanitize_key( wp_unslash( $_GET['so_el'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$out    = array( 'action' => $action );

	if ( 'types' === $action ) {
		$out['elementor']    = defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null;
		$out['wp']           = get_bloginfo( 'version' );
		$out['experiments']  = get_option( 'elementor_experiment' );
		$out['mcp_enabled']  = get_option( 'elementor_mcp_enabled' );
		$wm                  = \Elementor\Plugin::$instance->widgets_manager;
		$all                 = array_keys( $wm->get_widget_types() );
		$out['atomic_types'] = array_values( array_filter( $all, function ( $t ) { return 0 === strpos( $t, 'e-' ); } ) );
		$out['classic']      = array_values( array_filter( $all, function ( $t ) { return 0 !== strpos( $t, 'e-' ); } ) );
		$out['elements']     = array_keys( \Elementor\Plugin::$instance->elements_manager->get_element_types() );
		$dirs                = glob( WP_PLUGIN_DIR . '/elementor/modules/atomic-widgets/elements/*', GLOB_ONLYDIR );
		$out['source_dirs']  = $dirs ? array_map( 'basename', $dirs ) : array();
		$out['php']          = PHP_VERSION;
		$out['caps']         = class_exists( '\WP_Application_Passwords' );
	}

	if ( 'creds' === $action ) {
		$uid = 0;
		foreach ( get_users( array( 'role' => 'administrator', 'number' => 5 ) ) as $u ) {
			$uid = $u->ID;
			$out['user'] = $u->user_login;
			break;
		}
		if ( ! $uid ) {
			$out['error'] = 'no administrator found';
		} else {
			$res = \WP_Application_Passwords::create_new_application_password( $uid, array( 'name' => CT_ELP_APP ) );
			if ( is_wp_error( $res ) ) {
				$out['error'] = $res->get_error_message();
			} else {
				$out['login']    = $out['user'];
				$out['password'] = $res[0];
				$out['mcp_url']  = rest_url( 'elementor/mcp/' );
			}
		}
	}

	if ( 'revoke' === $action ) {
		$n = 0;
		foreach ( get_users( array( 'role' => 'administrator', 'number' => 5 ) ) as $u ) {
			foreach ( (array) \WP_Application_Passwords::get_user_application_passwords( $u->ID ) as $p ) {
				if ( CT_ELP_APP === $p['name'] ) {
					\WP_Application_Passwords::delete_application_password( $u->ID, $p['uuid'] );
					$n++;
				}
			}
		}
		$out['revoked'] = $n;
	}

	if ( 'media' === $action ) {
		// Sideload the theme's design images into the media library so the client
		// can manage/replace them from WordPress, and report url → attachment id.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$map = array();
		foreach ( (array) glob( get_template_directory() . '/assets/img/*' ) as $file ) {
			$name = basename( $file );
			$slug = sanitize_title( pathinfo( $name, PATHINFO_FILENAME ) );
			$have = get_posts( array( 'post_type' => 'attachment', 'name' => $slug, 'numberposts' => 1, 'post_status' => 'inherit' ) );
			if ( $have ) {
				$map[ $name ] = array( 'id' => $have[0]->ID, 'url' => wp_get_attachment_url( $have[0]->ID ) );
				continue;
			}
			$up  = wp_upload_bits( $name, null, (string) file_get_contents( $file ) );
			if ( ! empty( $up['error'] ) ) {
				$map[ $name ] = array( 'error' => $up['error'] );
				continue;
			}
			$id = wp_insert_attachment(
				array(
					'post_mime_type' => $up['type'],
					'post_title'     => $slug,
					'post_content'   => '',
					'post_status'    => 'inherit',
				),
				$up['file']
			);
			if ( is_wp_error( $id ) ) {
				$map[ $name ] = array( 'error' => $id->get_error_message() );
				continue;
			}
			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
			update_post_meta( $id, '_wp_attachment_image_alt', 'Skills Online — ' . str_replace( '-', ' ', $slug ) );
			$map[ $name ] = array( 'id' => $id, 'url' => wp_get_attachment_url( $id ) );
		}
		$out['media'] = $map;
	}

	if ( 'unbuild' === $action ) {
		// Roll a page back to the theme fragment (the verified design) by removing
		// its Elementor document. Used while iterating on the native conversion.
		$slug = isset( $_GET['slug'] ) ? sanitize_title( wp_unslash( $_GET['slug'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$p    = get_page_by_path( $slug, OBJECT, array( 'page', 'post' ) );
		if ( ! $p ) {
			$out['error'] = 'no such page';
		} else {
			foreach ( array( '_elementor_edit_mode', '_elementor_data', '_elementor_template_type', '_elementor_version', '_elementor_css' ) as $key ) {
				delete_post_meta( $p->ID, $key );
			}
			$out['unbuilt'] = $slug . ' (id ' . $p->ID . ') -> renders from the theme fragment';
		}
	}

	if ( 'regen' === $action ) {
		// Elementor's per-document CSS files can go missing while the <link> tag
		// keeps being emitted (a 404 stylesheet = the per-element styles silently
		// vanish). Recreate the directory, then clear the marker meta so the next
		// render rebuilds the files.
		$up   = wp_upload_dir();
		$dir  = trailingslashit( $up['basedir'] ) . 'elementor/css';
		$out['css_dir']      = $dir;
		$out['dir_exists']   = is_dir( $dir );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
			$out['dir_created'] = is_dir( $dir );
		}
		if ( is_dir( $dir ) ) {
			@chmod( $dir, 0755 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$out['writable'] = wp_is_writable( $dir );
			$out['files']    = count( (array) glob( $dir . '/*.css' ) );
		}
		$out['print_method'] = get_option( 'elementor_css_print_method' );
		$n = 0;
		foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'publish' ) ) as $p ) {
			if ( get_post_meta( $p->ID, '_so_managed', true ) ) {
				delete_post_meta( $p->ID, '_elementor_css' );
				$n++;
			}
		}
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		$out['cleared'] = $n;
	}

	if ( 'native' === $action ) {
		// Which managed posts are real Elementor documents right now.
		$rows = array();
		foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'publish' ) ) as $p ) {
			if ( ! get_post_meta( $p->ID, '_so_managed', true ) ) {
				continue;
			}
			$data = json_decode( (string) get_post_meta( $p->ID, '_elementor_data', true ), true );
			$c = is_array( $data ) ? count( $data ) : 0;
			$types = array();
			if ( is_array( $data ) ) {
				$walk = function ( $nodes ) use ( &$walk, &$types ) {
					foreach ( $nodes as $n ) {
						$types[ $n['widgetType'] ?? $n['elType'] ] = ( $types[ $n['widgetType'] ?? $n['elType'] ] ?? 0 ) + 1;
						if ( ! empty( $n['elements'] ) ) {
							$walk( $n['elements'] );
						}
					}
				};
				$walk( $data );
			}
			$native = 0;
			foreach ( $types as $t => $cnt ) {
				if ( 0 === strpos( (string) $t, 'e-' ) ) {
					$native += $cnt;
				}
			}
			$rows[] = array( 'slug' => $p->post_name, 'id' => $p->ID, 'roots' => $c, 'native' => $native, 'html' => $types['html'] ?? 0, 'built' => 'builder' === get_post_meta( $p->ID, '_elementor_edit_mode', true ) );
		}
		$out['rows'] = $rows;
		$out['totals'] = array( 'native' => array_sum( wp_list_pluck( $rows, 'native' ) ), 'html' => array_sum( wp_list_pluck( $rows, 'html' ) ) );
	}

	if ( 'mark' === $action ) {
		// A builder can write `_elementor_data` without flagging the post as its own
		// (it does that for a post that was not previously an Elementor document).
		// Without the flag the theme renders the fragment instead, so re-assert it.
		$n = 0;
		$rows = array();
		foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'publish' ) ) as $p ) {
			if ( ! get_post_meta( $p->ID, '_so_managed', true ) ) {
				continue;
			}
			// A stale edit lock makes the builder write to a revision instead of the
			// post, so the document never lands. Clear it before anything else.
			if ( get_post_meta( $p->ID, '_edit_lock', true ) ) {
				delete_post_meta( $p->ID, '_edit_lock' );
				$out['locks_cleared'] = ( $out['locks_cleared'] ?? 0 ) + 1;
			}
			$data = json_decode( (string) get_post_meta( $p->ID, '_elementor_data', true ), true );
			if ( ! is_array( $data ) || ! $data ) {
				$rows[] = array( 'slug' => $p->post_name, 'data' => 'empty' );
				continue;
			}
			if ( 'builder' !== get_post_meta( $p->ID, '_elementor_edit_mode', true ) ) {
				// A stale edit lock makes the builder write to a revision instead of
				// the post, so the document never lands.
				delete_post_meta( $p->ID, '_edit_lock' );
				update_post_meta( $p->ID, '_elementor_edit_mode', 'builder' );
				update_post_meta( $p->ID, '_elementor_template_type', 'post' === $p->post_type ? 'wp-post' : 'wp-page' );
				$n++;
				$rows[] = array( 'slug' => $p->post_name, 'fixed' => count( $data ) . ' root elements' );
			}
		}
		$out['flagged'] = $n;
		$out['rows']    = $rows;
	}

	if ( 'raw' === $action ) {
		// Dump the raw document meta for one slug: length, flag, and the head of the
		// stored JSON, to tell "never written" apart from "written but unreadable".
		$slug = isset( $_GET['slug'] ) ? sanitize_title( wp_unslash( $_GET['slug'] ) ) : 'home'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$p    = get_page_by_path( $slug, OBJECT, array( 'page', 'post' ) );
		if ( ! $p ) {
			$out['error'] = 'no such page';
		} else {
			$raw = (string) get_post_meta( $p->ID, '_elementor_data', true );
			$out['id']         = $p->ID;
			$out['len']        = strlen( $raw );
			$out['head']       = substr( $raw, 0, 220 );
			$out['mode']       = get_post_meta( $p->ID, '_elementor_edit_mode', true );
			$out['type']       = get_post_meta( $p->ID, '_elementor_template_type', true );
			$out['version']    = get_post_meta( $p->ID, '_elementor_version', true );
			$out['json_ok']    = is_array( json_decode( $raw, true ) );
			$out['json_uns']   = is_array( json_decode( wp_unslash( $raw ), true ) );
			$out['managed']    = (bool) get_post_meta( $p->ID, '_so_managed', true );
			$out['all_meta']   = array_keys( get_post_meta( $p->ID ) );
		}
	}

	if ( 'copy' === $action ) {
		// Some posts refuse a builder write (the write lands on a revision instead of
		// the post). Build on a scratch post, then transplant the document here.
		$from = isset( $_GET['from'] ) ? (int) $_GET['from'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$to   = isset( $_GET['to'] ) ? (int) $_GET['to'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$src  = get_post( $from );
		$dst  = get_post( $to );
		if ( ! $src || ! $dst ) {
			$out['error'] = 'bad ids';
		} else {
			$raw = (string) get_post_meta( $from, '_elementor_data', true );
			if ( '' === $raw ) {
				$out['error'] = 'source has no document';
			} else {
				update_post_meta( $to, '_elementor_data', wp_slash( $raw ) );
				update_post_meta( $to, '_elementor_edit_mode', 'builder' );
				update_post_meta( $to, '_elementor_template_type', 'post' === $dst->post_type ? 'wp-post' : 'wp-page' );
				update_post_meta( $to, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '4.3.4' );
				delete_post_meta( $to, '_elementor_css' );
				$check = (string) get_post_meta( $to, '_elementor_data', true );
				$out['copied'] = strlen( $check );
				$out['json_ok'] = is_array( json_decode( $check, true ) );
				$out['roots']   = $out['json_ok'] ? count( json_decode( $check, true ) ) : 0;
			}
		}
	}

	header( 'Content-Type: application/json; charset=utf-8' );
	echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;
}
add_action( 'init', 'so_elp_router', 97 );
