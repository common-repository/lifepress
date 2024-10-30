<?php
/**
 * Time object
 * @version 2.1
 */

class LIFEPRESS_Time{
	public $GMT;
	public $timezone;
	public $timezone0, $time_format, $current_time, $date_format;

	public function __construct(){


		// get LP timezone
			$settings = new LIFEPRESS_Settings();
			$tz_string = $settings->get_prop('_tz');

			// fallback timezone from site
			if( !$tz_string ){

				// timezones
				$this->GMT = $G = get_option('gmt_offset');

				//date_default_timezone_set("UTC"); 

				$this->current_time = current_time('timestamp');
				$this->time_format = get_option('time_format');
				$this->date_format = get_option('date_format');

				$wp_tz = wp_timezone_string();

			    if( empty( $wp_tz ) ){    $wp_tz = 'UTC';    }
			    $this->timezone = new DateTimeZone( $wp_tz );
				$this->timezone0 = new DateTimeZone( 'UTC' );

			}else{
				$this->timezone = new DateTimeZone( $tz_string );

			}
	}
}