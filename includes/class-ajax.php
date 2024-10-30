<?php
/**
 * AJAX
 * @version 2.1
 */

class LIFEPRESS_AJAX{
	public $SOW;

	public $user_id;

	public function __construct(){

		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );

		$this->SOW =  get_option('start_of_week');

		$ajax_events = array(
			'init_load'=>'init_load',
			'load_temp_content'=>'load_temp_content',			
			'load_entries'=>'load_entries',
			'get_item_data'=>'get_item_data',
			'form_submit'=>'form_submit',
			'delete_item'=>'delete_item',
			'search_entries'=>'search_entries',
			'load_months'=>'load_months',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			$prepend = 'lp_';
			add_action( 'wp_ajax_'. $prepend . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_'. $prepend . $ajax_event, array( $this, 'no_priv' ) );
		}

		add_filter( 'heartbeat_received', array($this,'heart_beat_data'), 10, 3 );
		add_filter( 'heartbeat_nopriv_received', array($this,'heart_beat_logout'), 10, 3 );
	}

	// AJAX endpoints @since 2.1
		public static function define_ajax(){

		}

	// NO PRIV
		function no_priv(){
			echo json_encode(array(
				'status'=>'bad', 'error_msg'=>__('Login required','lp'),'error_var'=>'login_required',
			));exit;
		}
		function heart_beat_logout($response, $data, $screen_id){
			$response['result_action'] = 'logout';
			return $response;
		}
		function heart_beat_data($R, $data, $screen_id){
			return $R;
		}

	// initial load data and templates
		function init_load(){

			global $current_user;

			if(!empty($current_user)) $this->user_id = $current_user->ID;

			$J = array();

			$start_range = $end_range = 0;

			// start of week
				$J['d']['sow'] = $this->SOW;

			$sD = $J['d']['sD'] = (int)$_POST['sD'];
			$sM = $J['d']['sM'] = ((int)$_POST['sM']+1);
			$sY = $J['d']['sY'] = (int)$_POST['sY'];

			// life span view
				$lsv = LIFEPRESS()->props->get_prop('_lsview_start' ,current_time('Y-m-d'));
				$user_lsv = get_user_meta( $this->user_id,'_lifepress_ls_sd');
				if($user_lsv) $lsv = $user_lsv[0];

				$lsv = explode('-', $lsv);
				$J['d']['lsv']['y'] =  $lsv[0];
				$J['d']['lsv']['m'] =  $lsv[1];
				$J['d']['lsv']['d'] =  $lsv[2];
			
			$DD = new DateTime('now');
			$this_tz = LIFEPRESS()->time->timezone;
			$DD->setTimezone( $this_tz );
			
			$DD->setDate($sY,$sM ,1)->setTime( 0,0,0);
			$sD = $DD->format('d');

						
			$startDate = clone $DD;

			//$J['d']['debug'] = $DD->format('Y-m-d').' '. $this_tz->getOffset($DD);
		
			
			// start of grid
				$start_range = $DD->format('U');

				// how many days before first of month to load
				$pre_days = $DD->format('w') - $this->SOW;
				if( $pre_days > 0 ){
					$startDate->modify( '-'. $pre_days . 'days');
					$start_range = $startDate->format('U');
				}
			
			// end of month
				$DD->modify( 'last day of this month' )
					->setTime(23, 59, 59);

				$end_range = $DD->format('U');		


				// adjust for complete month view days from prev and next months
				$eow = ( $DD->format('w') - $this->SOW + 7) % 7;
				$post_days = 6 - $eow;

				if( $post_days > 0){
					$DD->modify('+'. $post_days .'days');
					$end_range = $DD->format('U');
				}

			
			$ENT = new LIFEPRESS_Entries();

			$J['entry'] = $ENT->get_entries( $start_range, $end_range);
			$J['temp'] = array(				
				'week_view'=>LIFEPRESS()->temp->get('week_view'),
				'month_view'=>LIFEPRESS()->temp->get('month_view'),				
				'entry_view'=>LIFEPRESS()->temp->get('entry_view'),
				'entry_form'=>LIFEPRESS()->temp->get('entry_form'),
				'tag_form'=>LIFEPRESS()->temp->get('tag_form'),
				'tags_view'=>LIFEPRESS()->temp->get('tags_view'),
				'tags_view_field'=>LIFEPRESS()->temp->get('tags_view_field'),
				'search_view'=>LIFEPRESS()->temp->get('search_view'),
				'search_res_view'=>LIFEPRESS()->temp->get('search_res_view'),
				'month_select_view'=>LIFEPRESS()->temp->get('month_select_view'),
			);

			$J['d']['start_u'] = $start_range;
			$J['d']['end_u'] = $end_range;
		
			// day names
				$J['d']['day_names'] = 
					array(
					    0=> array('f'=> __('Sunday','lp'), 's'=> __('Sun','lp') ,'i'=>0),
					    array('f'=> __('Monday','lp'), 's'=> __('Mon','lp') ,'i'=>1),
					    array('f'=> __('Tuesday','lp'), 's'=> __('Tue','lp') ,'i'=>2),
					    array('f'=> __('Wednesday','lp'), 's'=> __('Wed','lp') ,'i'=>3),
					    array('f'=> __('Thursday','lp'), 's'=> __('Thu','lp') ,'i'=>4),
					    array('f'=> __('Friday','lp'), 's'=> __('Fri','lp') ,'i'=>5),
					    array('f'=> __('Saturday','lp'), 's'=> __('Sat','lp') ,'i'=>6)
					 );

			// month names
				$J['d']['mo_names'] = array(
				    0=> __('January','lp'),
				    __('February','lp'),
				    __('March','lp'),
				    __('April','lp'),
				    __('May','lp'),
				    __('June','lp'),
				    __('July','lp'),
				    __('August','lp'),
				    __('September','lp'),
				    __('October','lp'),
				    __('November','lp'),
				    __('December','lp'),
				);

			// entry tags data				
				$ETD = new LIFEPRESS_Type_Data();
				$tags = $ETD->get_tag_json_data();
				if($tags) $J['tags'] = $tags;

			// time data for debug
				$Dx = new DateTime( 'now');
				$Dx->setTimezone( LIFEPRESS()->time->timezone );
				//$Dx->setDate();
				$Dx->setTime(0,0,01);

				$J['d']['debugs'] = $Dx;

			wp_send_json( apply_filters('lifepress_ajax_init_load', $J ) ); wp_die();
		}

		function _get_sow_daydif($today_dow){
			$dayDif = 0;
			$today_day = $today_dow;
			$start_ow = $this->SOW;
			if( $start_ow >1) $dayDif = $today_day -( $start_ow-1);
			if( $today_day > $start_ow ) $dayDif = $today_day - $start_ow;
			if( $today_day == $start_ow ) $dayDif = 0;
			if( $start_ow > $today_day) $dayDif = 7 - $start_ow;
			return $dayDif;
		}
		function _get_days_in_week_after($dow){
			$sow = $this->SOW;
			$eow = $sow +6;
			$eow = $eow > 6? $eow -7: $eow;

			$days_left = 0;

			if( $dow < $eow ) $days_left = $eow - $dow;

			return $days_left;

		}

	// load months
		public function load_months(){			

			echo json_encode(array(
				'status'=>'bad', 'error_msg'=>__('Could not create an entry','lp'),
				'notice_msg'=>__('Could not create new entry'),
				'notice_type'=>'bad',
			));exit;
		}

	// GENRAL FORM SUBMISSIONS
		function form_submit(){

			$HELP = new LIFEPRESS_Helper();
			$post = $HELP->recursive_sanitize_array_fields($_POST);

			$item_type = $post['item_type'];
			$tag_data = array();

			// ENTRY
			if( $item_type == 'entry'){

				// EDIT
				if(isset($post['entry_id']) && !empty($post['entry_id'])){
					$entry_id = (int)$post['entry_id'];

					$date = explode('-', $post['date'] );
					
					$D = new DateTime();
					$D->setTimezone( LIFEPRESS()->time->timezone );
					$D->setDate($date[0], $date[1], $date[2]);
					$D->setTime(0,0,1);
					
					$entry = array(
						'ID'=> $entry_id,
						'post_title'=> (isset($post['title'])? 
							$post['title'] : __('Entry on').' '. $post['date'] ),
						'post_content'=> (isset($post['details'])? $post['details']:''),
						'post_date'=> $D->format('Y-m-d H:i:s'),
					);
					$result = wp_update_post($entry);

					if($result){
						$EN = new LIFEPRESS_Entry( $entry_id);
						$NE_data = array();

						do_action('lifepress_entry_edited', $EN, $post);

						// save image
						if( !empty( $_FILES ) && 'POST' == $_SERVER['REQUEST_METHOD']  ){
							$this->save_featured_image($entry_id, 'lp_entry_img');
						}

						// creating a new tag
						if( isset($post['new_tag']) && !empty($post['new_tag'])){						
							$new_tag_id = $EN->set_lp_type_term( $post['new_tag']  );

							if(!is_wp_error($new_tag_id ) ){
								$C = isset($post['c'])? $post['c']:'808080';
								$C = str_replace('#', '', $C);	
								$ETD = new LIFEPRESS_Type_Data();

								$ETD->set_id($new_tag_id);
								$ETD->set_new_meta('c',$C);
								$tag_data = $ETD->get_one_tag_json_data($new_tag_id);
							}
						}else{
							$new_tag_id = $EN->set_lp_type_term( $post['tag']  );
						}
					}
					
					$NE_data = $EN->get_entry_JSON_data();
					// success temp content
					echo json_encode(array(
						'status'=>'good',
						'content'=> LIFEPRESS()->temp->get( 'update_entry_success'),
						'entry_data'=> $NE_data,
						'tag_data' => $tag_data,
						'notice_msg'=> ($post['submit_type'] == 'submit' ? 
								__('Successfully updated entry'):
								__('Successfully updated draft entry')
							),
						'notice_type'=>'good',
						'd'=> $D
					));exit;

				// NEW
				}else{
					$ENT = new LIFEPRESS_Entries();

					$date = explode('-', $post['date'] );
					
					$D = new DateTime();
					$D->setTimezone( LIFEPRESS()->time->timezone );
					$D->setDate($date[0], $date[1], $date[2]);
					$D->setTime(0,0,1);

					$entry_id = $ENT->create_new(array(
						'post_type'=>'lp_entry',
						'post_title'=> (isset($post['title'])? 
							$post['title']: __('Entry on').' '.  $post['date'] ),
						'post_status'=>'publish',
						'post_content'=> (isset($post['details'])?  $post['details'] :''),
						'date'=> $D->format('Y-m-d H:i:s'),
					));


					if($entry_id){
						
						$EN = new LIFEPRESS_Entry( $entry_id);
						$NE_data = array();		

						do_action('lifepress_entry_created', $EN, $post);		

						// save image
						if( !empty( $_FILES ) && 'POST' == $_SERVER['REQUEST_METHOD']  ){
							$this->save_featured_image($entry_id, 'lp_entry_img');
						}

						// creating a new tag
						if( isset($post['new_tag']) && !empty($post['new_tag'])){
													
							$new_tag_id = $EN->set_lp_type_term( $post['new_tag'] );

							if(!is_wp_error($new_tag_id ) ){
								$C = isset($post['c'])? $post['c']:'808080';
								$C = str_replace('#', '', $C);	

								$ETD = new LIFEPRESS_Type_Data();

								$ETD->set_id($new_tag_id);
								$ETD->set_new_meta('c',$C);
								$tag_data = $ETD->get_one_tag_json_data($new_tag_id);
							}

						}else{
							$new_tag_id = $EN->set_lp_type_term( $post['tag'] );
						}

						$NE_data = $EN->get_entry_JSON_data();

						// success temp content
						echo json_encode(array(
							'status'=>'good',
							'content'=> LIFEPRESS()->temp->get( 'new_entry_success'),
							'entry_data' => $NE_data,
							'tag_data' => $tag_data,
							'notice_msg'=> ($post['submit_type'] == 'submit' ? 
								__('Successfully created entry'):
								__('Successfully saved draft entry')
							),
							'notice_type'=>'good',
						));exit;

					}else{
						echo json_encode(array(
						'status'=>'bad', 'error_msg'=>__('Could not create an entry','lp'),
						'notice_msg'=>__('Could not create new entry'),
						'notice_type'=>'bad',
						));exit;
					}
				}

			}else{ // tag type

				if( !isset($post['tag_id'])){
					echo json_encode(array(
						'status'=>'bad', 'error_msg'=>__('Tag ID Missing','lp'),
						'notice_msg'=>__('Tag ID Missing'),
						'notice_type'=>'bad',
						));exit;
				}

				$ETD = new LIFEPRESS_Type_Data();
				$term_id = $post['tag_id'];
				$ETD->set_id($term_id);

				$exists  = $ETD->tag_exists();

				if(!$exists){					
					echo json_encode(array(
						'status'=>'bad', 'error_msg'=>__('Tag does not exists','lp'),
						'notice_msg'=>__('Tag does not exists'),
						'notice_type'=>'bad',
						));exit;

				// exists
				}else{

					$C = isset($post['c'])? $post['c']:'808080';					
					$ETD->set_new_meta('c',$C);
					if( isset($post['n']) )$ETD->update_name( $post['n']);
					
					echo json_encode(array(
						'status'=>'good', 
						'content'=> LIFEPRESS()->temp->get( 'update_tag_success'),
						'tags'=> $ETD->get_tag_json_data(),
						'error_msg'=> '',
						'notice_msg'=>__('Successfully updated tag'),
							'notice_type'=>'good',
						));exit;
				}

			}

		}

	// Save image as featured image to post
		function save_featured_image($postid, $var_name){
			if ($_FILES[$var_name]['error'] !== UPLOAD_ERR_OK) __return_false();

			if ( !function_exists('media_handle_upload') ) {
				require_once (ABSPATH.'/wp-admin/includes/media.php');
				require_once (ABSPATH.'/wp-admin/includes/file.php');
				require_once (ABSPATH.'/wp-admin/includes/image.php');	
			}

			$attachmentId = media_handle_upload($var_name, $postid);
			unset($_FILES);

			$RR = set_post_thumbnail($postid, (int)$attachmentId);

		}

	// delete item
		function delete_item(){

			$HELP = new LIFEPRESS_Helper();
			$d = isset($_POST['d']) ? $HELP->recursive_sanitize_array_fields($_POST['d']) : array();
					
			$item_id = (int)$d['item_id'];

			$EN = new LIFEPRESS_Entry($item_id);

			$res = $EN->trash_post();

			if($res){
				echo json_encode(array(
					'status'=>'good','notice_type'=>'good',
					'notice_msg'=>__('Successfully deleted','lp').' '. $d['item_type'],					
				));exit;
			}else{
				echo json_encode(array(
					'status'=>'bad', 'notice_type'=>'bad',
					'notice_msg'=>__('Could not delete','lp') .' ' .$d['item_type'],					
				));exit;
			}
		}

	// get an item data
		public function get_item_data(){
			if( !isset($_POST['item_id']) || !isset($_POST['item_type'])){
				echo json_encode(array(
					'status'=>'bad', 'notice_type'=>'bad',
					'notice_msg'=>__('Missing Required Data','lp'),					
				));exit;
			}

			$entry_id = sanitize_text_field($_POST['item_id']);
			$entry_type = sanitize_text_field($_POST['item_type']);

			if($entry_type == 'entry'){
				$ET = new LIFEPRESS_Entry($entry_id);

				echo json_encode(array(
					'status'=>'good','notice_type'=>'good',
					'item_data'=> $ET->get_entry_JSON_data()
				));exit;
			}
		}

	// LOAD all entries
		function load_entries(){

			$view = sanitize_text_field($_POST['view']);

			$sD = (int)$_POST['sD'];
			$sM = (int)$_POST['sM']+1;
			$sY = (int)$_POST['sY'];

			if($view == 'list_view'){
				$sD = 1;
			}

			$cD = new DateTime($sY.'-'.$sM.'-'.$sD);
			$cD->setTimezone( LIFEPRESS()->time->timezone );
			
			$_ee = $cD->format('Y-m');
			$S = $cD->format('U');

			// end of month value
			switch($view){
				case 'list_view':
					$cD->modify('last day of '. $_ee);			
					$E = $cD->format('U');
				break;
				case 'week_view':
					$cD->modify('+7 days');			
					$E = $cD->format('U');
				break;
				case 'month_view':
					$cD->modify('last day of this month');			
					$E = $cD->format('U');
				break;
			}
			
			//print_r(date('Y-m-d', $S));
			//print_r(date('Y-m-d', $E));
			
			$ENT = new LIFEPRESS_Entries();
			$DD = new DateTime();
			$DD->setTimestamp( ( sanitize_text_field( $_POST['start_u'] )/1000) );
			$S = $DD->format('U');
			$DD->setTimestamp( ( sanitize_text_field( $_POST['end_u'] )/1000) );
			$E = $DD->format('U');

			echo json_encode( array(
				'entries'=> $ENT->get_entries($S, $E),
				'start_u'=> $S * 1000,
				'end_u'=> $E * 1000,
			) ); exit;
		}

	// search entries
		function search_entries(){
			
			$s = sanitize_text_field($_POST['s']);
			
			$ENT = new LIFEPRESS_Entries();
			$entries = $ENT->get_entries(0,0, $s);

			echo json_encode(array(
				'entries'=>	$entries,
				'has_entries'=> count($entries)>0 ? true: false
			));exit;

		}

	// TEMPLATES
		function load_temp_content(){
			$d = $_POST['d'];

			$temp = LIFEPRESS()->temp->get($content_id);
			if(!$temp){
				echo json_encode(array(
				'status'=>'bad', 'error_msg'=>__('No Temp','lp')
				));exit;
			}

			echo json_encode(array(
				'status'=>'good',
				'content'=>$temp
			));exit;
		}
}
new LIFEPRESS_AJAX();