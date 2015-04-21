<?php
/*
Plugin Name: Google Calendar Shortcode
Description: Enables using a shortcode to display a Google Calendar
Author: Middlebury College, Eli Madden
Version: 1.0
Copyright: 2015 President and Fellows of Middlebury College
License: Gnu General Public License V3 or later (GPL v3)
*/

add_filter( 'content_save_pre', 'gcs_calendar_replace_iframe' );
function gcs_calendar_replace_iframe( $content ){
	
	$start_offset = 0; //make it possible to loop and look for multiples
	$start = strpos( $content, '<iframe src=\"https://www.google.com/calendar/embed', $start_offset );
	if( $start === FALSE ) $start = strpos( $content, '&lt;iframe src=\"https://www.google.com/calendar/embed', $start_offset );
	if( $start !== FALSE ){
		$end = strpos( $content, '</iframe>', $start_offset + $start );
		if( $end !== FALSE ) $end += 9;
		else $end = strpos( $content, '&lt;/iframe&gt;', $start_offset + $start );
		if( $end !== FALSE ) $end += 15;
	}
	if( $end ){
		$length = $end - $start;
		$target = substr( $content, $start, $length );
	}
	$target = htmlspecialchars_decode( html_entity_decode( stripslashes( $target ) ) );

	$dom = new DOMDocument();
	$dom->loadHTML( $target );
	$iframe = $dom->getElementsByTagName( 'iframe' )->item( 0 );
	$src = $iframe->getAttribute( 'src' );
	$pieces = parse_url( $src );
	parse_str( $pieces[ 'query' ], $var_array );

	//$shortcode = '[gcs_calendar ';
	//if( isset( $var_array[ 
	
	global $wpdb;
	$text = '';
	foreach( $var_array as $key => $value ){
		$text .= ' | ' . $key . ': ' . $value;
	}
	$wpdb->insert( 'debug', array( 'text' => $text ) );
	//$text = 'start: ' . $start . ' end: ' . $end . ' target: ' . $target;
	//$wpdb->insert( 'debug', array( 'text' => $text ) );

	return $content;
}

add_shortcode('gcs_calendar','gcs_calendar');
function gcs_calendar( $atts ){
	ob_start();

	print_r($atts);
	echo'<p>';
	foreach( $atts as $key => $value ){
		$atts[ strtolower( $key ) ] = trim( strip_tags( $value ) );
		echo'<strong>' .  $key . '</strong>|' . $value . '|<br />';
	}
	echo'</p>';

	$errors = array();

	$ids = $atts[ 'id' ];
	$id_array = explode( ',', $ids);
	
	$colors = $atts[ 'color' ];
	$color_array = explode( ',', $colors);

	$day_array = array( '1' => 'SUNDAY', '2' => 'MONDAY', '3' => 'TUESDAY', '4' => 'WEDNESDAY', '5' => 'THURSDAY', '6' => 'FRIDAY', '7' => 'SATURDAY' );
	$language_array = array( 'ID','CA','CS','DA','DE','EN_GB','EN','ES','ES_419','FIL','FR','HR','IT','LV','LT','HU','NL','NO','PL','PT_BR','PT_PT','RO','SK','SL','FI','SV','TR','VI','EL','RU','SR','UK','BG','IW','AR','FA','HI','TH','ZH_TW','ZH_CN','JA','KO' );
	$country_array = gcs_countries(); //list too long to put here. Returned from function below.

	echo"\n<p>*Google Calendar*</p>";
	
	$iframe = '<iframe src="https://www.google.com/calendar/embed?';
	
	if( isset( $atts[ 'title' ] ) ) $iframe .= 'title=' . $atts[ 'title' ] . '&amp;';
	if( isset( $atts[ 'show_title' ] ) ) {
		if( in_array( strtoupper( $atts[ 'show_title' ] ), array( '0', 'NO', 'FALSE' ) ) ) $iframe .= 'showTitle=0&amp;';
		else if( !in_array( strtoupper( $atts[ 'show_title' ] ), array( '1', 'YES', 'TRUE' ) ) ) $errors[] = 'Invalid value for show_title. Using default.';
	}
	if( isset( $atts[ 'show_date' ] ) ) {
		if( in_array( strtoupper( $atts[ 'show_date' ] ), array( '0', 'NO', 'FALSE' ) ) ) $iframe .= 'showDate=0&amp;';
		else if( !in_array( strtoupper( $atts[ 'show_date' ] ), array( '1', 'YES', 'TRUE' ) ) ) $errors[] = 'Invalid value for show_date. Using default.';
	}
	if( isset( $atts[ 'show_printicon' ] ) ) {
		if( in_array( strtoupper( $atts[ 'show_printicon' ] ), array( '0', 'NO', 'FALSE' ) ) ) $iframe .= 'showPrint=0&amp;';
		else if( !in_array( strtoupper( $atts[ 'show_printicon' ] ), array( '1', 'YES', 'TRUE' ) ) ) $errors[] = 'Invalid value for show_printicon. Using default.';
	}
	if( isset( $atts[ 'show_calendarlist' ] ) ) {
		if( in_array( strtoupper( $atts[ 'show_calendarlist' ] ), array( '0', 'NO', 'FALSE' ) ) ) $iframe .= 'showCalendars=0&amp;';
		else if( !in_array( strtoupper( $atts[ 'show_calendarlist' ] ), array( '1', 'YES', 'TRUE' ) ) ) $errors[] = 'Invalid value for show_calendarlist. Using default.';
	}
	if( isset( $atts[ 'show_timezone' ] ) ) {
		if( in_array( strtoupper( $atts[ 'show_timezone' ] ), array( '0', 'NO', 'FALSE' ) ) ) $iframe .= 'showTz=0&amp;';
		else if( !in_array( strtoupper( $atts[ 'show_timezone' ] ), array( '1', 'YES', 'TRUE' ) ) ) $errors[] = 'Invalid value for show_timezone. Using default.';
	}
	if( isset( $atts[ 'viewmode' ] ) ){
		//echo'<p>Viewmode before:' . $atts[ 'viewmode' ] . '</p>';
		$viewmode = strtoupper( $atts[ 'viewmode' ] );
		if( !in_array( $viewmode, array( 'WEEK', 'MONTH', 'AGENDA' ) ) ) {
			$viewmode = 'MONTH';
			$errors[] = 'Invalid viewmode. Using default.';
		}
		$iframe .= 'mode=' . $viewmode . '&amp;';
	}
	if( isset( $atts[ 'height' ] ) ) $iframe .= 'height=' . $atts[ 'height' ] . '&amp;';
	if( isset( $atts[ 'weekstart' ] ) ) {
		if( in_array( strtoupper( $atts[ 'weekstart' ] ), $day_array ) || array_key_exists( $atts[ 'weekstart' ], $day_array ) ) { 
			if( !is_numeric( $atts[ 'weekstart' ] ) ) $weekstart = array_search( strtoupper( $atts[ 'weekstart' ] ), $day_array );
			else $weekstart = $atts[ 'weekstart' ];
			$iframe .= 'wkst=' . $weekstart . '&amp;';
		} else $errors[] = 'Invalid value for weekstart. Using default.';
	}
	if( isset( $atts[ 'language' ] ) ) {
		if( in_array( strtoupper( $atts[ 'language' ] ), $language_array ) ) $iframe .= 'hl=' . $atts[ 'language' ] . '&amp;';
		else $errors[] = 'Invalid value for language. Using default.';
	}
	if( isset( $atts[ 'bgcolor' ] ) ){
		$color = trim( $atts[ 'bgcolor' ], " #\t\n\r\0\x0B" ); //strip off '#' as well as the usual space, etc
		$color = str_replace( '%23', '', $color ); //if user copy-pasted the %23 from Google, strip it off as well
		if( preg_match( '/^[a-f0-9]{3}$|^[a-f0-9]{6}$/i', $color ) ) $iframe .= 'bgcolor=%23' . $color . '&amp;'; //check to be sure it's a hex code. Otherwise don't use it and Google will default to white.
		else $errors[] = 'Invalid value for bgcolor. Using default.';
	}
	//do each of the given calendar ID(s) and match with the given color(s)
	$count = 0;
	foreach( $id_array as $id ){ 
		if( strpos( $id, 'google.com' ) > 10 ){
			if( $count ) $iframe .= '&amp;';		
			$iframe .= 'src=' . trim( $id );		
			if( isset( $color_array[ $count ] ) ) {
				$color = trim( $color_array[ $count ], " #\t\n\r\0\x0B" ); //strip off '#' as well as the usual space, etc
				$color = str_replace( '%23', '', $color ); //if user copy-pasted the %23 from Google, strip it off as well
				if( preg_match( '/^[a-f0-9]{3}$|^[a-f0-9]{6}$/i', $color ) ) $iframe .= '&amp;color=%23' . $color;
				else {
					$errors[] = 'Invalid value for color. Using default.';
					$iframe .= '&amp;color=%232952A3';
				}
			}
			$count++;
		}else $errors[] = "Invalid or missing Google Calendar ID";
	}
	if( isset( $atts[ 'timezone' ] ) ){
		$timezone = str_replace( '%2F', '/', $atts[ 'timezone' ] ); //if user copy-pasted the %2F, convert it to a / for now so we can keep the array more readable
		if( in_array( $timezone, $country_array ) ) $iframe .= '&amp;ctz=' . str_replace( '/', '%2F', $timezone );
		else $errors[] = 'Invalid Timezone';
	}
	$iframe .= '"';
	
	if( isset( $atts[ 'show_border' ] ) ) {
		if( in_array( strtoupper( $atts[ 'show_border' ] ), array( '1', 'YES', 'TRUE' ) ) ) $iframe .= ' style=" border:solid 1px #777 "';
		else if( !in_array( strtoupper( $atts[ 'show_border' ] ), array( '0', 'NO', 'FALSE' ) ) ) $errors[] = 'Invalid value for show_border. Using default.';
	}
	$iframe .= ( isset( $atts[ 'width' ] ) ) ? ' width="' . $atts[ 'width' ] . '"' : ' width="100%"';
	$iframe .= ( isset( $atts[ 'height' ] ) ) ? ' height="' . $atts[ 'height' ] . '"' : ' height="600px"';
	$iframe .= '></iframe>';
	
	echo $iframe;
	
	if( count( $errors ) ){
		echo"\n" . '<div class="error gcs_errors">' . "\n";
		echo"<small><strong>Error";
		if( count( $errors ) > 1 ) echo"s";
		echo" in calendar configuration: </strong></small>";
		if( count( $errors ) > 1 ) echo"<br />\n";
		foreach( $errors as $error) echo"<small>" . $error . "</small><br />\n";
		echo'</div><!--//.error gcs_errors-->' . "\n";
	}
	?>
	***************
	<iframe src="https://www.google.com/calendar/embed?title=*TITLE*&amp;height=500&amp;wkst=2&amp;src=9pk4g9evvbabravigk2ek4fius%40group.calendar.google.com&amp;color=%235C1158&amp;src=en.usa%23holiday%40group.v.calendar.google.com&amp;color=%23FF0000&amp;ctz=America/Los_Angeles" style=" border:solid 1px #777 " width="800" height="500" frameborder="0" scrolling="no"></iframe>
	
	<?php
	//echo'<p>*' . get_option('timezone_string') . '*</p>';
	echo'<p>Errors:' . print_r( $errors ) . '</p>';
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function gcs_countries(){
	return array(
		'Pacific/Niue',
		'Pacific/Pago_Pago',
		'Pacific/Honolulu',
		'Pacific/Rarotonga',
		'Pacific/Tahiti',
		'Pacific/Marquesas',
		'America/Anchorage',
		'Pacific/Gambier',
		'America/Los_Angeles',
		'America/Tijuana',
		'America/Vancouver',
		'America/Whitehorse',
		'Pacific/Pitcairn',
		'America/Dawson_Creek',
		'America/Denver',
		'America/Edmonton',
		'America/Hermosillo',
		'America/Mazatlan',
		'America/Phoenix',
		'America/Yellowknife',
		'America/Belize',
		'America/Chicago',
		'America/Costa_Rica',
		'America/El_Salvador',
		'America/Guatemala',
		'America/Managua',
		'America/Mexico_City',
		'America/Regina',
		'America/Tegucigalpa',
		'America/Winnipeg',
		'Pacific/Galapagos',
		'America/Bogota',
		'America/Guayaquil',
		'America/Havana',
		'America/Iqaluit',
		'America/Jamaica',
		'America/Lima',
		'America/Montreal',
		'America/Nassau',
		'America/New_York',
		'America/Panama',
		'America/Port-au-Prince',
		'America/Rio_Branco',
		'America/Toronto',
		'Pacific/Easter',
		'America/Caracas',
		'America/Asuncion',
		'America/Barbados',
		'America/Boa_Vista',
		'America/Campo_Grande',
		'America/Cuiaba',
		'America/Curacao',
		'America/Grand_Turk',
		'America/Guyana',
		'America/Halifax',
		'America/La_Paz',
		'America/Manaus',
		'America/Martinique',
		'America/Port_of_Spain',
		'America/Porto_Velho',
		'America/Puerto_Rico',
		'America/Santo_Domingo',
		'America/Thule',
		'Atlantic/Bermuda',
		'America/St_Johns',
		'America/Araguaina',
		'America/Argentina/Buenos_Aires',
		'America/Bahia',
		'America/Belem',
		'America/Cayenne',
		'America/Fortaleza',
		'America/Godthab',
		'America/Maceio',
		'America/Miquelon',
		'America/Montevideo',
		'America/Paramaribo',
		'America/Recife',
		'America/Santiago',
		'America/Sao_Paulo',
		'Antarctica/Palmer',
		'Antarctica/Rothera',
		'Atlantic/Stanley',
		'America/Noronha',
		'Atlantic/South_Georgia',
		'America/Scoresbysund',
		'Atlantic/Azores',
		'Atlantic/Cape_Verde',
		'Africa/Abidjan',
		'Africa/Accra',
		'Africa/Bissau',
		'Africa/Casablanca',
		'Africa/El_Aaiun',
		'Africa/Monrovia',
		'America/Danmarkshavn',
		'Atlantic/Canary',
		'Atlantic/Faroe',
		'Atlantic/Reykjavik',
		'Etc/GMT',
		'Europe/Dublin',
		'Europe/Lisbon'
	);
}

?>