<?php
/*
Plugin Name: Google Calendar Shortcode
Plugin URI: https://github.com/middlebury/google-calendar-shortcode
Description: Insert a Google Calendars via a shortcode; automatically convert calendar iframe embed-code to shortcode syntax.
Author: Middlebury College, Eli Madden
Author URI: https://github.com/middlebury/
Version: 1.0
Copyright: 2015 President and Fellows of Middlebury College
License: Gnu General Public License V3 or later (GPL v3)
*/
add_action( 'wp_enqueue_scripts', 'google_calendar_shortcodes_add_styles' );
function google_calendar_shortcodes_add_styles(){
	global $wp_styles;
	wp_add_inline_style( $wp_styles->queue[0], 'div.google-calendar-shortcode-errors strong{ color: red }' );
}

add_filter( 'content_save_pre', 'google_calendar_shortcode_replace_iframe' );
function google_calendar_shortcode_replace_iframe( $content ) {
	preg_match_all('#(?:<|&lt;)iframe src=\\\"https?://www.google.com/calendar/.+(?:></iframe>|&gt;&lt;/iframe&gt;)#U', $content, $matches);
	foreach ( $matches[0] as $match ) {
		$html = html_entity_decode( stripslashes( $match ) );
		$dom = new DOMDocument();
		$dom->loadHTML( $html );
		$iframe = $dom->getElementsByTagName( 'iframe' )->item( 0 );
		$src = $iframe->getAttribute( 'src' );
		$pieces = parse_url( $src );
		parse_str( $pieces['query'], $var_array ); //this will overwrite multiple src and color, so do those next
		$parts = explode( '&' , $pieces['query'] );
		$sources = array();
		foreach ( $parts as $part ) {
			if( strpos( $part, 'src=' ) === 0 ) {
				$sources[] = substr( $part, 4 );
			}
		}
		$colors = array();
		foreach ( $parts as $part ) {
			if( strpos( $part, 'color=' ) === 0 ) {
				$colors[] = substr( $part, 6 );
			}
		}
		foreach ( $colors as $key => $value ) {
			if( strpos( $value, '%23' ) === 0 ) {
				$colors[ $key ] = substr( $value, 3 );
			}
		}

		$shortcode = '[google_calendar';
		if ( count( $sources ) > 0 ) {
			$shortcode .= ' id="';
			$count = 0;
			foreach ( $sources as $source ) {
				if ( $count > 0 ) {
					$shortcode .= ',';
				}
				$shortcode .= urldecode( $source );
				$count ++;
			}
			$shortcode .= '"';
		}
		if ( count( $colors ) > 0 ) {
			$shortcode .= ' color="';
			$count = 0;
			foreach ( $colors as $color ) {
				if ( $count > 0 ) {
					$shortcode .= ',';
				}
				$shortcode .= $color;
				$count ++;
			}
			$shortcode .= '"';
		}
		if ( strlen( $iframe->getAttribute( 'width' ) ) ) {
			$shortcode .= ' width="' . $iframe->getAttribute( 'width' ) . '"';
		}
		if ( isset( $var_array['height'] ) ) {
			$shortcode .= ' height="' . $var_array['height'] . '"';
		} elseif( strlen( $iframe->getAttribute( 'height' ) ) ) {
			$shortcode .= ' height="' . $iframe->getAttribute( 'height' ) . '"';
		}
		if ( isset( $var_array['mode'] ) ) {
			$shortcode .= ' viewmode="' . $var_array['mode'] . '"';
		}
		if ( isset( $var_array['title'] ) ) {
			$shortcode .= ' title="' . htmlentities( $var_array['title'] ) . '"';
		}
		if ( isset( $var_array['showTitle'] ) ) {
			$shortcode .= ' show_title="' . $var_array['showTitle'] . '"';
		}
		if ( isset( $var_array['showDate'] ) ) {
			$shortcode .= ' show_date="' . $var_array['showDate'] . '"';
		}
		if ( isset( $var_array['showPrint'] ) ) {
			$shortcode .= ' show_printicon="' . $var_array[ 'showPrint' ] . '"';
		}
		if ( isset( $var_array['showTabs'] ) ) {
			$shortcode .= ' show_tabs="' . $var_array['showTabs'] . '"';
		}
		if ( isset( $var_array['showCalendars'] ) ) {
			$shortcode .= ' show_calendarlist="' . $var_array['showCalendars'] . '"';
		}
		if ( isset( $var_array['showTz'] ) ) {
			$shortcode .= ' show_timezone="' . $var_array['showTz'] . '"';
		}
		if ( isset( $var_array['wkst'] ) ) {
			$shortcode .= ' weekstart="' . $var_array['wkst'] . '"';
		}
		if ( isset( $var_array['hl'] ) ) {
			$shortcode .= ' language="' . $var_array['hl'] . '"';
		}
		if ( isset( $var_array['bgcolor'] ) ) {
			$shortcode .= ' bgcolor="' . $var_array['bgcolor'] . '"';
		}
		if ( strlen( $iframe->getAttribute( 'style' ) ) && strpos( $iframe->getAttribute( 'style' ), 'border:solid' ) !== FALSE ) {
			$shortcode .= ' show_border="true"';
		}
		if ( isset( $var_array[ 'ctz' ] ) ) {
			$shortcode .= ' timezone="' . $var_array['ctz'] . '"';
		}

		$shortcode .= ']';
		$content = str_replace( $match, $shortcode, $content );
	}
	return $content;
}

add_shortcode( 'google_calendar', 'google_calendar_shortcode' );
function google_calendar_shortcode( $atts ) {
	ob_start();

	foreach ( $atts as $key => $value ) {
		$atts[ $key ] = strip_tags( $value );
	}
	$atts['title'] = urlencode( html_entity_decode( $atts[ 'title'] ) );
	$errors = array();

	if ( isset( $atts['id'] ) ) {
		$ids = $atts['id'];
		$id_array = explode( ',', $ids );
	} else {
		$errors[] = "Missing Google Calendar ID";
	}

	if ( isset( $atts['color'] ) ) {
		$colors = $atts['color'];
		$color_array = explode( ',', $colors );
	}

	$day_array = array(
		'1' => 'SUNDAY',
		'2' => 'MONDAY',
		'3' => 'TUESDAY',
		'4' => 'WEDNESDAY',
		'5' => 'THURSDAY',
		'6' => 'FRIDAY',
		'7' => 'SATURDAY'
	);
	$language_array = array( 'ID', 'CA', 'CS', 'DA', 'DE', 'EN_GB', 'EN', 'ES', 'ES_419', 'FIL', 'FR',
		'HR', 'IT', 'LV', 'LT', 'HU', 'NL', 'NO', 'PL', 'PT_BR', 'PT_PT', 'RO', 'SK', 'SL',	'FI', 'SV',
		'TR', 'VI', 'EL', 'RU', 'SR', 'UK', 'BG', 'IW', 'AR', 'FA', 'HI', 'TH', 'ZH_TW', 'ZH_CN', 'JA', 'KO'
	);
	$country_array = google_calendar_shortcode_countries(); //list too long to put here. Returned from function below.

	$iframe = '<iframe src="https://www.google.com/calendar/embed?';

	if (  isset( $atts['title'] ) ) {
		$iframe .= 'title=' . $atts['title'] . '&amp;';
	}
	if ( isset( $atts['show_title'] ) ) {
		if ( in_array( strtoupper( $atts['show_title'] ), array( '0', 'NO', 'FALSE' ) ) ) {
			$iframe .= 'showTitle=0&amp;';
		} elseif ( ! in_array( strtoupper( $atts['show_title'] ), array( '1', 'YES', 'TRUE' ) ) ) {
			$errors[] = 'Invalid value for show_title. Using default.';
		}
	}
	if ( isset( $atts['show_date'] ) ) {
		if ( in_array( strtoupper( $atts['show_date'] ), array( '0', 'NO', 'FALSE' ) ) ) {
			$iframe .= 'showDate=0&amp;';
		} elseif ( !in_array( strtoupper( $atts['show_date'] ), array( '1', 'YES', 'TRUE' ) ) ) {
			$errors[] = 'Invalid value for show_date. Using default.';
		}
	}
	if ( isset( $atts['show_printicon'] ) ) {
		if ( in_array( strtoupper( $atts['show_printicon'] ), array( '0', 'NO', 'FALSE' ) ) ) {
			$iframe .= 'showPrint=0&amp;';
		} elseif ( !in_array( strtoupper( $atts['show_printicon'] ), array( '1', 'YES', 'TRUE' ) ) ) {
			$errors[] = 'Invalid value for show_printicon. Using default.';
		}
	}
	if ( isset( $atts['show_tabs'] ) ) {
		if ( in_array( strtoupper( $atts['show_tabs'] ), array( '0', 'NO', 'FALSE' ) ) ) {
			$iframe .= 'showTabs=0&amp;';
		} elseif ( !in_array( strtoupper( $atts['show_tabs'] ), array( '1', 'YES', 'TRUE' ) ) ) {
			$errors[] = 'Invalid value for show_tabs. Using default.';
		}
	}
	if ( isset( $atts['show_calendarlist'] ) ) {
		if ( in_array( strtoupper( $atts['show_calendarlist'] ), array( '0', 'NO', 'FALSE' ) ) ) {
			$iframe .= 'showCalendars=0&amp;';
		} elseif ( !in_array( strtoupper( $atts['show_calendarlist'] ), array( '1', 'YES', 'TRUE' ) ) ) {
			$errors[] = 'Invalid value for show_calendarlist. Using default.';
		}
	}
	if ( isset( $atts['show_timezone'] ) ) {
		if ( in_array( strtoupper( $atts['show_timezone'] ), array( '0', 'NO', 'FALSE' ) ) ) {
			$iframe .= 'showTz=0&amp;';
		} elseif ( !in_array( strtoupper( $atts['show_timezone'] ), array( '1', 'YES', 'TRUE' ) ) ) {
			$errors[] = 'Invalid value for show_timezone. Using default.';
		}
	}
	if ( isset( $atts['viewmode'] ) ){
		$viewmode = strtoupper( $atts['viewmode'] );
		if ( !in_array( $viewmode, array( 'WEEK', 'MONTH', 'AGENDA' ) ) ) {
			$viewmode = 'MONTH';
			$errors[] = 'Invalid viewmode. Using default.';
		}
		$iframe .= 'mode=' . $viewmode . '&amp;';
	}
	if ( isset( $atts['height'] ) ) {
		if ( preg_match( '/^([0-9]+)(px)?$/i', $atts['height'], $matches ) ) {
			$iframe .= 'height=' . $matches[1] . '&amp;';
		} else {
			$iframe .= 'height=600&amp;';
			$atts['height'] = '600';
			$errors[] = 'Invalid height. Using default.';
		}
	} else {
		$iframe .= 'height=600&amp;';
	}
	if ( isset( $atts['weekstart'] ) ) {
		if ( in_array( strtoupper( $atts['weekstart'] ), $day_array ) || array_key_exists( $atts['weekstart'], $day_array ) ) {
			if ( !is_numeric( $atts['weekstart'] ) ) {
				$weekstart = array_search( strtoupper( $atts['weekstart'] ), $day_array );
			} else {
				$weekstart = $atts['weekstart'];
			}
			$iframe .= 'wkst=' . $weekstart . '&amp;';
		} else {
			$errors[] = 'Invalid value for weekstart. Using default.';
		}
	}
	if ( isset( $atts['language'] ) ) {
		if ( in_array( strtoupper( $atts['language'] ), $language_array ) ) {
			$iframe .= 'hl=' . $atts['language'] . '&amp;';
		} else {
			$errors[] = 'Invalid value for language. Using default.';
		}
	}
	if ( isset( $atts['bgcolor'] ) ){
		$color = trim( $atts['bgcolor'], " #\t\n\r\0\x0B" ); //strip off '#' as well as the usual space, etc
		$color = str_replace( '%23', '', $color ); //if user copy-pasted the %23 from Google, strip it off as well
		if ( preg_match( '/^[a-f0-9]{3}$|^[a-f0-9]{6}$/i', $color ) ) {
			$iframe .= 'bgcolor=%23' . $color . '&amp;'; //check to be sure it's a hex code. Otherwise don't use it and Google will default to white.
		} else {
			$errors[] = 'Invalid value for bgcolor. Using default.';
		}
	}
	//do each of the given calendar ID(s) and match with the given color(s)
	$count = 0;
	foreach( $id_array as $id ) {
		if ( strlen( $id ) > 0 ) {
			if ( $count )
				$iframe .= '&amp;';
			$iframe .= 'src=' . trim( $id );
			if ( isset( $color_array[ $count ] ) ) {
				$color = trim( $color_array[ $count ], " #\t\n\r\0\x0B" ); //strip off '#' as well as the usual space, etc
				$color = str_replace( '%23', '', $color ); //if user copy-pasted the %23 from Google, strip it off as well
				if ( preg_match( '/^[a-f0-9]{3}$|^[a-f0-9]{6}$/i', $color ) ) {
					$iframe .= '&amp;color=%23' . $color;
				} else {
					$errors[] = 'Invalid value for color. Using default.';
					$iframe .= '&amp;color=%232952A3';
				}
			}
			$count++;
		} else {
			$errors[] = "Missing Google Calendar ID!";
		}
	}
	if ( isset( $atts['timezone'] ) ){
		$timezone = urldecode( $atts['timezone'] ); //if user copy-pasted the %2F, convert it to a / for now so we can keep the array more readable
		if ( in_array( $timezone, $country_array ) ) {
			$iframe .= '&amp;ctz=' .  $timezone;
		} else {
			$errors[] = 'Invalid Timezone';
		}
	}
	$iframe .= '"';

	if ( isset( $atts[ 'show_border' ] ) ) {
		if ( in_array( strtoupper( $atts['show_border'] ), array( '1', 'YES', 'TRUE' ) ) ) {
			$iframe .= ' style=" border:solid 1px #777 "';
		} elseif ( in_array( strtoupper( $atts['show_border'] ), array( '0', 'NO', 'FALSE' ) ) ) {
			$iframe .= ' style=" border:0 "';
		} else {
			$iframe .= ' style=" border:0 "';
			$errors[] = 'Invalid value for show_border. Using default.';
		}
	} else {
		$iframe .= ' style=" border:0 "';
	}
	if ( isset( $atts['width'] ) ) {
		if ( preg_match( '/^[0-9]+(%|px|em)?$/', $atts['width'], $width_matches ) ) {
			$iframe .= ' width="' . $width_matches[0] . '"';
		} else {
			$iframe .= ' width="100%"';
			$errors[] = 'Invalid width. Using default.';
		}
	} else {
		$iframe .= ' width="100%"';
	}
	if ( isset( $atts['height'] ) ) {
		$iframe .= ' height="' . $atts['height'] . '"';
	} else {
		$iframe .= ' height="600"';
	}
	$iframe .= '></iframe>';

	echo $iframe;

	if ( count( $errors ) ) {
		echo "\n" . '<div class="error google-calendar-shortcode-errors">' . "\n";
		echo "<small><strong>Error";
		if ( count( $errors ) > 1 )
			echo "s";
		echo " in calendar configuration: </strong></small>";
		if ( count( $errors ) > 1 )
			echo "<br />\n";
		foreach ( $errors as $error ) {
			echo "<small>" . $error . "</small><br />\n";
		}
		echo '</div><!--//.error gcs_errors-->' . "\n";
	}

	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function google_calendar_shortcode_countries() {
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
