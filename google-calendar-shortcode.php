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

	echo'<p>';
	foreach( $atts as $key => $value ){
		$atts[ $key ] = trim( strip_tags( $value ) );
		echo'<strong>' .  $key . '</strong>|' . $value . '|<br />';
	}
	echo'</p>';

	$ids = $atts[ 'id' ];
	$id_array = explode( ',', $ids);
	
	$colors = $atts[ 'color' ];
	$color_array = explode( ',', $colors); 
	
	echo'<p>*Google Calendar*</p>';
	
	//ob_start();

	//$code = $atts[ 'code' ];

	//$id = $atts[ 'id' ];
	//echo'<p>*' . $id . '*</p>';

	$iframe = '<iframe src="https://www.google.com/calendar/embed?';
	$count = 0;
	foreach( $id_array as $id ){
		if( $count ) $iframe .= '&amp;';
		
		//****verify proper id****
		$iframe .= 'src=' . trim( $id );
		
		$color = ( isset( $color_array[ $count ] ) ) ? $color_array[ $count ] : '2952A3'; //default color (Blue)
		//****verify proper color, strip off # ***
		$iframe .= '&amp;color=%23' . trim( $color );

		$count++;
	}
	
	$iframe .= '" width="100%" height="600px"></iframe>';
	echo $iframe;

	?>
	<!--<iframe src="https://www.google.com/calendar/embed?src=en.usa%23holiday%40group.v.calendar.google.com&amp;src=9pk4g9evvbabravigk2ek4fius%40group.calendar.google.com&amp;color=%23AB8B00" width="100%" height="600px"></iframe>-->
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


?>