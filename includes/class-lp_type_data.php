<?php
/**
 * Entry Type tag object
 * @version 2.1
 */

class LIFEPRESS_Type_Data{
	public $type_data= false;
	public $type_id;

	public function __construct(){
		$this->type_data = get_option('lp_type_meta');

		//print_r($this->type_data );		
	}

	// data saved
	// c - color eg. #7A7A7A

	function set_id($ID){
		$this->type_id = (int)$ID;
	}
	
	public function reload_type_data(){
		$this->type_data = get_option('lp_type_meta');
	}

	function get_all_colors(){
		$D = $this->type_data;
		if(empty($D)) return false;
		$O = array();
		foreach($D as $f=>$v){
			if(isset($v['c'])) $O[] = $v['c'];
		}
		if(count($O)== 0) return false;
		return $O;

	}
	function color(){
		$C = $this->get_meta('c');
		return !$C? 'A79B8E': str_replace('#','',$C);
	}
	function set_new_meta( $field, $value){
		if( !$this->type_data ) $this->type_data = array();
		
		$this->type_data[$this->type_id][$field] = $value;
		update_option('lp_type_meta',$this->type_data );
	}
	function get_meta($field ){
		if(!isset($this->type_data[$this->type_id])) return false;
		if(!isset($this->type_data[$this->type_id][$field])) return false;
		return $this->type_data[$this->type_id][$field];
	}

	public function update_name($new_name){
		return wp_update_term($this->type_id, 'lp_type', array(
			'name'=> $new_name
		));
	}

	public function tag_exists(){
		$term_o = term_exists($this->type_id, 'lp_type');

		return (0 !== $term_o && null !== $term_o) ? true : false;
	}

	public function get_tag_json_data(){
		$terms = get_terms('lp_type', array('hide_empty'=>false));
		if(!empty($terms) && !is_wp_error($terms)){

			$RR = array();
			foreach($terms as $term){
				$this->set_id($term->term_id);

				$RR[$term->term_id] = array(
					'n'=> $term->name,
					'c'=> $this->color(),
					'tc'=> (LIFEPRESS()->help->is_light_color($this->color())? '000000':'ffffff'),
					'id'=> $term->term_id
				);
			}
			return $RR;

		}else{ return false;}
	}

	public function get_one_tag_json_data($tag_id){
		$term = get_term( $tag_id, 'lp_type');
		if(!empty($term) && !is_wp_error($term)){

			$this->set_id($tag_id);
			return array(
				'n'=> $term->name,
				'c'=> $this->color(),
				'tc'=> (LIFEPRESS()->help->is_light_color($this->color())? '000000':'ffffff'),
				'id'=> $term->term_id
			);
		}
		return false;
	}

	
}
