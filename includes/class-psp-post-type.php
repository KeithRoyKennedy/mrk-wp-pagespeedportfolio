<?php
/**
 * Custom Post Type registration.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_Post_Type
 *
 * Registers the psp_site custom post type.
 */
class PSP_Post_Type {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	/**
	 * Register the psp_site custom post type.
	 */
	public static function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Sites', 'Post type general name', 'pagespeed-portfolio' ),
			'singular_name'         => _x( 'Site', 'Post type singular name', 'pagespeed-portfolio' ),
			'menu_name'             => _x( 'Sites', 'Admin Menu text', 'pagespeed-portfolio' ),
			'name_admin_bar'        => _x( 'Site', 'Add New on Toolbar', 'pagespeed-portfolio' ),
			'add_new'               => __( 'Add New', 'pagespeed-portfolio' ),
			'add_new_item'          => __( 'Add New Site', 'pagespeed-portfolio' ),
			'new_item'              => __( 'New Site', 'pagespeed-portfolio' ),
			'edit_item'             => __( 'Edit Site', 'pagespeed-portfolio' ),
			'view_item'             => __( 'View Site', 'pagespeed-portfolio' ),
			'all_items'             => __( 'All Sites', 'pagespeed-portfolio' ),
			'search_items'          => __( 'Search Sites', 'pagespeed-portfolio' ),
			'parent_item_colon'     => __( 'Parent Sites:', 'pagespeed-portfolio' ),
			'not_found'             => __( 'No sites found.', 'pagespeed-portfolio' ),
			'not_found_in_trash'    => __( 'No sites found in Trash.', 'pagespeed-portfolio' ),
			'featured_image'        => _x( 'Site Screenshot', 'Overrides the "Featured Image" phrase', 'pagespeed-portfolio' ),
			'set_featured_image'    => _x( 'Set site screenshot', 'Overrides the "Set featured image" phrase', 'pagespeed-portfolio' ),
			'remove_featured_image' => _x( 'Remove site screenshot', 'Overrides the "Remove featured image" phrase', 'pagespeed-portfolio' ),
			'use_featured_image'    => _x( 'Use as site screenshot', 'Overrides the "Use as featured image" phrase', 'pagespeed-portfolio' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-performance',
			'supports'           => array( 'title', 'thumbnail' ),
			'show_in_rest'       => false,
		);

		register_post_type( 'psp_site', $args );
	}
}
