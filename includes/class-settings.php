<?php
/**
 * Settings
 * @version 2.1
 */

defined( 'ABSPATH' ) || exit;

if( !class_exists('LIFEPRESS_Settings')) exit;

class LIFEPRESS_Settings{
	public $props = array();
	public function __construct(){
		$this->load_props();
	}

	public function load_props(){
		$this->props = get_option('lp_settings');
		if(!$this->props) $this->props = array();
	}

	public function get_prop($field, $default = false){
		if(count($this->props)==0) return $default;
		if(!isset( $this->props[$field])) return $default;

		return $this->props[$field];
	}

	public function print_prop($field, $default){
		echo $this->get_prop($field, $default);
	}

	public function set_prop($field, $value, $force_save = false){
		$this->props[ $field ] = $value;

		if($force_save) $this->save();
	}

	public function save_settings($post){
		$new = array();
		foreach($post as $f=>$v){
			$new[$f] = addslashes(esc_html(stripslashes(($v))));
		}
		$this->props = $new;
		$this->save();
	}

	public function save(){
		update_option( 'lp_settings', $this->props);
	}
}