<?php
/**
 *	Uninstall Lifepress
 * @version 2.1
 */

if(defined( 'WP_UNINSTALL_PLUGIN' )){

	global $wpdb, $wp_roles;

	// Delete pages
	wp_trash_post( get_option( 'life_create_pages' ) );

	// Delete options
	$wpdb->query("DELETE FROM $wpdb->options WHERE 
		option_name LIKE '%lp_%' 
		OR option_name LIKE '%lifepress_%'
		OR option_name LIKE '%_life_%'
		OR option_name LIKE '%life_%';");

	// Delete posts + data.
	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'lp_entry' );" );
	$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

	// Delete term taxonomies
	foreach ( array( 'lp_type' ) as $taxonomy ) {
		$wpdb->delete(
			$wpdb->term_taxonomy,
			array(
				'taxonomy' => $taxonomy,
			)
		);
	}

	// Delete orphan term meta
	if ( ! empty( $wpdb->termmeta ) ) {
		$wpdb->query( "DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id WHERE tt.term_id IS NULL;" );
	}

	wp_cache_flush();
}