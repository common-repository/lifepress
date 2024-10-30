<?php
/**
 * Admin Section
 * @versino 2.1
 */

Class LIFEPRESS_Admin{

	public function __construct(){
		add_action('admin_menu', array($this,'admin_menu'), 5);
		add_action('admin_init', array($this, 'admininit'));		
	}

	public function admin_menu(){
		add_options_page(
			'LifePress Settings', 
			'LifePress Settings', 
			'manage_options', 
			'lifepress_settings', 
			array($this,'settings_page')
		);
	}

	public function admininit(){
		// User profile
		add_action('show_user_profile', array($this,'user_info'),10,1);
		add_action('edit_user_profile', array($this,'user_info'),10,1);
		add_action('profile_update', array($this,'user_save'),10,1);

		if( isset($_REQUEST['page']) && $_REQUEST['page'] == 'lifepress_settings'){
			$this->admin_styles();
		}
	}

	public function admin_styles(){
		wp_enqueue_style('admin', LIFEPRESS_URL. '/assets/admin.css','',LIFEPRESS()->version );

		do_action('lifepress_admin_styles');

	}

	public function settings_page(){

		$this->save_settings();

		?>
		<div class='wrap'>
			<h1><?php _e('LifePress Settings','lp');?> v. <?php echo LIFEPRESS()->version;?></h1>

			<?php

			// SETTINGS SAVED MESSAGE
			$updated_code = (isset($_POST['settings-updated']) && $_POST['settings-updated']=='true')? '<div class="updated fade"><p>'.__('Settings Saved','lp').'</p></div>':null;
			echo $updated_code;	

			?>

			<div id='lifepress_settings' style=''>

			<form method='post'>
				<?php settings_fields( 'lp_field_group'); ?>
				<?php wp_nonce_field( LIFEPRESS_BASENAME, 'lp_noncename' );?>
				<?php 


				$lp_url = esc_url( get_permalink(get_option('lifepress_dashboard') ) );

				echo $this->field_types_html(array(
					array(
						'type'=>'text',
						'label'=>__('Dashboard Title','lp'),
						'default'=>'Life Journal',
						'name'=>'_dash_title',
					),
					array(
						'type'=>'static_data',
						'label'=> __('LifePress dashboard page URL'),
						'content'=>  '<a href="'. $lp_url .'" target="_blank">'. $lp_url . "</a>"
					),
					array(
						'type'=>'select',
						'label'=>__('Default Dashboard View','lp'),
						'options'=> apply_filters('lifepress_settings_def_view', array(
							'week_view'=> __('Week View','lp'),
							'month_view'=> __('Month View','lp'),
						)),
						'default'=>'Week View',
						'name'=>'_dash_def_view',
					),
					array(
						'type'=>'select',
						'label'=>__('Month data loading method','lp'),
						'options'=> array(
							'prev_data'=> __('Using previously loaded data, if available','lp'),
							'fresh_data'=> __('Load fresh data at all times','lp'),
						),
						'default'=>'prev_data',
						'name'=>'_month_data_loading_method',
					),
					array(
						'type'=>'select',
						'label'=>__('Default Timezone','lp'),
						'options'=> LIFEPRESS()->help->get_timezone_array(),
						'default'=> wp_timezone_string(),
						'name'=>'_tz',
					),
					array(
						'type'=>'select',
						'label'=>__('Select the years count for past and future','lp'),
						'options'=> array(
							'5'=> 5,
							'6'=> 6,
							'7'=> 7,
							'8'=> 8,
							'9'=> 9,
							'10'=> 10,
						),
						'default'=>'5',
						'name'=>'_years_count',
					)
				));


				do_action('lifepress_admin_settings_end');

				?>

				<div class='lp_settings_row'>
					<p class='submit'>
						<input type='submit' name='<?php _e('submit','lp');?>' id='submit' class='button button-primary' value='<?php _e('Save Changes','lp');?>'/>
					</p>
				</div>

			</form>

			</div>
		</div>

		<?php
	}

	public function field_types_html($array){
		
		
		ob_start();

		foreach( $array as $field):
			extract($field);

			$value = LIFEPRESS()->props->get_prop( $name );
			if(!$value) $value = $default;

			?><div class='lp_settings_row'><?php

			switch( $type){
				case 'notice':
					?>
					<p><?php echo $content;?></p>
					<?php
				break;
				case 'static_data':
					?>
					<p>
						<label><?php echo esc_html( $label );?></label>
						<span><?php echo $content;?></span>
					</p>
					<?php
				break;
				case 'select':
					?>
					<p>
						<label><?php echo esc_html( $label );?></label>
						<select name='<?php echo $name;?>'>
							<?php 
							foreach($options as $F=>$V){
								$S = $value == $F ? 'selected="selected"':'';
								echo "<option {$S} value='". esc_html( $F )."'>". esc_html( $V )."</option>";
							}

							?>
						</select>
					</p>
					<?php
				break;
				case 'text':
					?>
					<p>
						<label><?php echo esc_html( $label );?></label>
						<input type='text' name='<?php echo $name;?>' value='<?php echo $value;?>'/>
					</p>
					<?php
				break;
				case 'number':
					?>
					<p>
						<label><?php echo esc_html( $label );?></label>
						<input type='number' step='1' min='<?php echo esc_html( $min );?>' max='<?php echo esc_html( $max );?>' name='<?php echo esc_html( $name );?>' value='<?php echo esc_html( $value );?>'/>
					</p>
					<?php
				break;
				case 'date':
					?>
					<p>
						<label><?php echo esc_html( $label );?></label>
						<input type='date' name='<?php echo esc_html( $name );?>' value='<?php echo esc_html( $value );?>'/>
					</p>
					<?php
				break;
			}

			?></div><?php

		endforeach;
		return ob_get_clean();
	}

	function save_settings(){

		if( isset($_POST['lp_noncename']) && isset( $_POST ) ){
			if ( wp_verify_nonce( $_POST['lp_noncename'], LIFEPRESS_BASENAME ) ){

				LIFEPRESS()->props->save_settings( $_POST);

				$_POST['settings-updated']='true';		
			}else{
				echo '<div class="notice error"><p>'.__('Settings not saved, nonce verification failed! Please try again later!','lp').'</p></div>';
			}
		}

	}

	// user profile
	public function user_info($user){

		$UM = get_user_meta( $user->ID);

		?>	
		<h3><?php _e('LifePress Information');?></h3>
		<table class='form-table'>
			<tr>
				<th><label><?php _e('Life Span Start Date');?></label></th>
				<td>
					<input type='date' name='_lifepress_ls_sd' value='<?php echo isset($UM['_lifepress_ls_sd'])? esc_html( $UM['_lifepress_ls_sd'][0] ):'';?>'/>
				</td>
			</tr>
			<?php do_action('lp_user_profile_info', $user);?>
		</table>
		<?php
	}

	public function user_save($user_id){
		if ( current_user_can('edit_user',$user_id) )
			update_user_meta($user_id, '_lifepress_ls_sd', sanitize_text_field($_POST['_lifepress_ls_sd']) );
	}

}

new LIFEPRESS_Admin();