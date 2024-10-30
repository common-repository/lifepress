<?php
/**
 * LifePress Entry object
 * @version 2.1
 */

class LIFEPRESS_Entry{
	private $pmv;
	public $meta_array_key = '_lpdata';
	public $meta_array_data = array();
	public $meta_data = array();
	public $ID, $post_thumbnail_url, $tag, $author, $post_author,$post_title, $post_type, $post_date, $post_content, $excerpt, $post_status, $time, $timeR;

	public function __construct($id){
		$this->ID = $id;		
	}

// meta data
	public function load_all_meta(){
		$this->meta_data = get_metadata('post',$this->ID);

		// also populate meta array data if present in all meta
		if( isset($this->meta_data[ $this->meta_array_key ]))
			$this->meta_array_data = maybe_unserialize( $this->meta_data[ $this->meta_array_key ] );
	}
	function get_meta($field, $force = false){
		if( !isset($this->meta_data[$field]) || $force){
			if( !is_array($this->meta_data)) $this->meta_data = array();
			$this->meta_data[ $field] = get_metadata('post',$this->ID, $field, true);	
		}

		if(empty($this->meta_data[$field])) return false;
		return maybe_unserialize($this->meta_data[$field]);
	}
	public function get_all_meta(){
		if(count($this->meta_data) == 0) $this->load_all_meta();
		return $this->meta_data;
	}
	
	function check_yn($field){
		if(empty($this->meta_data[$field])) return false;
		if($this->meta_data[$field] =='yes') return true;
		return false;
	}
	

	function set_data(){
		$this->meta_data = get_post_custom($this->ID);
	}	
	public function set_meta($key, $value, $update_meta = true){
		if( !is_array($this->meta_data)) $this->meta_data = array();
		$this->meta_data[$key] = $value;
		if($update_meta) $this->save_meta( $key, $value);
	}
	public function set_multiple_meta($array){
		if(!is_array($array)) return false;

		foreach($array as $key=>$value){
			$this->set_meta( $key, $value);
		}
	}
	public function save_meta($key, $value){
		update_metadata('post',$this->ID, $key, $value);
	}	

	public function delete_meta($field){
		delete_metadata('post',$this->ID, $field);
	}
	function set_lp_type_term($term){

		$term_o = term_exists($term, 'lp_type');
		$term_id = false;

		if(0 !== $term_o && null !== $term_o){
			$term_return = wp_set_object_terms($this->ID, (int)$term_o['term_id'], 'lp_type');
			$term_id = $term_o['term_id'];
		}else{
			$new_term = wp_insert_term($term, 'lp_type');
			$term_return = wp_set_object_terms($this->ID, (int)$new_term['term_id'], 'lp_type');
			$term_id = $new_term['term_id'];
			
		}

		return $term_id;
	}

// DB Queries
	// @version 2.1
	public function load_all_meta_query(){
		
		global $wpdb;

		// Define a cache key
			$cache_key = 'lp_entry_metas';

		// Try to get the cached results
			$metas = wp_cache_get($cache_key, 'entry_metas');

		if ($metas === false) {
			$results = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id='{$this->ID}'", ARRAY_A);	

			$metas = array();

			if($results && !is_wp_error($results)){
				foreach($results as $d){
					$metas[ $d['meta_key']] = $d['meta_value'];
				}

				// Cache the results
			    wp_cache_set($cache_key, $metas, 'entry_metas', 3600); // Cache for 1 hour
			}
		}

		$this->meta_data = $metas;
			
	}


// Meta array Data
	public function load_meta_array($force = false){
		$this->meta_array_data = $this->get_meta($this->meta_array_key, $force);
		return $this->meta_array_data? $this->meta_array_data: array();
	}

	public function get_array_meta($field){

		if( !is_array( $this->meta_array_data )) return false;
		if(count($this->meta_array_data)==0) return false;
		if(!isset($this->meta_array_data[$field])) return false;

		return $this->meta_array_data[$field];
	}
	public function set_array_meta( $field, $val , $meta_array_key='', $force = false){
		
		if(!empty($meta_array_key)) $this->meta_array_key = $meta_array_key;

		if(!is_array($this->meta_array_data)) $this->meta_array_data = array();

		$this->meta_array_data[$field] = LIFEPRESS()->help->recursive_sanitize_array_fields($val);
		$this->set_meta( $this->meta_array_key, $this->meta_array_data);

		if($force) $this->save_meta( $this->meta_array_key , $this->meta_array_data );				
	}
	public function save_array_meta($meta_array_key=''){
		if(!empty($meta_array_key)) $this->meta_array_key = $meta_array_key;
		$this->save_meta( $this->meta_array_key, $this->meta_array_data);
	}
	// DELETE
	public function delete_array_meta($field, $meta_array_key='', $force = false){
		if(!empty($meta_array_key)) $this->meta_array_key = $meta_array_key;
		
		$this->meta_array_data = $this->get_meta($this->meta_array_key, $force);

		if(!isset($this->meta_array_data[$field])) return true;

		unset($this->meta_array_data[$field]);

		$this->set_meta( $meta_array_key, $this->meta_array_data);

		if($force) $this->save_meta( $meta_array_key , $this->meta_array_data );		
	}


// GETTING
	function get_author(){
		return $this->get_prop('author');
	}
	function get_title(){
		return get_the_title($this->ID);
	}	

	function get_entry_JSON_data($post=''){

		$this->get_post($post);

		$_id = $this->ID;
		$J = array();
		
		$GMTof = get_option('gmt_offset');

		$TM = new DateTime( $this->post_date );
		$TM->setTimezone( LIFEPRESS()->time->timezone );
		
		$this_tz = LIFEPRESS()->time->timezone;
		$time_offset = ($this_tz->getOffset($TM) * -1);

		$TM->modify( '+' . $time_offset .' seconds');

		$J[$_id]['ID'] = $_id;
		$J[$_id]['details'] = $this->post_content;
		$J[$_id]['author'] = $this->post_author;
		$J[$_id]['title'] = $this->post_title;
		$J[$_id]['time'] = $TM->format('U');
		$J[$_id]['timeR'] = $TM->format('j F, Y');
		if(!empty($this->post_thumbnail_url))
			$J[$_id]['img_url'] = $this->post_thumbnail_url;

		// terms
		if($this->tag){
			$J[$_id]['tag']['id'] = $this->tag['id'];
		}

		// actions
		$J[$_id]['actions'] = array(
			array(
				'text'=>'Edit Entry',
				'd'=> array(
					'item_id'=>$_id,
					'item_type'=>'entry',
					'type'=> 'lb_edit_item',
					'temp_key'=>'entry_form',
					'form_type'=>'edit',
				)						
			),array(
				'text'=>'Delete Entry',
				'd'=> array(
					'item_id'=>$_id,
					'type'=> 'lb_delete_item',
					'item_type'=>'entry',
					'post_trigs'=>array(
						'lp_lb_close'
					)
				)
			)
		);


		return apply_filters('lifepress_entry_json', $J, $this);
	}
	function get_post($post = ''){

		if(!empty($post)){
			$post = $post;
		}else{
			global $wpdb;
			$post = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID='{$this->ID}'");
			$post =  ($post || count($post)>0) ? $post[0]: false;
		}

		if($post){
			$GMTof = get_option('gmt_offset');
			$_id = $this->ID;


			$this->author = $post->post_author;
			$this->post_author = $post->post_author;
			$this->post_title = $post->post_title;
			$this->post_type = $post->post_type;
			$this->post_date = $post->post_date;
			$this->post_content = apply_filters('the_content',$post->post_content);
			$this->excerpt = $post->post_excerpt;
			$this->post_status = $post->post_status;

			$TM = new DateTime( $this->post_date);
			$TM->setTimezone( LIFEPRESS()->time->timezone );

			
			$this->time = $TM->format('M j,Y \A\T g:iA');
			$this->time = $TM->format('U');
			$this->timeR = $TM->format('j F, Y');

			// featured image
			$post_thumbnail_id = get_post_thumbnail_id( $post );
			if($post_thumbnail_id){
				$img = wp_get_attachment_image_src($post_thumbnail_id, 'full');
				if(isset($img[0])) $this->post_thumbnail_url = $img[0];
			}

			// terms
			$T = wp_get_post_terms($_id,'lp_type');
			if($T){
				$ETD = new LIFEPRESS_Type_Data();

				$this->tag = array();
				$ETD->set_id($T[0]->term_id);
				$this->tag['id'] = $T[0]->term_id;
				$this->tag['name'] = $T[0]->name;
				$this->tag['c'] = $ETD->color();
			}
		}			
		
		return false;
	}

	public function trash_post(){
		return wp_trash_post( $this->ID);
	}
}