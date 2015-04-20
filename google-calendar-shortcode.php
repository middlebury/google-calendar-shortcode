<?php
/*
Plugin Name: Google Calendar Shortcode
Description: Enables using a shortcode to display a Google Calendar
Author: Middlebury College, Eli Madden
Version: 1.0
Copyright: 2015 President and Fellows of Middlebury College
License: Gnu General Public License V3 or later (GPL v3)
*/
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

	$ids = $atts[ 'id' ];
	$id_array = explode( ',', $ids);
	
	$colors = $atts[ 'color' ];
	$color_array = explode( ',', $colors);

	$day_array = array( '1' => 'SUNDAY', '2' => 'MONDAY', '3' => 'TUESDAY', '4' => 'WEDNESDAY', '5' => 'THURSDAY', '6' => 'FRIDAY', '7' => 'SATURDAY' );
	$language_array = array( 'ID','CA','CS','DA','DE','EN_GB','EN','ES','ES_419','FIL','FR','HR','IT','LV','LT','HU','NL','NO','PL','PT_BR','PT_PT','RO','SK','SL','FI','SV','TR','VI','EL','RU','SR','UK','BG','IW','AR','FA','HI','TH','ZH_TW','ZH_CN','JA','KO' );
	
	echo"\n<p>*Google Calendar*</p>";
	
	$iframe = '<iframe src="https://www.google.com/calendar/embed?';
	
	if( isset( $atts[ 'title' ] ) ) $iframe .= 'title=' . $atts[ 'title' ] . '&amp;';
	if( isset( $atts[ 'show_title' ] ) && in_array( strtoupper( $atts[ 'show_title' ] ), array( '0', 'NO', 'FALSE' ) ) ) $iframe .= 'showTitle=0&amp;';
	if( isset( $atts[ 'show_date' ] ) && in_array( strtoupper( $atts[ 'show_date' ] ), array( '0', 'NO', 'FALSE' ) ) ) $iframe .= 'showDate=0&amp;';
	if( isset( $atts[ 'show_printicon' ] ) && in_array( strtoupper( $atts[ 'show_printicon' ] ), array( '0', 'NO', 'FALSE' ) ) ) $iframe .= 'showPrint=0&amp;';
	if( isset( $atts[ 'show_calendarlist' ] ) && in_array( strtoupper( $atts[ 'show_calendarlist' ] ), array( '0', 'NO', 'FALSE' ) ) ) $iframe .= 'showCalendars=0&amp;';
	if( isset( $atts[ 'show_timezone' ] ) && in_array( strtoupper( $atts[ 'show_timezone' ] ), array( '0', 'NO', 'FALSE' ) ) ) $iframe .= 'showTz=0&amp;';
	if( isset( $atts[ 'viewmode' ] ) ){
		//echo'<p>Viewmode before:' . $atts[ 'viewmode' ] . '</p>';
		$viewmode = strtoupper( $atts[ 'viewmode' ] );
		$viewmode = in_array( $viewmode, array( 'WEEK', 'MONTH', 'AGENDA' ) ) ? $viewmode : 'MONTH';
		//echo'<p>Viewmode after:' . $viewmode . '</p>';
		$iframe .= 'mode=' . $viewmode . '&amp;';
	}
	if( isset( $atts[ 'height' ] ) ) $iframe .= 'height=' . $atts[ 'height' ] . '&amp;';
	if( isset( $atts[ 'weekstart' ] ) && ( in_array( strtoupper( $atts[ 'weekstart' ] ), $day_array ) || array_key_exists( $atts[ 'weekstart' ], $day_array ) ) ) {
		if( !is_numeric( $atts[ 'weekstart' ] ) ) $weekstart = array_search( strtoupper( $atts[ 'weekstart' ] ), $day_array );
		else $weekstart = $atts[ 'weekstart' ];
		$iframe .= 'wkst=' . $weekstart . '&amp;';
	}
	if( isset( $atts[ 'language' ] ) && in_array( strtoupper( $atts[ 'language' ] ), $language_array ) ) $iframe .= 'hl=' . $atts[ 'language' ] . '&amp;';
	if( isset( $atts[ 'bgcolor' ] ) ){
		$color = trim( $atts[ 'bgcolor' ], " #\t\n\r\0\x0B" ); //strip off '#' as well as the usual space, etc
		$color = str_replace( '%23', '', $color ); //if user copy-pasted the %23 from Google, strip it off as well
		if( preg_match( '/^[a-f0-9]{3}$|^[a-f0-9]{6}$/i', $color ) ) $iframe .= 'bgcolor=%23' . $color . '&amp;'; //check to be sure it's a hex code. Otherwise don't use it and Google will default to white.		
	}

	$count = 0;
	foreach( $id_array as $id ){ //do each of the given calendar ID(s) and match with the given color(s)
		if( $count ) $iframe .= '&amp;';		
		$iframe .= 'src=' . trim( $id );		
		if( isset( $color_array[ $count ] ) ){
			$color = trim( $color_array[ $count ], " #\t\n\r\0\x0B" ); //strip off '#' as well as the usual space, etc
			$color = str_replace( '%23', '', $color ); //if user copy-pasted the %23 from Google, strip it off as well
			$color = ( preg_match( '/^[a-f0-9]{3}$|^[a-f0-9]{6}$/i', $color ) ) ? $color : '2952A3'; //check to be sure it's a hex code, if not use default Blue. *Note: Google at this time appears to only support color codes that match the choices they give.
			$iframe .= '&amp;color=%23' . $color;
		}
		$count++;
	}
	$iframe .= '"';

	if( isset( $atts[ 'show_border' ] ) && in_array( strtoupper( $atts[ 'show_border' ] ), array( '1', 'YES', 'TRUE' ) ) ) $iframe .= ' style=" border:solid 1px #777 "';
	if( isset( $atts[ 'width' ] ) )$iframe .= ' width="' . $atts[ 'width' ] . '"';
	if( isset( $atts[ 'height' ] ) ) $iframe .= ' height="' . $atts[ 'height' ] . '"';

	$iframe .= '></iframe>';
	echo $iframe;

	?>
	***************
	<iframe src="https://www.google.com/calendar/embed?title=*TITLE*&amp;showTz=0&amp;height=500&amp;wkst=2&amp;hl=tr&amp;bgcolor=%2355ff09&amp;src=9pk4g9evvbabravigk2ek4fius%40group.calendar.google.com&amp;color=%235C1158&amp;src=en.usa%23holiday%40group.v.calendar.google.com&amp;color=%23AB8B00&amp;ctz=America%2FNew_York" style=" border:solid 1px #777 " width="800" height="500" frameborder="0" scrolling="no"></iframe>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


?>