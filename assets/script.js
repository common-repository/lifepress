/**
 * JS
 * @version 2.1
 */
jQuery(document).ready(function($){



DASH = false;

// currently focused items
ITEM_ID = ITEM_TYPE = false;
ITEMS = {};
var LOADED_date_range = {}; // start and end date range of entries loaded in ITEMS

EN = false; // entries


TEMP = false; // templates
DD = LIFEPRESS_DATA = false; // other data
var TAGS_DATA = {}; // tags data
_TEMP_DATA  = {} // ajax loaded temp data
VIEW = 'week_view';

// Date range start time
DS = new Date(); //  start date range 
DS.setMinutes(0);
DS.setMilliseconds(0);
DS.setSeconds(0);
DS.setHours(0);


// DATE range end 
DE = new Date(); //  start date range 
DE.setMinutes(0);
DE.setMilliseconds(0);
DE.setSeconds(0);
DE.setHours(0);

// NOW
NOW = new Date();
NOW.setMinutes(0);
NOW.setMilliseconds(0);
NOW.setSeconds(0);
NOW.setHours(0);

// FOCUS Day
FOCUS = new Date();
FOCUS.setMinutes(0);
FOCUS.setMilliseconds(0);
FOCUS.setSeconds(0);
FOCUS.setHours(0);

// Data for grid view
var GV_start_year = new Date( 1984, 6, 27);
var GV_age = 90;

TZ_adj = (NOW.getTimezoneOffset() * 60000);
 //154,759,680,0000 --php 1547596800
//1547625600  - 154762560

// UTC
UT = Date.UTC( NOW.getFullYear(), NOW.getMonth(), NOW.getDate());

BODY = $('body');
handlebar_adds();

// if on dashboad main page
if(BODY.hasClass('lifepress_dash')) DASH = true;

// if dashboard loaded witin page
if( BODY.find('#lifepress_inpage').length > 0) DASH = true;

// Heart Beat
	$( document )
		.on( 'heartbeat-tick', function( e, D ) {
			if(D.result_action == 'logout'){
				window.location = lp_ajax.home_url;
				return;
			}
		});

// FNT
	$.fn.lp_dash = function (options) {

		var el = this;
		var defaults = {
			'd':{},
			'entry':{},
			'temp':{},
			'tags':'all',
			'loaded_range_start':'',
			'loaded_range_end':'',
		};
		var dash = {};
		var LB = el.find('.lp_one_lightbox');

		// INIT
		var init = function(){
			dash.O = $.extend({},defaults, options);

			// set init start and end range
			dash.O.loaded_range_start = parseInt( dash.O.d.start_u) * 1000;
			dash.O.loaded_range_end = parseInt( dash.O.d.start_e) * 1000;

			range_title();
			interaction()

		}

		var range_title = function(){
			if(!('mo_names' in dash.O.d)) return false;

			txt = dash.O.d.mo_names[ FOCUS.getMonth() ]+' '+FOCUS.getFullYear();
			el.find('.lp_range_title').html(txt);
		}

		var interaction = function(){
			// search
			// submit search
			el.on('keypress','.lp_search_inputfield', function(event){
				if(event.keyCode == 13 || event.which == 13){
					$(this).siblings('.lp_search_submit').trigger('click');
				}
			});			
			el.on('click','.lp_search_submit',function(){
				var AJAXDATA = {}
				AJAXDATA['action'] = 'lp_search_entries';
				var inp = $(this).siblings('input');
				AJAXDATA['s'] = inp.val();

				$.ajax({
					beforeSend: function(){ LB.addClass('lp_loading'); },
					type: 'POST',url:lp_ajax.ajaxurl,data: AJAXDATA,dataType:'json',
					success:function(data){	

						var entry_data = __populate_tag_data( data);

						_html = get_temp_processed_html('search_res_view', entry_data);
						el.find('.lp_search_results').html( _html );
					},complete: function(){
						LB.removeClass('lp_loading');
						inp.blur();
					}
				});
			});

			// uncheck tags
			el.on('click','.lp_side_tag',function(){
				if( !($(this).parent().hasClass('editmode'))){
					
					$(this).toggleClass('select');

					const etid = $(this).data('id');

					if( !$(this).hasClass('select')){
						$('.lp_body_view_content').find('.lp_entry_item[data-etid="'+ etid +'"]').hide();
					}else{
						$('.lp_body_view_content').find('.lp_entry_item[data-etid="'+ etid +'"]').show();
					}
				}
				
			});

			// editting tags
			el.on('click','.lp_side_tag_edit',function(){
				var __this = $(this);
				if(__this.hasClass('select')){
					__this.removeClass('select');
					__this.parent().removeClass('editmode');
					__this.siblings().removeClass('lp_trig_action');
				}else{
					__this.addClass('select');
					__this.parent().addClass('editmode');
					__this.siblings().each(function(){
						$(this).addClass('lp_trig_action').data({
							'd':{
								'type'		:'lb_edit_item',
								'temp_key'	:'tag_form',
								'form_type'	:'edit',
								'item_id'	: $(this).data('id'),
								'item_type'	:'tag'
							}
						});
					});
				}
			});
		}

		init();
	}

// if dash load range events
	if(DASH){
		var SOW = $('.lp_body').data('sow');

		VIEW = BODY.find('.top_views').data('def_view');

		var AJAXDATA = {}
		AJAXDATA['action'] = 'lp_init_load';
		AJAXDATA['view'] = VIEW;
		AJAXDATA['sD'] = DS.getDate();
		AJAXDATA['sM'] = DS.getMonth();
		AJAXDATA['sY'] = DS.getFullYear();



		$.ajax({
			beforeSend: function(){  },
			type: 'POST',url:lp_ajax.ajaxurl,data: AJAXDATA,dataType:'json',
			success:function(data){	

				if(data.status == 'bad' && data.error_var == 'login_required'){
					window.location.replace( lp_ajax.home_url);
				}else{
					TEMP = data.temp;
					TAGS_DATA = data.tags;
					DD = data.d;


					// set loaded time range
					LOADED_date_range['s'] = parseInt(data.d.start_u)*1000;
					LOADED_date_range['e'] = parseInt(data.d.end_u)*1000;

					modify_range_start();

					// set new entries in global
					ITEMS['entry'] = {};
					ITEMS.entry = __populate_tag_data( data.entry );	

					// set life span year
						GV_start_year = new Date( data.d.lsv.y, ( parseInt(data.d.lsv.m)-1), data.d.lsv.d);
										
					_draw_entries();
					__draw_tags();

					$('body').lp_dash({
						d: data.d,
						entry: data.entry
					});
				}				

			},complete:function(){ 	}
		});
	}

// Modify start date
	// return how many days before today in the week
		function _get_sow_daydif(today){
			$dayDif = 0;
			$today_day = today;
			$start_ow = SOW;
			if( $start_ow >1) $dayDif = $today_day -( $start_ow-1);
			if( $today_day > $start_ow ) $dayDif = $today_day - $start_ow;
			if( $today_day == $start_ow ) $dayDif = 0;
			if( $start_ow > $today_day) $dayDif = 7 - $start_ow;
			return $dayDif;
		}
		function _get_eow_daydif(today){
			sow = parseInt( SOW);

			var eow = ( today - sow + 7) % 7;
			var post_days = 6 - eow;

			return ( post_days > 0) ? post_days : 0;
		}

	function modify_range_start(direction){
		if( VIEW =='list_view' || VIEW == 'month_view'){

			// adjust focus if direction send
			if(direction) FOCUS.setMonth(  (direction == 'next'? FOCUS.getMonth()+1: FOCUS.getMonth()-1) );

			// based on focus date set 1st of focus month as start range
			DS.setFullYear( FOCUS.getFullYear());
			DS.setDate(1);
			DS.setMonth( FOCUS.getMonth());

			// adjust date range for 1st day of week
			adjust_days = _get_sow_daydif( DS.getDay() );
			//DS.setTime( DS.getTime() -( adjust_days *86400000) );
			DS.setDate( DS.getDate() -  _get_sow_daydif( DS.getDay() ) );

			// set end range time based on 
			DE.setTime( FOCUS.getTime() );
			DE.setMonth( FOCUS.getMonth()+1, 0);

			// adjust end range to last day of week			
			DE.setDate( DE.getDate() + _get_eow_daydif( DE.getDay() ) );
			
		}
		if(VIEW == 'week_view' ){

			// adjust focus if direction sent
			if(direction) FOCUS.setTime(  (direction == 'next'? FOCUS.getTime()+604800000: FOCUS.getTime()-604800000) );
			
			// based on focus adjust date range to 1st day of week
			adjust_days = _get_sow_daydif( FOCUS.getDay() );
			//DS.setTime( FOCUS.getTime() -( adjust_days *86400000) );
			DS.setTime( FOCUS.getTime() );
			DS.setDate( DS.getDate() - adjust_days );
			
			// set end rage based on new start range
			DE.setTime( DS.getTime() + (86400000*6) );

		}
	}

// Supportive
	// populate tag data into JSON using tag data
		function __populate_tag_data(entry_data){
			var new_entry_data = {};
			if( 'entries' in entry_data){
				$.each(entry_data.entries, function(entry_id, entry_d){
					new_entry_data[entry_id] = entry_d;
					
					if( 'tag' in entry_d && 'id' in entry_d.tag ){
						var tag_id = entry_d.tag.id;

						if( TAGS_DATA[ tag_id ] ){
							new_entry_data[entry_id]['tag'] = TAGS_DATA[ tag_id ];
						}
					}
				});

				entry_data['entries'] = new_entry_data;
			}

			return entry_data;
		}
		function __populate_with_tag_data(one_entry_data){

			if( 'tag' in one_entry_data && 'id' in one_entry_data.tag ){
				var tag_id = one_entry_data.tag.id;

				if( TAGS_DATA[ tag_id ] ){
					one_entry_data['tag'] = TAGS_DATA[ tag_id ];
				}
			}

			return one_entry_data;
		}

	// draw focus range header title
		function mod_range_title(){
			//console.log(DS);
			//console.log(FOCUS);
			if(!('mo_names' in DD)) return false;

			// do translative month name
			txt = DD.mo_names[ FOCUS.getMonth() ]+' '+FOCUS.getFullYear();
			BODY.find('.lp_range_title').html(txt);

		}

	// draw the entries view on body
		function get_temp_processed_html(temp_type, data){
			template = Handlebars.compile( TEMP[temp_type]);
			return template( data );
		}

	// get weeks in a year FNC
		function getWeekNumber(d) {
		    // Copy date so don't modify original
		    d = new Date(+d);
		    d.setHours(0,0,0);
		    // Set to nearest Thursday: current date + 4 - current day number
		    // Make Sunday's day number 7
		    d.setDate(d.getDate() + 4 - (d.getDay()||7));
		    // Get first day of year
		    var yearStart = new Date(d.getFullYear(),0,1);
		    // Calculate full weeks to nearest Thursday
		    var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7)
		    // Return array of year and week number
		    return [d.getFullYear(), weekNo];
		}

		function weeksInYear(year) {
		  var month = 11, day = 31, week;

		  // Find week that 31 Dec is in. If is first week, reduce date until
		  // get previous week.
		  do {
		    d = new Date(year, month, day--);
		    week = getWeekNumber(d)[1];
		  } while (week == 1);

		  return week;
		}

// JS generating HTML using data
	function _draw_entries(){

		//console.log(VIEW);
			
		var tempDATA = {};
		tempDATA['dates'] = {};
		EN = ITEMS.entry;
			
		// week of events
		if(VIEW == 'week_view' || VIEW == 'list_view'){
			i = 0;
			for( var d = new Date( DS.getFullYear() , DS.getMonth() , DS.getDate()) ; d <= DE; d.setDate( d.getDate() +1) ){


				tempDATA.dates[i] = {}
				tempDATA.dates[i]['this_date'] = '<span>'+d.getDate()+' '+ DD.mo_names[ d.getMonth() ].substring(0,3) +', '
					+  DD.day_names[ d.getDay() ].s+ "</span><em>"+d.getDate()+"</em>";


				thisD_SU = Math.floor(d.getTime()/1000);
				thisD_EU = thisD_SU + 86400; // day ending unix

				//console.log(thisD_SU + ' '+thisD_EU); //1603843200 //1603782000/1603868400

				// start of week
					if(DD.sow == d.getDay()) tempDATA.dates[i]['sow'] = true ;
					if(NOW.getDate() == d.getDate() && NOW.getMonth() == d.getMonth() && NOW.getFullYear() == d.getFullYear())
					 tempDATA.dates[i]['today'] = true ;
					if(NOW.getTime() > d.getTime()) tempDATA.dates[i]['past'] = true ;
					if(FOCUS.getMonth() == d.getMonth()) tempDATA.dates[i]['focus_mo'] = true ;

				// previous and next month
					if( FOCUS.getMonth() != d.getMonth() ) {
						if( d.getMonth() > FOCUS.getMonth() )
							tempDATA.dates[i]['next'] = true ;

						if( d.getMonth() < FOCUS.getMonth() )
							tempDATA.dates[i]['prev'] = true ;
					}

				// entries	
				if(EN && typeof EN === 'object' ){

					tempDATA.dates[i]['entries'] = {};
					$.each(EN, function(ind,val){

						if( val === undefined) return;
						
						if(val.time >= thisD_SU && val.time < thisD_EU){
							tempDATA.dates[i].entries[ind] = __populate_with_tag_data(val);
						}
					});
				}
				i++
			}
		}

		// month view
		if(VIEW == 'month_view'){

			// day names
				// adjust based on sow - start of week
				var start_of_week = _z = DD.sow;
				var day_names = {};

				for(z=0; z<=6; z++){
					day_names[z]= DD.day_names[_z];
					_z++;
					_z = (_z>6)? 0: _z;
				}

				tempDATA['day_names'] = day_names;

			tempDATA['weeks'] = {};			
			box_count = 0;
			_week = 0;

			//console.log( DS);
			//console.log( DE);

			// EACH DAY
			for( var d = new Date( DS.getFullYear() , DS.getMonth() , DS.getDate()) ; 
				d.getTime() <= DE.getTime(); 
				d.setDate( d.getDate() +1) 
			){


				i = box_count;		
				
				// week
					if( box_count % 7 == 0 ){
						_week++;
						tempDATA.weeks[ _week] = {};
						tempDATA.weeks[ _week]['days'] = {};
					} 

				tempDATA.weeks[_week].days[i] = {};
				tempDATA.weeks[_week].days[i]['time'] = d.getTime()/1000;
				tempDATA.weeks[_week].days[i]['this_date'] = "<span>" + d.getDate() +"</span><em>" + d.getDate() + "</em>";

				thisD_SU = Math.floor(d.getTime()/1000);
				thisD_EU = thisD_SU + 86400; // day ending unix

				// start of week
					if(NOW.getDate() == d.getDate() && NOW.getMonth() == d.getMonth() && NOW.getFullYear() == d.getFullYear()) tempDATA.weeks[_week].days[i]['today'] = true ;
					if(NOW.getTime() > d.getTime()) tempDATA.weeks[_week].days[i]['past'] = true ;

					if(FOCUS.getMonth() == d.getMonth()) tempDATA.weeks[_week].days[i]['focus_mo'] = true ;

				// previous and next month
					if( FOCUS.getMonth() != d.getMonth() ) {
						if( d.getMonth() > FOCUS.getMonth() )
							tempDATA.weeks[_week].days[i]['next'] = true ;

						if( d.getMonth() < FOCUS.getMonth() )
							tempDATA.weeks[_week].days[i]['prev'] = true ;
					}

				// entries
				if(EN && typeof EN === 'object' ){
					tempDATA.weeks[_week].days[i]['entries'] = {};
					$.each(EN, function(ind,val){
						if( val === undefined) return;
						if(val.time >= thisD_SU && val.time < thisD_EU){						
							tempDATA.weeks[_week].days[i].entries[ind] = __populate_with_tag_data(val);
						}
					});
				}
				box_count++;
			}
				
		}

		//console.log( tempDATA);
		BODY.trigger('populate_tempDATA',[tempDATA]);

		_html = get_temp_processed_html(VIEW, tempDATA);
		
		BODY.find('.lp_body_view_content').html( _html );		
	}

	function __draw_tags(){
		var tempDATA = {};
		tempDATA['tags'] = {};
		tempDATA.tags = TAGS_DATA;

		BODY.trigger('populate_tempDATA',[tempDATA]);

		_html = get_temp_processed_html('tags_view', tempDATA);
		BODY.find('.lp_tags').html( _html );
	}

// GLOBAL item setting and getting
	BODY.on('global_items_set',function(e, items, item_id, item_type){
		ITEM_ID = item_id;
		ITEM_TYPE = item_type;
		//ITEMS[item_type] = {};
		if(items && item_type in ITEMS) ITEMS[item_type] = items;
	});

// Click interactions
	BODY
		// others
			.on('get_temp_data',function(e,data){
				var AJAXDATA = {}
				AJAXDATA['action'] = 'lp_load_temp_content';
				AJAXDATA['d'] = data;

				$.ajax({
					beforeSend: function(){  },
					type: 'POST',url:lp_ajax.ajaxurl,data: AJAXDATA,dataType:'json',
					success:function(data){		
						if(data.status == 'good'){
							_TEMP_DATA = data.content;
						}	
					},complete:function(){ 	}
				});
			})
			.on('click','.lp_trig_action',function(event, other_data){
				O = $(this);
				d = O.data('d');


				// LB content via ajax data then temp
				if(d.type == 'lb_ajax_content'){
					BODY.trigger('show_lb_base');

					// get ajax template data
					BODY.trigger('get_temp_data',[d]);

					_content = get_temp_processed_html(d.temp_key, _TEMP_DATA );
					BODY.find('.lp_lb_content').html( _content );
				}

				// LB cotent via temp
				if(d.type == 'lb_temp'){
					BODY.trigger('show_lb_base');

					// append tag data to temp data
					d['tags'] = {};
					d.tags = TAGS_DATA;
					
					//console.log(d);

					// pass on time from date box clicked
					if(d.temp_key == 'entry_form' && other_data !== undefined && 'time' in other_data){
						d['fields'] = {'time': other_data.time };
					}

					_content = get_temp_processed_html(d.temp_key, d );
					BODY.find('.lp_lb_content').html( _content );

					// for entry form process text editor
					if(d.temp_key == 'entry_form'){
						BODY.trigger('lp_populate_editors');				
					}

					BODY.trigger('after_lb_content_loaded',[O, d]);
				}

				// edit item in LB
				if(d.type == 'lb_edit_item'){

					if( d.item_type == 'entry'){
						item_data = ITEMS[d.item_type][d.item_id];
						d['fields'] = item_data;
					}

					if( d.item_type == 'tag'){
						d['fields'] = TAGS_DATA[d.item_id];

						BODY.trigger('show_lb_base');
						_content = get_temp_processed_html(d.temp_key, d );
						BODY.find('.lp_lb_content').html( _content );
					}
						
					// append tag data to temp data
					d['tags'] = {};
					d.tags = TAGS_DATA;

					console.log(d);

					BODY.trigger('populate_lb_in',[ get_temp_processed_html(d.temp_key, d ) ] );

					BODY.trigger('after_lb_content_loaded',[O, d]);	

					if( d.item_type == 'entry'){
						BODY.trigger('lp_populate_editors');
					}			
				}

				// DElete item
				if(d.type == 'lb_delete_item'){
					
					LB = O.closest('.lp_one_lightbox');

					AJ = {};
					AJ['action'] = 'lp_delete_item';
					AJ['d'] = d;

					$.ajax({
						beforeSend: function(){			LB.addClass('lp_loading');		},
						type: 'POST',url:lp_ajax.ajaxurl,data: AJ,dataType:'json',
						success:function(data){	
							if(data.status == 'good'){
								BODY.trigger('lp_lb_close');

								ITEMS[d.item_type][d.item_id] = {};
								_draw_entries();

								BODY.trigger('hide_lb',[O]);	
							}

							if('notice_msg' in data){
								BODY.trigger('show_notice',[ data.notice_msg, data.notice_type]);
							}							

						},complete:function(){	LB.removeClass('lp_loading'); BODY.trigger('hide_lb');	}
					});
				}

				// Other actions
				if(d.type == 'lb_other'){
					BODY.trigger('after_lb_content_loaded',[O, d]);
				}
			})
			
			.on('click','.lp_toggles',function(){
				item = $(this).data('t');
				BODY.find('.'+ item).toggle();
				if( $(this).hasClass('lp_form_field_label')){
					$(this).hide();
				}
			})
			.on('click','.lp_toggles_dn',function(){
				item = $(this).data('t');
				BODY.find('.'+ item).toggleClass('dn');
			})
			
			.on('click','.lp_view_style',function(){
				view_style = $(this).data('t');
				$(this).parent().find('.view').removeClass('focus');
				$(this).addClass('focus');

				VIEW = view_style+'_view';

				modify_range_start();
				_draw_entries();
			})

		// click on entry items from anywhere
			.on('click','.lp_entry_item',function(){				
			
				entry_id = $(this).data('id');

				// set global
				BODY.trigger('global_items_set',['', entry_id, 'entry']);					
				
				// if entry item data loaded locally
				if( entry_id in ITEMS.entry){
					entry_data = __populate_with_tag_data( ITEMS.entry[ entry_id] );

					BODY.trigger('show_lb_base');
					BODY.trigger('populate_lb_in',[ get_temp_processed_html('entry_view',entry_data) ] );
				}else{
					BODY.trigger('show_lb_base');

					AJ = {};
					AJ['action'] = 'lp_get_item_data';
					AJ['item_id'] = entry_id;
					AJ['item_type'] = 'entry';					

					$.ajax({
						beforeSend: function(){	},
						type: 'POST',url:lp_ajax.ajaxurl,data: AJ,dataType:'json',
						success:function(data){	
							if(data.status == 'good'){
								// add the item data to local obj
								ITEMS.entry[entry_id] = data.item_data[entry_id];

								var __content = get_temp_processed_html('entry_view',
									__populate_with_tag_data( data.item_data[entry_id] )
								);
								BODY.trigger('populate_lb_in',[ 
									__content
								] );
							}

							if('notice_msg' in data){
								BODY.trigger('show_notice',[ data.notice_msg, data.notice_type]);
							}							

						},complete:function(){	}
					});
				}					
			})

		// when navigated to new date ranges 
		.on('click','.lp_view_change',function(){
				
			// set the date range
			modify_range_start( $(this).hasClass('next')? 'next':'prev' );	

			_load_new_entry_content_after_set();				
		})

		// click on a month date box
		.on('click','.lp_month_view .day',function(event){
			event.stopImmediatePropagation();
			//console.log($(event.target).attr('class'));

			var other_data = {time: $(this).data('time')};

			
			if( ($(event.target).is('div')) )
				BODY.find('.lp_new_entry_btn').trigger('click',[ other_data ]);
		})

		// run to load date picker and editor
		.on('lp_populate_editors',function(){
			BODY.find('#lp_set_date').datepicker({
				dateFormat: 'yy-mm-dd',
			});

			$('.lp_form_details').trumbowyg({
				btns: [
			        ['viewHTML'],
			        ['undo', 'redo'], // Only supported in Blink browsers
			        //['formatting'],
			        ['strong', 'em', 'del'],
			        //['superscript', 'subscript'],
			        ['link'],
			        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
			        ['unorderedList', 'orderedList'],
			        ['removeformat'],
			        ['fullscreen']
			    ],
			    autogrow: true
			});	
		})
	;

// Image attachment
	BODY.on('change focus click','.lp_select_image_input', function(event){
		var $this = $(this),
	      	_val = $this.val(),
	      	valArray = _val.split('\\'),
	      	newVal = valArray[valArray.length-1],
	      	$button = $this.siblings('.lp_select_image'),
	     	$fakeFile = $this.siblings('.file_holder');

	  	if(newVal !== '') {
	   		var btntext = $this.attr('data-text');
	    	$button.text( btntext);
	    	if($fakeFile.length === 0) {
	    	  	$button.after('<span class="file_holder">' + newVal + '</span>');
	    	} else {
	      		$fakeFile.text(newVal);
	    	}		    	
	  	}
	});
	// run actual input field image when click on span button
		BODY.on('click','.lp_select_image',function(){
			$(this).siblings('.lp_select_image_input').click();
		});

// tooltip
	BODY.on('mouseover','.lp_tooltip',function(event){
		event.stopPropagation();

		const t = $(this).attr('title');
		var p = $(this).position();
		
		//console.log( event.offsetY +' '+ event.pageY);
		//console.log($(this).width());

		var cor = getCoords(event.target);

		$('.lp_tooltip_box').removeClass('show');
		$('.lp_tooltip_box').css({'top': (cor.top-35), 'left': ( cor.left -5 ) })
			.html(t).addClass('show');
		//$('.lp_tooltip_box').css({'top': (p.top-40), 'left': ( event.pageX -5 ) }).html(t).addClass('show');
	})
	.on('mouseout','.lp_tooltip',function(){	
		$('.lp_tooltip_box').removeClass('show');
	});

	function getCoords(elem) { // crossbrowser version
	    var box = elem.getBoundingClientRect();
	    //console.log(box);

	    var body = document.body;
	    var docEl = document.documentElement;

	    var scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
	    var scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;

	    var clientTop = docEl.clientTop || body.clientTop || 0;
	    var clientLeft = docEl.clientLeft || body.clientLeft || 0;

	    var top  = box.top +  scrollTop - clientTop;
	    var left = box.left + scrollLeft - clientLeft;

	    return { top: Math.round(top), left: Math.round(left) };
	}

// load new entry content, after parameters are changed and set
	function _load_new_entry_content_after_set(){

		var AJAXDATA = {}
		AJAXDATA['action'] = 'lp_load_entries';
		AJAXDATA['view'] = VIEW;			
		AJAXDATA['sD'] = DS.getDate();						
		AJAXDATA['sM'] = DS.getMonth();						
		AJAXDATA['sY'] = DS.getFullYear();			

		AJAXDATA['start_u'] = DS.getTime();
		AJAXDATA['end_u'] = DE.getTime();			

		// console.log( LOADED_date_range.s+' '+ AJAXDATA.start_u);
		// console.log( LOADED_date_range.e+' '+ AJAXDATA.end_u);

		// if load from prev data is enabled
		const load_from_data =  BODY.find('.date_range').data('mdld') == 'fresh_data' ? false:true;

		// check if date range is loaded
		if( load_from_data && LOADED_date_range.s <= AJAXDATA.start_u && LOADED_date_range.e >= AJAXDATA.end_u){
			_draw_entries();
			mod_range_title();
		}else{
			$.ajax({
				beforeSend: function(){ 					
					//_draw_entries(); // draw the grids before entried populated
					mod_range_title();
					BODY.find('.lp_body_view_content').addClass('lp_loading'); 
				},
				type: 'POST',url:lp_ajax.ajaxurl,data: AJAXDATA,dataType:'json',
				success:function(data){		
					
					// append new entries to gloabl entries
					$.each( data.entries, function(i,v){
						ITEMS.entry[i] = v;
					});

					// update new loaded date range
					LOADED_date_range['s'] = (LOADED_date_range.s <= data.start_u? LOADED_date_range.s :data.start_u);
					LOADED_date_range['e'] = (LOADED_date_range.e >= data.end_u? LOADED_date_range.e :data.end_u);

					_draw_entries();

				},complete:function(){ 
					BODY.find('.lp_body_view_content').removeClass('lp_loading');	
				}
			});
		}		
	}

// Lightbox
 	BODY		
		.on('show_lb_base',function(e){
			var lb_data = {};
			var LBS = $('#lp_lightboxes');

			// existing lightbox count
			lb_count = BODY.find('.lp_one_lightbox').length;
			lb_count_no = ( !lb_count || lb_count === undefined ) ? 1: lb_count+1;
			lb_class_name = 'lb'+ lb_count_no;

			// get new lightbox html
			new_lightbox_html = __get_lightbox_html( lb_count_no, lb_class_name );
			LBS.append( new_lightbox_html );

			// show the LB
			const new_one_lightbox = LBS.find('.'+ lb_class_name );
			setTimeout(function(){	new_one_lightbox.addClass('show');	},200);

			$('html').addClass('lifepress_overflow');
			BODY.addClass('hide_overflow');
			
		})
		.on('populate_lb_in',function(e, content, lb_class_name){

			var LB = BODY.find('.lp_one_lightbox:last-child');
			LB.find('.lp_lb_content').html( content);
			BODY.trigger('lifepress_lightbox_content_populated');

		})
		.on('click','.lp_lb_close',function(){
			BODY.trigger('hide_lb', [$(this) ]);
		})
		.on('hide_lb',function(ev, OBJ){

			var LB = OBJ.closest('.lp_one_lightbox');
			var LBS = $('#lp_lightboxes');
			lb_count = BODY.find('.lp_one_lightbox').length;
			LB.removeClass('show');
			setTimeout(function(){	LB.remove();	},200);

			if( lb_count == 1){
				$('html').removeClass('lifepress_overflow');
				BODY.removeClass('hide_overflow');
			}			
		})	
	;
	function __get_lightbox_html(z, lb_class_name){
		return "<div class='lp_one_lightbox "+ lb_class_name+"' style='z-index: "+z+"'><div class='lp_one_lb_in'><div class='lp_one_lb_inin'>"+
				"<a class='lp_lb_close'></a><div class='lp_lb_content lp_lb_content_"+ lb_class_name+"'><span class='lp_loader disb pad40'></span></div><div class='lp_lb_msg'></div>"+
				"</div></div></div>";
	}

// month selector
	// when lightbox loaded set current month and year as selected values
	BODY.on('after_lb_content_loaded',function(event, O, d){
		if(d.temp_key == 'month_select_view'){
			
			// set the current focus date as selector values
			const month = FOCUS.getMonth();
			const year = FOCUS.getFullYear();

			const section = BODY.find('.lp_month_select_view');

			section.find('span').removeClass('select');

			section.find('.months span.'+month).addClass('select');
			section.find('.years span.'+year).addClass('select');
		}
	});

// manual set month
	BODY.on('after_lb_content_loaded',function(event, O, d){		
		if(d.type == 'lb_other' && d.action == 'manual_set_month'){			
			const section = BODY.find('.lp_month_select_view');
			const set_month = section.find('.months span.select').data('num');
			const set_year = section.find('.years span.select').data('num');	

			// set new focus months
			FOCUS.setMonth( set_month );
			FOCUS.setFullYear( set_year );

			modify_range_start();

			_load_new_entry_content_after_set();	

			BODY.trigger('hide_lb', [O]);	
		}
	});

	// when month select items are clicked set focus 
	BODY.on('click','.lp_month_set',function(){
		const section = BODY.find('.lp_month_select_view');

		$(this).siblings('span').removeClass('select');
		$(this).addClass('select');
	});

	// go to today @2.1
	BODY.on('click','.lp_goto_today',function(){
		// set new focus months
			FOCUS.setDate( NOW.getDate() );
			FOCUS.setMonth( NOW.getMonth() );
			FOCUS.setFullYear( NOW.getFullYear() );

			modify_range_start();

			_load_new_entry_content_after_set();	
	});
		

// form interaction
	// interaction
		// color picker show
		BODY.on('after_lb_content_loaded',function(){
			BODY.on('click','.tag_color_add_new',function(){
				$(this).spectrum('show');
			});

			BODY.find('.tag_color_add_new').spectrum({
				color:"808080",
				change: function(color){
					//console.log(color);
					$(this).find('em').css('background-color', color.toHexString() );
					$(this).siblings('input[name=c]').val( color.toHexString() );
					$(this).siblings('.tag_colors').find('em').removeClass('select');
				},
				move:function(color){
					$(this).find('em').css('background-color', color.toHexString() );
				}
			});
		});

		BODY.on('click','.lp_entry_tag',function(){
			O = $(this);
			term = O.html();
			term_id = O.data('id');
			c = O.data('c');

			O.closest('form').find('input[name=tag]').val(term);
			O.closest('form').find('input[name=tag_id]').val(term_id);
			O.closest('form').find('input[name=tag_color]').val(c);
			O.closest('.lp_entry_tags').find('.selected_tag').html(term).css('background-color', c);

			O.parent().parent().addClass('dn');

		});
		BODY.on('click','.create_new_tag_btn',function(){
			O = $(this);

			// select existing
			if(O.siblings('.lp_tag_new').is(':visible')){
				O.siblings('.selected_tag').show();
				O.siblings('.lp_tag_new').addClass('dn');
			// create new
			}else{
				O.siblings('.selected_tag').hide();
				O.siblings('.existing_tags').addClass('dn');
				O.siblings('.lp_tag_new').removeClass('dn');
			}			
		})
		.on('click','.lp_new_tag_color',function(){
			O = $(this);
			O.parent().find('em').removeClass('select');
			O.addClass('select');
			O.closest('form').find('input[name=c]').val( O.data('c'));
		})		
	;

	// submission

		// save
		BODY.on('click','.form_submit',function(){
			form = $(this).closest('form');
			LB = $(this).closest('.lp_one_lightbox');
			

			var form_submit_type = $(this).hasClass('save_draft') ? 'save_draft':'submit';
			var is_saving_draft = form_submit_type == 'save_draft' ? true : false;
			form.find('input[name=submit_type]').val(form_submit_type);

			var item_type = form.find('input[name=item_type]').val();

			form.ajaxSubmit({
				beforeSend: function(){			LB.addClass('lp_loading');		},
				type: 'POST',url:lp_ajax.ajaxurl,dataType:'json',
				success:function(data){						
					if(data.status == 'good'){						

						// for tag update
						if( item_type == 'tag'){
							TAGS_DATA = data.tags;
							__draw_tags();
							BODY.find('.lp_tags').removeClass('editmode');
							_draw_entries();
						}else{
							//form.replaceWith(data.content);
						}

						if( !is_saving_draft) BODY.trigger('hide_lb',[form]);	

						if( is_saving_draft){
							var new_id = '';
							$.each(data.entry_data, function(ind, v){
								new_id = ind;
							});

							form.find('input[name=form_type]').val('edit');
							form.find('input[name=entry_id]').val( new_id );
						}

						// update tag data
						if('tag_data' in data && Object.keys(data.tag_data).length >0 ){
							TAGS_DATA[ data.tag_data.id ] = data.tag_data;
							__draw_tags();
						}

						// update entry data
						if('entry_data' in data){
							$.each(data.entry_data, function(ind, v){
								ITEMS[ item_type ][ind] = v;
							});
							_draw_entries();
						}
					}	

					if('notice_msg' in data)	
						BODY.trigger('show_notice',[ data.notice_msg, data.notice_type]);

				},complete:function(){	
					LB.removeClass('lp_loading'); 
					//BODY.trigger('hide_lb');	
				}
			});

		});

// FOOTER NOTICES
	BODY.on('show_notice',function(e, notice_msg, notice_type){		

		var num = 'fn_'+ getRandomInt(10,99);

		notice = $("<span class='"+notice_type+" "+num+"'>"+notice_msg+"</span>");
		var FN = $('.footer_notices');

		FN.append( notice );
		setTimeout(function(){
			FN.find('span.'+ num).addClass('show');
		},200);
		setTimeout(function(){
			FN.find('span.'+ num).removeClass('show');
		},4000);
		setTimeout(function(){
			FN.find('span.'+ num).remove();
		},4200);
	});

// Supportive
	function handlebar_adds(){
		Handlebars.registerHelper('ifCond',function(v1, operator, v2, options){	
			if( v1 === undefined ) return options.inverse(this);		
			v1 = v1.toLowerCase();

			return checkCondition(v1, operator, v2)
                ? options.fn(this)
                : options.inverse(this);
		});
		Handlebars.registerHelper('dateCheck',function(v1, v2,  options){
			v3 = parseInt(v2) + 86400;
			return ( v1 >= v2 && v1 < v3)? options.fn(this): options.inverse(this);
		});
		Handlebars.registerHelper('debug',function(v1,  options){
			//console.log(v1);
			return '';
		});

		Handlebars.registerHelper('toJSON', function(obj) {
		    return new Handlebars.SafeString(JSON.stringify(obj));
		});
		Handlebars.registerHelper('formatDATETIME', function(v1,v2) {
			time = v1? v1:v2;
			dt = new Date( parseInt(time) *1000 );

			var utcDS = dt.getTime() + TZ_adj;

			_d = new Date( utcDS) ;	
		
			return _d.getFullYear()+'-'+ (_d.getMonth()+1)+'-'+_d.getDate();
		});
		
	}

	function isObject(a){
		return (!!a) && (a.constructor === Object);
	}
	function isArray(a){
		 return (!!a) && (a.constructor === Array);
	}
	
	function checkCondition(v1, operator, v2) {
        switch(operator) {
            case '==':
                return (v1 == v2);
            case '===':
                return (v1 === v2);
            case '!==':
                return (v1 !== v2);
            case '<':
                return (v1 < v2);
            case '<=':
                return (v1 <= v2);
            case '>':
                return (v1 > v2);
            case '>=':
                return (v1 >= v2);
            case '&&':
                return (v1 && v2);
            case '||':
                return (v1 || v2);
            default:
                return false;
        }
    }
    function getRandomInt(min, max) {
	  min = Math.ceil(min);
	  max = Math.floor(max);
	  return Math.floor(Math.random() * (max - min) + min); //The maximum is exclusive and the minimum is inclusive
	}

});