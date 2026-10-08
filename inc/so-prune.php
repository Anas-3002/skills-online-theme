<?php
/**
 * TEMPORARY one-off housekeeping: remove the theme source directories left behind
 * by the git deploys (wp-content/themes/skills-online-*-src). The activated theme
 * lives in its own directory and is never touched.
 *
 * Usage: /?so_prune=<token>
 * Deleted immediately after it runs.
 *
 * @package SkillsOnline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recursively delete a directory.
 *
 * @param string $path Absolute path.
 * @return bool
 */
function so_prune_rmdir( $path ) {
	if ( ! is_dir( $path ) ) {
		return false;
	}
	$items = scandir( $path );
	foreach ( (array) $items as $item ) {
		if ( '.' === $item || '..' === $item ) {
			continue;
		}
		$full = $path . DIRECTORY_SEPARATOR . $item;
		if ( is_dir( $full ) && ! is_link( $full ) ) {
			so_prune_rmdir( $full );
		} else {
			@unlink( $full ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
	}
	return @rmdir( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
}

/**
 * Run the prune.
 */
function so_prune_run() {
	if ( ! isset( $_GET['so_prune'] ) || 'so-prune-91c7f0a3d5' !== sanitize_key( wp_unslash( $_GET['so_prune'] ) ) ) {
		return;
	}
	header( 'Content-Type: application/json; charset=utf-8' );

	$active = get_option( 'stylesheet' );
	$root   = rtrim( get_theme_root(), '/\\' );
	$removed = array();
	$skipped = array();

	foreach ( (array) glob( $root . '/skills-online-*-src' ) as $dir ) {
		$name = basename( $dir );
		if ( $name === $active ) {
			$skipped[] = $name;
			continue;
		}
		$removed[] = $name . ( so_prune_rmdir( $dir ) ? ':removed' : ':failed' );
	}

	echo wp_json_encode(
		array(
			'active'  => $active,
			'removed' => $removed,
			'skipped' => $skipped,
		)
	);
	exit;
}
add_action( 'init', 'so_prune_run' );
