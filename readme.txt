=== Google Calendar Shortcode ===

Contributors: Eli Madden, adamfranco
Tags: google, calendar
Requires at least: 3.3
Tested up to: 4.3.1
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.en.html

Insert a Google Calendars via a shortcode; automatically convert calendar iframe embed-code to shortcode syntax.

== Description ==

The Google Calendar Shortcode plugin allows users to safely embed Google Calendars
in posts and pages using a shortcode. Users can copy-paste the Google-generated iframe
embed-code into a post and the plugin will convert it to a shortcode.

All Google Calendar iframe attributes available at the time of this writing are supported:

<table>
<tr><th>shortcode parameter</td><th>Google Calendar IFRAME Parameter</th></tr>
<tr><td>width</td><td>width</td></tr>
<tr><td>height</td><td>height</td></tr>
<tr><td>viewmode</td><td>viewmode</td></tr>
<tr><td>title</td><td>title</td></tr>
<tr><td>show_title</td><td>showTitle</td></tr>
<tr><td>show_date</td><td>showDate</td></tr>
<tr><td>show_printicon</td><td>showPrint</td></tr>
<tr><td>show_tabs</td><td>showTabs</td></tr>
<tr><td>show_calendarlist</td><td>showCalendars</td></tr>
<tr><td>show_timezone</td><td>showTz</td></tr>
<tr><td>weekstart</td><td>wkst</td></tr>
<tr><td>language</td><td>hl</td></tr>
<tr><td>bgcolor</td><td>bgcolor</td></tr>
<tr><td>show_border="true"</td><td>style="border:solid"</td></tr>
<tr><td>timezone</td><td>ctz</td></tr>
</table>

More usage details can be found in [the Middlebury College wiki](http://mediawiki.middlebury.edu/wiki/LIS/Help:WordPress_Plugins#Google_Calendar_Shortcode)

== Installation ==

1. Unzip the `google-calendar-shortcode.zip` into your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.0 =
* First release.
