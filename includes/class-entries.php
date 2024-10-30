<?php
/**
 * Entries
 */

class LIFEPRESS_Entries{

	function get_entries($start=0, $end=0, $search=''){
		$J = array();
		$A = array(
			'post_type'=>'lp_entry',
			'posts_per_page'=>-1,
		);

		// current author
		$author_id = get_current_user_id();

		$A['author'] = $author_id;

		if($start >0 && $end >0){
			$A['date_query'] = array(
				array(
					'after'=> date('Y-m-d', $start),
					'before'=> date('Y-m-d',$end),
					'inclusive'=>true
				)
			);
		}
		if(!empty($search)){
			$A['s'] = $search;
		}

		$E = new WP_Query( $A);

		if($E->have_posts()):

			$ETD = new LIFEPRESS_Type_Data();

			foreach($E->posts as $P){
				$_id = $P->ID;

				$entry = new LIFEPRESS_Entry($_id);
				$entry_json = $entry->get_entry_JSON_data($P);

				$J[$_id] =  $entry_json[$_id];
			}	
		endif;


		return $J;
	}

	function create_new($args){
		if(!empty($args) && is_array($args)){
			$valid_type = (function_exists('post_type_exists') &&  post_type_exists($args['post_type']));

			if(!$valid_type)	return false;

			$__post_content = !empty($_POST['post_content'])? 
				sanitize_text_field($_POST['post_content']): 
				(!empty($args['post_content'])?$args['post_content']:false);
			
			$__post_content = ($__post_content)?
		        	wpautop(convert_chars(stripslashes($__post_content))): 
		        	'';

		    // author id
		    $current_user = wp_get_current_user();
	        $author_id =  ( isset($current_user->ID)) ? $current_user->ID : 
	        	( !empty($args['author_id'])? $args['author_id']:1);

	        if(empty($author_id)) return false;
	        if($author_id == 0 || $author_id == '0') return false;

		    $new_post = array(
	            'post_title'   => wp_strip_all_tags($args['post_title']),
	            'post_content' => $__post_content,
	            'post_status'  => $args['post_status'],
	            'post_type'    => $args['post_type'],
	            'post_name'    => sanitize_title($args['post_title']),
	            'post_author'  => $author_id,
	            'post_date'		=> (isset($args['date'])? $args['date']:'')
	        );
		    return wp_insert_post($new_post);
		}else{
			return false;
		}
	}
}