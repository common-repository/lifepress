<?php
/**
 * Install
 */

class LIFEPRESS_Install{

	public function __construct(){
		add_action('init', array($this,'register_post_types'));
		add_action('init', array($this,'register_tax'));
		add_action('admin_init', array($this,'create_pages'));

		add_filter('display_post_states', array($this,'post_state'),10,2);

		$this->init_caps();
	}

	// capabilities
		function init_caps(){
			global $wp_roles;

			if ( class_exists('WP_Roles') )
				if ( ! isset( $wp_roles ) )
					$wp_roles = new WP_Roles();
			
			$capabilities = $this->core_caps();
			
			foreach( $capabilities as $cap_group ) {
				foreach( $cap_group as $cap ) {
					$wp_roles->add_cap( 'administrator', $cap );
				}
			}
		}

		function core_caps(){
			$capabilities = array();

			$capabilities['core'] = apply_filters('lp_core_capabilities',array(
				"manage_lifepress"
			));
			
			$capability_types = array( 'lifepress' );

			foreach( $capability_types as $capability_type ) {

				$capabilities[ $capability_type ] = array(

					// Post type
					"publish_{$capability_type}",
					"publish_{$capability_type}s",
					"edit_{$capability_type}",
					"edit_{$capability_type}s",
					"edit_others_{$capability_type}s",
					"edit_private_{$capability_type}s",
					"edit_published_{$capability_type}s",

					"read_{$capability_type}s",
					"read_private_{$capability_type}s",
					"delete_{$capability_type}",
					"delete_{$capability_type}s",
					"delete_private_{$capability_type}s",
					"delete_published_{$capability_type}s",
					"delete_others_{$capability_type}s",					

					// Terms
					"assign_{$capability_type}_terms",
					"manage_{$capability_type}_terms",
					"edit_{$capability_type}_terms",
					"delete_{$capability_type}_terms",
					
					"upload_files"
				);
			}
			return $capabilities;
		}


	// TAX
		function register_tax(){
			$__capabilities = array(
				'manage_terms' 		=> 'manage_lifepress_terms',
				'edit_terms' 		=> 'edit_lifepress_terms',
				'delete_terms' 		=> 'delete_lifepress_terms',
				'assign_terms' 		=> 'assign_lifepress_terms',
			);

			register_taxonomy( 'lp_type', 
				apply_filters( 'lp_taxonomy_objects_lp_type', array('lp_entry') ),
				apply_filters( 'lp_taxonomy_args_lp_type', array(
					'hierarchical' 	=> false,  // this make it tags or category style
					'label' 		=> __('Entry Type','lp'), 
					'show_ui' 		=> true,
					'show_in_menu'=>true,
					'show_admin_column'=>true,
					'show_in_nav_menus'=>true,
					'query_var' 	=> true,
					'capabilities'	=> $__capabilities,
				)) 
			);
		}

	function register_post_types(){
		$labels = $this->get_proper_labels( __('LifePress Entry','lp'),__('LifePress Entries','lp'));
		register_post_type('lp_entry', 
			apply_filters( 'lp_register_post_type_lp_entry',
				array(
					'labels' => $labels,
					'description'	=> __('LifePress journal entry','lp'),
					'public' 				=> true,
					'show_ui' 				=> true,
					'capability_type' 		=> 'lifepress',
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'hierarchical' 			=> false,
					'query_var'		 		=> true,
					'supports' 				=> array('title','author','thumbnail','editor','custom-fields','page-attributes'),					
					'menu_position' 		=> 5, 
					'has_archive' 			=> true,
					'exclude_from_search'	=> true
				)
			)
		);
	}		

	// SUPPORT
		function get_proper_labels($sin, $plu){
			return array(
			'name' => _x($plu, 'post type general name' , 'lp'),
			'singular_name' => _x($sin, 'post type singular name' , 'lp'),
			'add_new' => __('Add New '. $sin , 'lp'),
			'add_new_item' => __('Add New '.$sin , 'lp'),
			'edit_item' => __('Edit '.$sin , 'lp'),
			'new_item' => __('New '.$sin , 'lp'),
			'all_items' => __('All '.$plu , 'lp'),
			'view_item' => __('View '.$sin , 'lp'),
			'search_items' => __('Search '.$plu , 'lp'),
			'not_found' =>  __('No '.$plu.' found' , 'lp'),
			'not_found_in_trash' => __('No '.$plu.' found in Trash' , 'lp'), 
			'parent_item_colon' => '',
			'menu_name' => _x($plu, 'admin menu', 'lp')
		  );
		}

		function post_state($states, $post){
			if (  'page' == get_post_type( $post->ID ) &&  $post->post_name == 'lifepress-dashboard'){
		        $states[] = __('LifePress'); 
		    } 

		    return $states;
		}

	// create pages that the plugin relies on 
		public static function create_pages(){


			global $wpdb;

			// check if page creation already done
			$created_page = get_option('lifepress_create_pages');

			//if($created_page) return;
			
			// save the page id as "lifepress_dashboard" in options
			$pages = apply_filters('lifepress_create_pages',array(
				'lifepress_dashboard' => array(
					'name'=> _x( 'LifePress Dashboard', 'page_slug', 'lp' ),
					'title'=> _x( 'LifePress Dashboard', 'lp' ),
					'content'=>'[LifePress]'
				)
			));

			foreach ( $pages as $key => $page ) {
				
				$option_value = get_option( $key );

				$slug = esc_sql( $page['name'] );
				$post_parent = 0;

				if ( $option_value > 0 && $p = get_post( $option_value ) ){
					continue;
				}

				$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug ) );
				if ( $page_found ) {
					if ( ! $option_value )	update_option( $key, $page_found );
					continue;
				}

				$page_data = array(
			        'post_status' 		=> 'publish',
			        'post_type' 		=> 'page',
			        'post_author' 		=> 1,
			        'post_name' 		=> $slug,
			        'post_title' 		=> $page['title'],
			        'post_content' 		=> $page['content'],
			        'post_parent' 		=> $post_parent,
			        'comment_status' 	=> 'closed'
			    );
			    $page_id = wp_insert_post( $page_data );
			    update_option( $key, $page_id );

			    continue;
			}

			update_option('lifepress_create_pages',true);

		}
}

new LIFEPRESS_Install();