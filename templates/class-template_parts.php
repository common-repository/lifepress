<?php
/**
 * Template Parts
 * @version 2.1
 */

defined( 'ABSPATH' ) || exit;

if( !class_exists('LIFEPRESS_Temp')) exit;

class LIFEPRESS_Temp{

	function _dashboard_body(){
		$now = current_time('timestamp');

		$SETTINGS = new LIFEPRESS_Settings();
			$def_view = $SETTINGS->get_prop('_dash_def_view');
			$mdata_loading_m = $SETTINGS->get_prop('_month_data_loading_method');
			if( !$def_view ) $def_view = 'week_view';

		if( !is_user_logged_in()){
			echo "<p class='lp_no_access'>". __('You must login to view the LifePress Dashboard','lp');

			echo " <a href='". esc_url( wp_login_url( get_permalink() ) ) ."'>".__('Login','lp') ."</a>";

			echo "</p>";

			return;
		}

		$month_select_data = json_encode(array('type'=>'lb_temp','temp_key'=>'month_select_view'));

		?>
		<div class='lp_dashboard dfx'>
			<div class='lp_elms'>	
				<em class='lp_tooltip_box'><?php _e('Tooltip Test','lp');?></em>		
			</div>
			<div class='lp_topbar dfx'>
				<span class='title lifepress_title'><?php LIFEPRESS()->props->print_prop('_dash_title',__('Life Journal','lp'));?></span>
				<span class='top_right dfx alic'>
					<span class='date_range' data-mdld='<?php echo esc_html( $mdata_loading_m );?>'>
						<span class='lp_btn lp_btn_grey lp_goto_today'><?php _e('Today','lp');?></span>
						<span class='lp_view_change prev'><i class='fas fa-angle-left'></i></span>
						<span class='title lp_range_title lp_trig_action lpfw800'  data-t='lb' data-d='<?php echo $month_select_data;?>'><?php echo esc_html( date('F',$now).' '. date('Y',$now) );?></span>
						<span class='lp_view_change next'><i class='fas fa-angle-right'></i></span>
					</span>
					<span class='header_right dfx alic'>

						<span class='top_views dfx' data-def_view='<?php echo esc_attr( $def_view );?>'>

							<?php 
							foreach( apply_filters('lp_temp_header_views', array(
								'week'=> 'fas fa-calendar-week',
								'month'=> 'fas fa-calendar-alt',
							)) as $F=>$V){
								$FF = $def_view == $F. '_view' ? 'focus':'';
								echo "<span class='lp_view_style view ". esc_attr( $F )." ". esc_attr( $FF )."' data-t='". esc_attr( $F )."'><i class='". esc_attr( $V )."'></i></span>";
							}

							?>											
						</span>
						<span class='lp_search_trig lp_trig_action' data-t='lb' data-d='<?php echo json_encode(array('type'=>'lb_temp','temp_key'=>'search_view'));?>' data-lbc='search'><i class='fas fa-search'></i></span>
						<span class='lp_new_entry_btn lp_btn lp_trig_action lp_new_btn marl10' data-t='lb' data-d='<?php echo json_encode(array('type'=>'lb_temp','temp_key'=>'entry_form','form_type'=>'new'));?>' data-lbc='new_form'>+</span>
					</span>
				</span>
			</div>
			<div class='lp_body ' data-sow='<?php echo esc_attr( get_option('start_of_week') );?>'>
				<div class='lp_tags_out'>
					<div class='lp_tags'></div>
				</div>
				<div class='lp_body_view_content'><span class='lp_loader mart50'></span></div>				
				<div id='lp_lightboxes'></div>
				<div class='footer_notices'></div>
			</div>
		</div>
		<?php
	}


	public function month_select_view(){

		$settings = new LIFEPRESS_Settings();

		$btn_data = json_encode(array('type'=>'lb_other','action'=>'manual_set_month'));

		?>
		<div class='lp_month_select_view'>
			<?php 

			$month = date('n', LIFEPRESS()->time->current_time )-1;

			echo "<p class='months'>";
			foreach(
				array(
					0=> __('Jan','lp'),
					__('Feb','lp'),
					__('Mar','lp'),
					__('Apr','lp'),
					__('May','lp'),
					__('Jun','lp'),
					__('Jul','lp'),
					__('Aug','lp'),
					__('Sep','lp'),
					__('Oct','lp'),
					__('Nov','lp'),
					__('Dec','lp'),
				)
				as $f=>$v
			){					
				echo "<span class='lp_month_set ". esc_attr($f)." ". ( $month == $f ? 'select':'' ) ."' data-num='". esc_attr($f)."'>" . esc_html( $v ) ."</span>";
			}
			echo "</p>";

			echo "<p class='years'>";

			$years_count = (int) $settings->get_prop('_years_count', 5);
			
			$year = date('Y', LIFEPRESS()->time->current_time );
			for($x= ( $years_count * -1); $x<= $years_count; $x++){
				$year_x = $year + $x;
				echo "<span class='lp_month_set ". esc_attr($year_x)." ". ($year == $year_x? 'select':'') ."' data-num='". esc_attr($year_x) ."'>" . $year_x ."</span>";
			}
			echo "</p>";

			echo "<p><a class='lp_btn blue lp_trig_action' data-d='". $btn_data. "'>" . __('Set Month','lp') . "</a></p>";

			?>
		</div>
		<?php
	}


	// VIEWS		
		function week_view(){
			?>
			<div class='lp_week_view dfx'>
			{{#each dates}}		
				<div class='week_col {{#if today}} today{{/if}}{{#if past}} past{{/if}}'>
					<span class='day_name db w100'>{{{this_date}}}</span>
					<div class='week_entries db pad5'>
						{{#each entries}}
							<span class='lp_entry_item dfx {{#if title}}{{else}}notitle{{/if}}' data-etid='{{tag.id}}' data-id='{{@key}}' style='background-color:#{{tag.c}}'>
								<span class='lp_entry_in'><?php echo apply_filters('lifepress_temp_week_view_entry', '{{title}}','week_view');?>	
								</span>	
							</span>				
						{{/each}}
					</div>
				</div>
			{{/each}}
			</div>
			<?php
		}	

		function month_view(){


			
			?>
			<div class='lp_month_view dfx'>
			<div class='day_names'>
				{{#each day_names}}
					<p class='date_box' data-i='{{i}}'><span>{{f}}</span><em>{{s}}</em></p>
				{{/each}}
			</div>
			<div class='weeks dfx'>
				{{#each weeks}}
					<div class='week dfx'>
						{{#each days}}
						<div class='day{{#if today}} today{{/if}}{{#if prev}} prev{{/if}}{{#if past}} past{{/if}}{{#if focus_mo}} focus_mo{{/if}}{{#if next}} next{{/if}}' data-time='{{time}}'>
							<span class='day_top'>{{{this_date}}}</span>
							<span class='list_entries'>
								{{#each entries}}
									<span class='lp_entry_item dfx hOP7 curp {{#if title}}{{else}}notitle{{/if}}' data-etid='{{tag.id}}' data-id='{{@key}}' style='background-color:#{{tag.c}}'>
										<span class='lp_entry_in'><?php echo apply_filters('lifepress_temp_month_view_entry','{{title}}','month_view');?></span>	
									</span>

								{{/each}}
							</span>
						</div>
						{{/each}}
					</div>
				{{/each}}
			</div>
			
			</div>
			</div>
			<?php
		}	

		function search_res_view(){
			?>
			{{#if has_entries}}
				{{#each entries}}		
					<div class='search_entry padb10 lp_entry_item' style='background-color:#{{tag.c}}; color:#{{tag.tc}}' data-id='{{@key}}'>
						<p class='entry_date'>{{timeR}}</p>
						<h3>{{title}}</h3>
						<p>{{{details}}}</p>
					</div>
				{{/each}}
			{{else}}
				<p><?php _e('No Results Found','lp');?></p>
			{{/if}}
			<?php
		}

	function get($type, $unix=''){

		ob_start();
		switch($type){
			case has_action("LIFEPRESS_temp_{$type}"):
				do_action("LIFEPRESS_temp_{$type}");	
			break;

			case 'month_select_view':
				echo $this->month_select_view();
			break;

			case 'search_view':
				?>
				<div class='lp_search'>
					<div class='lp_search_input padt20 padb20 posr boxsbb'>
						<input class='w100 lp_search_inputfield' type='text' name='s' value='' placeholder='<?php _e('Search Entries...','lp');?>'/>
						<span class='lp_search_submit lp_btn blue posa'><?php _e('Search');?></span>
					</div>
					<div class='lp_search_results'></div>
				</div>
				<?php break;
			case 'search_res_view':
				echo $this->search_res_view();
			break;
			case 'entry_view':
				?>
				<div class='lp_lb_single_entry'>
					<h2>{{title}}</h2>
					<p class="entry_date">{{timeR}}</p>

					<?php do_action('lifepress_temp_entry_view');?>
					
					<div class='details'>{{{details}}}</div>

					{{#if img_url}}<div class='lp_entry_image'><img src="{{img_url}}"></div>{{/if}}

					<p class="entry_footer"><span class='dfx alic jutc-spacebt'>
						{{#if tag}}<span class='dfx'><b style='border-color:#{{tag.c}}'></b>{{{tag.n}}}</span>{{/if}}
						{{#if actions}}
							<span class='hidden_dropl posr'>
								<i class='fa fa-ellipsis-h'></i>
								<span class='dropd_list'>
								{{#each actions}}
									<span class='dropd_item lp_trig_action' data-d='{{toJSON d}}'>{{text}}</span>
								{{/each}}
								</span>
							</span>
						{{/if}}
					</span></p>
				</div>
				<?php
			break;	
			case 'week_view':
				echo $this->week_view();
			break;
			case 'month_view':
				echo $this->month_view();
			break;
			case 'tags_view':
				?>
				{{#if tags}}
				{{#each tags}}
					<span class='lp_side_tag select lp_tooltip' title='{{n}}' data-id='{{@key}}' style='background-color:#{{c}};'></span>
				{{/each}}
				<span class='lp_side_tag_edit'><i class='fas fa-pencil-alt'></i></span>
				{{/if}}
				<?php 
			break;
			case 'tags_view_field':
				?>
				
				<?php 
			break;
			case 'tag_form':
				?>			
				<form class='lp_form lp_tag_form'>
					<input type="hidden" name='action' value='lp_form_submit'/>
					<input type="hidden" name='form_type' value='{{form_type}}'/>
					<input type="hidden" name='item_type' value='<?php _e('tag','lp');?>'/>
					<input type="hidden" name='submit_type' value='submit'/>
					<input type="hidden" name='tag_id' value='{{item_id}}'/>

					<p class='padb10 fwb ttu fz16'><?php _e('Update Entry Tag','lp');?></p>
					
					<div class='data_row marb10'>
						<span class='lp_entry_tags'>
							
							<input class='tags' type="text" placeholder='<?php _e('Tag Name');?>' name='n' value='{{{fields.n}}}'>
							<input type="hidden" name='c' value='{{fields.c}}'/>
							<span class='tag_colors dfx padt5'>
								<?php
								$ETD = new LIFEPRESS_Type_Data();
								$Cc = array(
									'808080','AD1457', 'D81B60','D50000','E67C73',
									'F4511E','EF6C00', 'F09300','f6bf26',
									'e4c441','c0ca33', '7cb342','33b679',
									'0b8043','009688', '039be5','4285f4',
									'3f51b5','7986cb', 'b39ddb','9e69af',
									'8e24aa','795548', '616161','a79b8e',
								);

								$allC = $ETD->get_all_colors();
								if($allC) $Cc = array_merge($Cc, $allC);

								$Cc = array_unique($Cc);

								foreach($Cc as $C){
									$C = str_replace('#', '', $C );
									echo "<em class='lp_new_tag_color {{#ifCond '". esc_attr($C)."' '==' fields.c}}select{{/ifCond}}' style='background-color:#". esc_attr($C)."' data-c='". esc_attr($C)."'></em>";
								}
								?>
							</span>
							<span class='tag_color_add_new lp_btn blue'><em></em><?php _e('New Color');?></span>
						</span>
					</div>
					<p class="data_row padt10"><span class='lp_btn form_submit orange'><?php _e('Submit');?></span></p>
				</form>
				<?php
			break;
			case 'entry_form':
				?>			
				<form class='lp_form'>
					<input type="hidden" name='action' value='lp_form_submit'/>
					<input type="hidden" name='form_type' value='{{form_type}}'/>
					<input type="hidden" name='item_type' value='<?php _e('entry','lp');?>'/>
					<input type="hidden" name='submit_type' value='submit'/>
					<input type="hidden" name='entry_id' value='{{item_id}}'/>
					
					<p class='padb30 lpfw800 lpfont1 ttu lpfz30'><?php _e('Add New Entry','lp');?></p>
					
					<p class='date data_row'>
						<i class="fas fa-clock"></i>
						<input type='text' id='lp_set_date' name='date' value='{{formatDATETIME fields.time <?php echo esc_html( current_time('timestamp') );?> }}' placeholder='<?php _e('Add Date','lp');?>'/>
					</p>
					
					<p class='data_row no_icon'><input type="text" placeholder='<?php _e('Add title','lp')?>' name='title' value='{{fields.title}}'/></p>
					
					<div class='data_row details marb10 lp_toggabalables'>
						<span class='lp_form_icons lp_clickable lp_toggles' data-t='lp_editor_box'>
							<i class="fas fa-align-left"></i>
						</span>
						<span class='lp_form_field_label lp_toggles lp_clickable' data-t='lp_editor_box'><?php _e('Add description','lp');?></span>
						
						<div class='lp_editor_box ' style='display:{{#if fields.details}}block{{else}}none{{/if}}'>
							<textarea class='lp_form_details' name='details'>{{fields.details}}</textarea>
						</div>
					</div>

					
					<div class='data_row marb10'>
						<i class="fas fa-image"></i>
						<p class='w100'>
							<span class='lp_btn blue lp_select_image'><?php _e('Select Image','lp');?></span>
							<input class='lp_select_image_input' style='opacity:0;display:none' type="file" name="lp_entry_img"/>
							<?php echo wp_nonce_field( 'my_image_upload', 'my_image_upload_nonce' );?>
						</p>
					</div>
					

					<?php do_action('lifepress_entry_form_mid', $this);?>

					<p class='data_row tags'>
						<i class="fas fa-tag"></i>
						<span class='lp_entry_tags db w100'>
							<span class='selected_tag lp_toggles_dn' data-t='existing_tags' style="background-color:#{{#if fields.tag.c}}{{fields.tag.c}}{{else}}808080{{/if}}">{{#if fields.tag.n}}{{fields.tag.n}}{{else}}General{{/if}}</span>
							<input type="hidden" name='tag' value='{{#if fields.tag.n}}{{fields.tag.n}}{{else}}General{{/if}}'/>
							<input type="hidden" name='tag_id' value='{{fields.tag.id}}'/>
							<input type="hidden" name='tag_color' value='{{#if fields.tag.c}}{{fields.tag.c}}{{else}}808080{{/if}}'/>

							<span class='create_new_tag_btn lp_btn blue'><?php _e('Create New','lp');?></span>
							<?php 
								$terms = get_terms('lp_type', array('hide_empty'=>false));

								$ETD = new LIFEPRESS_Type_Data();

								if(!empty($terms) && !is_wp_error($terms)){									
									echo "<span class='existing_tags dn mart10'><span class='form_tags_in dfx'>";
									?>{{#if tags}}
									{{#each tags}}
										<i class='lp_entry_tag' data-id='{{@key}}' style='background-color:#{{c}};color:#{{tc}}' data-c='#{{c}}'>{{{n}}}</i>
									{{/each}}
									{{/if}}
									<?php echo "</span></span>";
								}
							?>
							
							<span class='lp_tag_new dn'>
								<span class='db'>
									<input class='tags' type="text" placeholder='<?php _e('Tag Name','lp');?>' name='new_tag'>
									<input type="hidden" name='c' value='808080'/>
									<span class='tag_colors dfx padt5'>
										<?php
										$Cc = array(
											'808080','AD1457', 'D81B60','D50000','E67C73',
											'F4511E','EF6C00', 'F09300','f6bf26',
											'e4c441','c0ca33', '7cb342','33b679',
											'0b8043','009688', '039be5','4285f4',
											'3f51b5','7986cb', 'b39ddb','9e69af',
											'8e24aa','795548', '616161','a79b8e',
										);

										$allC = $ETD->get_all_colors();
										if($allC) $Cc = array_merge($Cc, $allC);
										$Cc = array_unique($Cc);

										foreach($Cc as $C){
											$C = str_replace('#', '', $C);
											echo "<em class='lp_new_tag_color ". ($C=='808080'?'select':'')."' style='background-color:#". esc_html($C)."' data-c='". esc_attr($C)."'></em>";
										}

										?>
									</span>
									<span class='tag_color_add_new lp_btn blue'><em></em><?php _e('New Color','lp');?></span>
								</span>
							</span>
							
						</span>
					</p>
					<p class="data_row no_icon padt30" style="justify-content: flex-start;">
						<span class='lp_btn orange form_submit'><?php _e('Submit','lp');?></span>
						<span class='lp_btn grey form_submit save_draft'><?php _e('Save Draft','lp');?></span>
					</p>
				</form>
				<?php
			break;

			case 'new_entry_success':
				?>
				<p><b></b></p>
				<p><?php _e('Successfully Created New Entry','lp');?></p>
				<?php
			break;
			case 'update_entry_success':
				?>
				<p><b></b></p>
				<p><?php _e('Successfully Updated New Entry','lp');?></p>
				<?php
			break;
			case 'update_tag_success':
				?>
				<p><b></b></p>
				<p><?php _e('Successfully Updated Tag','lp');?></p>
				<?php
			break;
		}

		$O = ob_get_clean();
		if(empty($O)) return false;
		return $O;
	}

	// SUPPORTIVE
		
}