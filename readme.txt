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
<tr><th>width</th><th>width</th></tr>
<tr><th>height</th><th>height</th></tr>
<tr><th>viewmode</th><th>viewmode</th></tr>
<tr><th>title</th><th>title</th></tr>
<tr><th>show_title</th><th>showTitle</th></tr>
<tr><th>show_date</th><th>showDate</th></tr>
<tr><th>show_printicon</th><th>showPrint</th></tr>
<tr><th>show_tabs</th><th>showTabs</th></tr>
<tr><th>show_calendarlist</th><th>showCalendars</th></tr>
<tr><th>show_timezone</th><th>showTz</th></tr>
<tr><th>weekstart</th><th>wkst</th></tr>
<tr><th>language</th><th>hl</th></tr>
<tr><th>bgcolor</th><th>bgcolor</th></tr>
<tr><th>show_border="true"</th><th>style="border:solid"</th></tr>
<tr><th>timezone</th><th>ctz</th></tr>
</table>

== Installation ==

1. Unzip the `google-calendar-shortcode.zip` into your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.0 =
* First release.
