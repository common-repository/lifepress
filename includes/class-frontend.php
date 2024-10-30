<?php
/** 
 * Front end
 * @version 2.1
 */

class LIFEPRESS_Front{
	public function __construct(){

		add_action( 'init', array( $this, 'init' ) ,15);
		add_filter( 'template_include', array( $this, 'template_loader' ) , 99);
	}

	public function init(){
		$this->register_styles_scripts();			
	}

	// STYLES
		function register_styles_scripts(){
								
			
			//styles
			wp_register_style('lp_main',LIFEPRESS_URL.'/assets/global.css' ,'',LIFEPRESS()->version);
			wp_register_style('spectrum',LIFEPRESS_URL.'/assets/libs/spectrum.css' );


			// trumbowyg
			wp_register_style('trumbowyg',LIFEPRESS_URL.'/assets/libs/trumbowyg/ui/trumbowyg.css' );
			wp_register_script('trumbowyg', LIFEPRESS_URL. '/assets/libs/trumbowyg/trumbowyg.min.js', array('jquery'),LIFEPRESS()->version,true );

			// scripts
			wp_register_script('fontawesome', LIFEPRESS_URL. '/assets/libs/fontawesome/all.js', array(),LIFEPRESS()->version,true );

			wp_enqueue_style("jquery-ui-css", LIFEPRESS_URL. '/assets/libs/jquery-ui/jquery-ui.css','',LIFEPRESS()->version);
			
			wp_register_style( 'google_fonts', $this->google_fonts(), array(), LIFEPRESS()->version);

			//wp_register_script('evcal_google_fonts', '//fonts.googleapis.com/css?family=Noto+Sans:400,700', '', '' , 'screen' );
			
			wp_register_script('lp_colorpicker', LIFEPRESS_URL. '/assets/libs/spectrum.js', array('jquery'),LIFEPRESS()->version,true );
			wp_register_script('main', LIFEPRESS_URL. '/assets/script.js', array('jquery', 'jquery-ui-core','jquery-ui-datepicker'),LIFEPRESS()->version,true );			
			wp_register_script('handlebars', LIFEPRESS_URL. '/assets/libs/handlebars.js', array('jquery'),LIFEPRESS()->version,true );

			wp_localize_script( 
				'main', 
				'lp_ajax', 
				array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ) , 
					'home_url'=> wp_login_url( get_permalink(get_option('lifepress_dashboard') ) ),
					'postnonce' => wp_create_nonce( 'lp_nonce' )
				)
			);
		}
		function load_styles(){
			wp_enqueue_style( 'google_fonts');
			wp_enqueue_style( 'lp_main');
			//wp_enqueue_style( 'scrollbar');
			wp_enqueue_style( 'spectrum');
			wp_enqueue_style( 'trumbowyg');

		}
		function load_scripts(){
			//wp_enqueue_script('scrollbar');
			wp_enqueue_script('fontawesome');
			wp_enqueue_script('trumbowyg');
			wp_enqueue_script('handlebars');
			wp_enqueue_script('lp_colorpicker');
			wp_enqueue_script('jquery-form');


			wp_enqueue_script('main');

			do_action('lifepress_load_scripts');
		}
		
		// google fonts
		public function google_fonts(){
			$google_fonts = apply_filters(
				'evo_google_font_families',
				array(
					'noto-sans' => 'Noto+Sans:400,400italic,700',
					//'open-sans' => 'Open+Sans:400,400italic,700',
					//'Roboto' => 'Roboto:400,700,900',
					//'league-spartan' => 'League+Spartan:400,700',
					//'monsterrat' => 'Montserrat:700,800,900',
					'poppins' => 'Poppins:700,800,900',
					//'figtree' => 'Figtree:700,800,900',
				)
			);

			$query_args = array(
				'family' => implode( '|', $google_fonts ),
				'subset' => rawurlencode( 'latin,latin-ext' ),
			);

			$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );

			return $fonts_url;
		}

	// TEMPLATE
		function template_loader($template){

			global $post;

			// get lifepress dashboard page id from saved vales
			$lifepress_dash_page_id = get_option('lifepress_dashboard');

		
			if($lifepress_dash_page_id && !empty($post) && $post->ID ==  $lifepress_dash_page_id){
				// call up lifepress scripts and styles
				$this->only_dashboard_page();

				// get lifepress template path
				$template = $this->return_dashboard_template();
			}

			return $template;
		}

		public function return_dashboard_template($atts=''){
			
			if( !empty($atts)) $this->atts = $atts;

			$file = false;

			$file = 'dashboard.php';
			$paths = array(
				0 => TEMPLATEPATH.'/' . LIFEPRESS()->template_url,				
				1 => STYLESHEETPATH .'/'. LIFEPRESS()->template_url . 'templates/',
				2 => LIFEPRESS_PATH .'/templates/',
			);

			$template = '';
			
			foreach($paths as $P){

				if(file_exists($P.$file) ){						
					$template = $P.$file;
					break;
				}
			}

			return $template;
		}

	// SUPPORT
		// lifepress page only
		function only_dashboard_page(){
			add_filter('body_class',array($this,'browser_body_class'));
			$this->load_styles();
			$this->load_scripts();
			add_filter('show_admin_bar', '__return_false');
		}
		public function browser_body_class($classes=''){	

			if(empty($classes)) $classes = array();		
			$classes[] = 'lifepress_dash';
			$classes[] = 'lifepress';
			
			return $classes;
		}
}	