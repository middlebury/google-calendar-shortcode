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
		$atts[ strtolower( $key ) ] = trim( strip_tags( $value ) );
		echo'<strong>' .  $key . '</strong>|' . $value . '|<br />';
	}
	echo'</p>';

	$ids = $atts[ 'id' ];
	$id_array = explode( ',', $ids);
	
	$colors = $atts[ 'color' ];
	$color_array = explode( ',', $colors); 
	
	echo"\n<p>*Google Calendar*</p>";
	
	$iframe = '<iframe src="https://www.google.com/calendar/embed?';
	
	if( isset( $atts[ 'viewmode' ] ) ){
		//echo'<p>Viewmode before:' . $atts[ 'viewmode' ] . '</p>';
		$viewmode = strtoupper( $atts[ 'viewmode' ] );
		$viewmode = in_array( $viewmode, array( 'WEEK', 'MONTH', 'AGENDA' ) ) ? $viewmode : 'MONTH';
		//echo'<p>Viewmode after:' . $viewmode . '</p>';
		$iframe .= 'mode=' . $viewmode . '&amp;';
	}
	if( isset( $atts[ 'height' ] ) ) $iframe .= 'height=' . $atts[ 'height' ] . '&amp;';

	$count = 0;
	foreach( $id_array as $id ){
		if( $count ) $iframe .= '&amp;';
		
		//****verify proper id****
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

	if( isset( $atts[ 'width' ] ) )$iframe .= ' width="' . $atts[ 'width' ] . '"';
	if( isset( $atts[ 'height' ] ) ) $iframe .= ' height="' . $atts[ 'height' ] . '"';
	if( isset( $atts[ 'viewmode' ] ) ) $iframe .= ' height="' . $atts[ 'height' ] . '"';

	$iframe .= '></iframe>';
	echo $iframe;

	?>
	<iframe src="https://www.google.com/calendar/embed?mode=MONTH&amp;height=500&amp;wkst=1&amp;bgcolor=%23ffffff&amp;src=9pk4g9evvbabravigk2ek4fius%40group.calendar.google.com&amp;color=%235C1158&amp;src=en.usa%23holiday%40group.v.calendar.google.com&amp;color=%23AB8B00&amp;ctz=America%2FNew_York" style=" border-width:0 " width="800" height="500" frameborder="0" scrolling="no"></iframe>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


?>