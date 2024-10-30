<?php
/**
 * Shortcodes
 * @version 2.1
 */

class LIFEPRESS_Shortcodes{
	public function __construct(){
		add_shortcode('add_lifepress',array($this,'dashboard'));
	}

	public function dashboard($atts = ''){
		LIFEPRESS()->front->load_styles();
		LIFEPRESS()->front->load_scripts();

		ob_start();

		?><div id='lifepress_inpage'><?php

		LIFEPRESS()->temp->_dashboard_body();

		?></div><?php 

		return ob_get_clean();

	}
	
}