<?php
/**
 * TEMPORARY one-off: remove the provisioning helper files this build created in
 * the WordPress root (the token-gated auto-login script and any sibling helper).
 * Hostinger's API has no file-delete operation, so the removal runs from inside
 * WordPress, where PHP already has write access.
 *
 * Usage: /?so_cleanup=<token>
 * Deleted with the rest of the temporary code before hand-over.
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Delete leftover provisioning scripts from the WordPress root.
 */
function so_cleanup_run() {
	if ( ! isset( $_GET['so_cleanup'] ) || 'so-clean-4d18ae6b92' !== sanitize_key( wp_unslash( $_GET['so_cleanup'] ) ) ) {
		return;
	}
	header( 'Content-Type: application/json; charset=utf-8' );

	$root     = rtrim( ABSPATH, '/\\' );
	$patterns = array( 'create_autologin*.php', '*autologin*.php', 'so-probe.php', 'el-probe.php' );
	$removed  = array();
	$kept     = array();

	foreach ( $patterns as $pattern ) {
		foreach ( (array) glob( $root . '/' . $pattern ) as $path ) {
			$name = basename( $path );
			if ( @unlink( $path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$removed[] = $name;
			} else {
				$kept[] = $name;
			}
		}
	}

	echo wp_json_encode(
		array(
			'removed' => $removed,
			'kept'    => $kept,
			'root'    => $root,
		)
	);
	exit;
}
add_action( 'init', 'so_cleanup_run' );
