<?php
/**
 * In-memory stand-ins for the WordPress multisite, site transient, and
 * attachment functions CampusDirectoryPhotoCache uses. State lives in
 * $media_store; call reset_media_store() between cases.
 *
 * Every attachment records the blog it was written in, and every
 * get_posts()/rest_url() call records the blog it ran in, so tests can prove
 * photos land on the store site and REST URLs come from the viewing site.
 */

function reset_media_store() {
	global $media_store;
	$media_store = array(
		'multisite'       => false,
		'main_site'       => 1,
		'blog'            => 1,
		'blog_stack'      => array(),
		'switches'        => array(),
		'filters'         => array(),
		'site_transients' => array(),
		'posts'           => array(),
		'next_id'         => 100,
		'uploads'         => array(),
		'deleted'         => array(),
		'upload_error'    => false,
		'upload_throws'   => false,
		'get_posts_blogs' => array(),
		'rest_url_blogs'  => array(),
		'delete_fails'    => array(),
		'cron'            => array(),
		'cron_cleared'    => array(),
	);
}
reset_media_store();

if ( ! function_exists( 'is_multisite' ) ) {
	function is_multisite() {
		global $media_store;
		return $media_store['multisite'];
	}
}
function get_main_site_id() {
	global $media_store;
	return $media_store['main_site'];
}
function get_current_blog_id() {
	global $media_store;
	return $media_store['blog'];
}
function switch_to_blog( $id ) {
	global $media_store;
	$media_store['blog_stack'][] = $media_store['blog'];
	$media_store['switches'][]   = $id;
	$media_store['blog']         = $id;
	return true;
}
function restore_current_blog() {
	global $media_store;
	$media_store['blog'] = array_pop( $media_store['blog_stack'] );
	return true;
}
if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) {
		global $media_store;
		return isset( $media_store['filters'][ $hook ] ) ? $media_store['filters'][ $hook ]( $value ) : $value;
	}
}
function get_site_transient( $key ) {
	global $media_store;
	return isset( $media_store['site_transients'][ $key ] ) ? $media_store['site_transients'][ $key ]['value'] : false;
}
function set_site_transient( $key, $value, $expiration ) {
	global $media_store;
	$media_store['site_transients'][ $key ] = array( 'value' => $value, 'expiration' => $expiration );
	return true;
}
function delete_site_transient( $key ) {
	global $media_store;
	unset( $media_store['site_transients'][ $key ] );
	return true;
}
if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( $key ) {
		global $transients;
		unset( $transients[ $key ] );
		return true;
	}
}
if ( ! function_exists( 'rest_url' ) ) {
	function rest_url( $path = '' ) {
		global $media_store;
		$media_store['rest_url_blogs'][] = $media_store['blog'];
		return 'https://example.test/wp-json/' . ltrim( $path, '/' );
	}
}
function sanitize_file_name( $name ) {
	return preg_replace( '/[^A-Za-z0-9._-]/', '', $name );
}

function get_posts( $args ) {
	global $media_store;
	$media_store['get_posts_blogs'][] = $media_store['blog'];
	$matches = array();
	foreach ( $media_store['posts'] as $id => $post ) {
		if ( $post['blog'] !== $media_store['blog'] ) continue;
		$ok = true;
		foreach ( $args['meta_query'] ?? array() as $clause ) {
			$value = $post['meta'][ $clause['key'] ] ?? null;
			if ( 'IN' === $clause['compare'] ) {
				$ok = $ok && null !== $value && in_array( strtolower( $value ), array_map( 'strtolower', $clause['value'] ), true );
			} elseif ( '<' === $clause['compare'] ) {
				$ok = $ok && null !== $value && (int) $value < (int) $clause['value'];
			}
		}
		if ( $ok ) $matches[] = $id;
	}
	sort( $matches );
	if ( isset( $args['posts_per_page'] ) && $args['posts_per_page'] > 0 ) {
		$matches = array_slice( $matches, 0, $args['posts_per_page'] );
	}
	if ( 'ids' === ( $args['fields'] ?? '' ) ) return $matches;
	return array_map( function ( $id ) {
		return (object) array( 'ID' => $id );
	}, $matches );
}
function get_post_meta( $id, $key, $single = false ) {
	global $media_store;
	return $media_store['posts'][ $id ]['meta'][ $key ] ?? '';
}
function update_post_meta( $id, $key, $value ) {
	global $media_store;
	$media_store['posts'][ $id ]['meta'][ $key ] = $value;
	return true;
}
function wp_upload_bits( $name, $deprecated, $bits ) {
	global $media_store;
	if ( $media_store['upload_throws'] ) {
		throw new RuntimeException( 'storage unavailable' );
	}
	if ( $media_store['upload_error'] ) {
		return array( 'error' => $media_store['upload_error'] );
	}
	// Like core: upload_dir decides the folder (default YYYY/MM), and
	// wp_unique_filename() lists it unless pre_wp_unique_filename_file_list
	// hands back a list (null = it would scandir).
	$filters = $GLOBALS['upload_filters'] ?? array();
	$base    = '/uploads/site-' . $media_store['blog'];
	$dir     = array(
		'basedir' => $base,
		'baseurl' => 'https://example.test' . $base,
		'subdir'  => '/2026/11',
		'path'    => $base . '/2026/11',
		'url'     => 'https://example.test' . $base . '/2026/11',
		'error'   => false,
	);
	if ( isset( $filters['upload_dir'] ) ) $dir = call_user_func( $filters['upload_dir'], $dir );
	$file_list = isset( $filters['pre_wp_unique_filename_file_list'] )
		? call_user_func( $filters['pre_wp_unique_filename_file_list'], null, $dir['path'], $name )
		: null;
	$media_store['uploads'][] = array( 'name' => $name, 'bits' => $bits, 'blog' => $media_store['blog'], 'path' => $dir['path'], 'file_list' => $file_list );
	return array(
		'file'  => $dir['path'] . '/' . $name,
		'url'   => $dir['url'] . '/' . $name,
		'error' => false,
	);
}
function wp_insert_attachment( $args, $file, $parent = 0, $wp_error = false ) {
	global $media_store;
	$id = $media_store['next_id']++;
	$media_store['posts'][ $id ] = array(
		'blog' => $media_store['blog'],
		'args' => $args,
		'file' => $file,
		'url'  => 'https://example.test' . $file,
		'meta' => array(),
	);
	return $id;
}
function wp_update_attachment_metadata( $id, $data ) {
	global $media_store;
	$media_store['posts'][ $id ]['metadata'] = $data;
	return true;
}
function _wp_relative_upload_path( $path ) {
	return basename( $path );
}
function wp_get_attachment_url( $id ) {
	global $media_store;
	return $media_store['posts'][ $id ]['url'] ?? false;
}
function wp_delete_attachment( $id, $force = false ) {
	global $media_store;
	if ( in_array( $id, $media_store['delete_fails'], true ) ) return false;
	$media_store['deleted'][] = $id;
	unset( $media_store['posts'][ $id ] );
	return true;
}

// WP-Cron, per site.
function wp_next_scheduled( $hook ) {
	global $media_store;
	return $media_store['cron'][ $media_store['blog'] ][ $hook ]['timestamp'] ?? false;
}
function wp_schedule_event( $timestamp, $recurrence, $hook ) {
	global $media_store;
	$media_store['cron'][ $media_store['blog'] ][ $hook ] = array( 'timestamp' => $timestamp, 'recurrence' => $recurrence );
	return true;
}
function wp_clear_scheduled_hook( $hook ) {
	global $media_store;
	$media_store['cron_cleared'][] = array( 'blog' => $media_store['blog'], 'hook' => $hook );
	unset( $media_store['cron'][ $media_store['blog'] ][ $hook ] );
	return 1;
}
if ( ! function_exists( 'wp_timezone' ) ) {
	function wp_timezone() {
		return new DateTimeZone( 'America/Los_Angeles' );
	}
}

// Seed a stored photo as if an earlier render had written it.
function seed_stored_photo( $uid, $bytes, $checked, $blog = 1 ) {
	global $media_store;
	$id = $media_store['next_id']++;
	$media_store['posts'][ $id ] = array(
		'blog' => $blog,
		'args' => array(),
		'file' => '/uploads/site-' . $blog . '/cd-photo-' . $uid . '-old.jpg',
		'url'  => 'https://example.test/uploads/site-' . $blog . '/cd-photo-' . $uid . '-old.jpg',
		'meta' => array(
			CampusDirectoryPhotoCache::META_UID     => $uid,
			CampusDirectoryPhotoCache::META_HASH     => md5( $bytes ),
			CampusDirectoryPhotoCache::META_LAST_CHECKED => $checked,
		),
	);
	return $id;
}
