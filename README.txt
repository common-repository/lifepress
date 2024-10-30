=== LifePress ===
Contributors: ashanjay
Tags: journal, calendar, diary, life events, online journal
Author URI: https://ashanjay.com
Requires at least: 6.0
Tested up to: 6.6.1
Stable tag: 2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

You are the creator of wonderful events in your life. Record and track progress of your life events with LifePress.

== Description ==

https://www.youtube.com/watch?v=umNuBKNpHBI

LifePress is a calendar based journal recorder that will allow you to track back progress and review past events to learn from the past and make positive progress in future. With LifePress you can record unlimited daily events easily with unlimited color-coded categories separated for each user. Track back recorded events quickly with weekly and monthly calendar view. 

You are the creator of wonderful events, journal them with **[LifePress](https://ashanjay.com/lifepress/)**.

== Main FEATURES ==

* Record **unlimited** daily events
* Create unlimited color-coded event categories
* Edit event categories in real-time
* Ability to write lengthy description for entries if desired
* Record events by each loggedin website user
* Navigate through months or weeks from top navigator
* Easily check back and view or track recorded events and progress
* Weekly and monthly view of events
* Search previously recorded events
* Compatible with responsive design for veiwing from various devices
* Various pluggable actions and filters allow developers to extend features
* Quickly access any month from fast selector
* From any date come back easily to current date
* Attach images to entries
* Insert Calendar dashboard anywhere on your site using shortcode
* Ability to set timezone for entries

== 21 days to make a change in your life ==

Scientists have calculated that an average of 21 days are required to be established as a habit in your life, if done repeatedly on those days. LifePress offers a great platform to record your repeated attempts at learning a new talent or practice to improve for 21 days. If you record these repeated attempts in LifePress, you can easily look back and see when you started the 21 day challenge, when you fell off, and how you are making progress.


== Own your personal Journal Data ==

With LifePress, journal entry data is all yours. Your personal data is privately saved only in your database and only you can see them. Each logged in user will have their own separate LifePress calendar.

== Business Applications ==

LifePress is a great solution for business organizations that want to influence their members to record daily entries to track progress in a calendar format. Each user's data is only visible to that user and admin in the calendar, which makes it a great team empowering solution. This would be an ideal solution for life coaches, fitness instructors, physical/life therapists who want their clients to record progressive data to help their clients make tangible changes in life.

== LifePress Pro - Coming Soon! ==

As we have received several requests to improve the lifepress plugin for greater application, we are developing the Lifepress Pro, plugin as an extension to LifePress that will bring greater data metric information for calendar entries. We are also hoping in future iterations to implement data visualization for weekly and monthly data aggregates and ability to set goals and calculate progress towards them. 
Update (Aug 2024) We are hoping to get this Pro version out as we release version 2.1 and continue to put time into LifePress development.


== Screenshots ==

1. LifePress front-end interactive dashboard for loggedin user
2. Super quick add new entry form
3. Journal entry with image view
4. Edit entry tag form

== Changelog ==
= 2.1 (2024-8-20) =
ADDED: timezone setting
ADDED: setting to set how many past and future years for selection
ADDED: button to go to today from any month
ADDED: previous month date box highlight
ADDED: next month date boxes with highlighted color
ADDED: previous and next month entried to also load on view in month view
FIXED: uniform font across the design
FIXED: save meta error with no data at first load
FIXED: proper timezone adjustment of entries
FIXED: implement db cache for entry meta data
FIXED: sanitized and escape output data
FIXED: start of week to sync with wp settings
FIXED: week view UI and styles
UPDATED: Layout and UI design changes
UPDATED: Search lightbox UI and designs

= 2.0.3 (2023-7-10) =
FIXED: several php 8.2 compatibilities
FIXED: WordPress 6.2.2 compatibility

= 2.0.2 (2022-2-10) =
ADDED: Ability to insert lifepress via shortcode [add_lifepress]
ADDED: ability to click on empty month view date box to create new entry
ADDED: Option to set how the new month data to be loaded to calendar
FIXED: dashboard template overridding all the template pages
FIXED: input field placeholder to have different styles
FIXED: entry tag with & not formatting correct
UPDATED: Add new entry form design
UPDATED: Settings page layout design

= 2.0.1 (2021-7-27) =
ADDED: loading animation fullscreen
FIXED: footer notices showing a scroll bar for a second
FIXED: LIFEPRESS_Helper class initiation error

= 2.0 (2021-7-8) =
ADDED: trumbrowyg entry editor 
ADDED: support for image for an entry
ADDED: Ability to edit tag data on frontend
ADDED: ability to scroll tag circles on left side if too many to display
ADDED: Settings option to set default view style
ADDED: Save draft entry option
FIXED: once a tag is selected, can not change
FIXED: uninstall option delete values 
FIXED: no search results not showing anything
FIXED: footer notices to animate on appearance
FIXED: adding new tag with color real time update content
FIXED: various responsive styles
UPDATED: LIFEPRESS_Helper function to do array sanitation
UPDATED: Interface styles

= 1.0.1 (2020-12-21) =
ADDED: quick month selector
ADDED: text string translatable POT file
FIXED: dashboard not loading for other users
FIXED: untranslative text strings converted to __()

= 1.0.0 (2020-11-13) =
* Initial Release

== Frequently Asked Questions ==

= Some months do not load entries, what could I do? =

In version 2.0.2 we added option in LifePress setting "Month data loading method" switch this to "Load fresh data at all times". This will force the calendar to load a fresh copy of data from database everytime a month is switched, instead of using previously loaded month data.

= Can users see other's data? =

No, only the user with administrator permission can see all the entries from backend. Event entry records are saved for each user.

= Is there a limit to how many entries I can submit? =

No, there is no limit to how many entries you can submit. It is unlimited.

= Can I set the default view style? =

Yes, you can set default view style from month view to week view from Settings > LifePress Settings > Default Dashboard View

= Can I display the dashboard on any page? =

Not at this moment. Right now it is only visible on the lifepress page created automatically.

= Can I show other post types in the lifePress dashboard? =

No, only the lifepress created custom post types with slug lp_entry can be shown in the dashboard.


