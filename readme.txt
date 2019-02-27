=== Open edX LMS and Wordpress integrator ===
Contributors: eduNEXT
Tags: wordpress, plugin, Open edX, LMS
Requires at least: 3.9
Tested up to: 4.9.5
Stable tag: 1.9.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


This plugin makes it easy to integrate a Wordpress site with an open edX site from eduNEXT or your own installation.


== Description ==

Open edX LMS and Wordpress integrator. This plugin helps you set up your Wordpress site as the front end site or marketing site for your online learning initiative powered by the open edX platform.

The idea behind this integration is to leave the open edX only to be used once the user logs in to visit his dashboard or courses and to use the power of Wordpress to build:

- the site's the homepage
- the course catalog page
- the pages that describe each of the courses
- any additional static pages that the initiative requires


This plugin is made available by eduNEXT, a world class open edX services provider. The plugin is initially tuned to work against an open edX site provided by eduNEXT, but it can also be used to integrate Wordpress and any open edX site.

The integration between open edX and Wordpress currently works at 2 different levels:

1) At the site menu level.
By adding a menu to your open edX site that allows users to log in / register to the open edX site. Once the user has logged in, this menu becomes a user menu with all the standard options the open edX user menu has.

2) At the page / post content level.
By adding a course access button to be placed in each of the pages that describe the courses.
This button is added using a shortcode, and it takes care of rendering the correct action depending on the configuration that the course has for that particular user on the open edX site.

If you require a different kind of integration, contact us at https://www.eduNEXT.co


== Usage ==

= Menu Integration =

To create an open edX menu:

1. Go to Appearance > Menus
2. On the accordion item called "Open edX WP Integrator", select from the list the menu-items you want to include in your menu. Press Add to Menu.
3. Organize the items in your menu.

The list of menu items includes:

- Login/User Menu:

    If the user is logged in, the menu will display the name of the user with a link to the dashboard of the lms.
    Otherwise it will display a link to login, with the label provided. To change the label, you can edit the menu item in place. Be sure to follow the convention <Label displayed for logged out user>/<This will be replaced by the user name>

- Login/Dashboard:

    If the user is logged in, the menu will display the configured label with a link to the dashboard of the lms.
    Otherwise it will display a link to login, with the label provided. To change the label, you can edit the menu item in place. Be sure to follow the convention <Label displayed for logged out user>/<Label displayed for the logged out user>

- Login Btn:

    A menu item, with a link to the login page. If the user is already logged in, nothing will appear.

- Register Btn:

    A menu item, with a link to the register page. If the user is already logged in, nothing will appear.

- User Menu:

    A menu item, with a link to the dashboard page using the username as the label. If the user is not logged in, this item will not appear.

- Resume your last course:

    A link to the last known location of a user in his or her courses. If the user is not logged in, this item will not appear.

- Dashboard:

    A link to the user dashboard. If the user is not logged in, this item will not appear.

- Profile:

    A link to the user profile page. If the user is not logged in, this item will not appear.

- Account:

    A link to the user account settings page. If the user is not logged in, this item will not appear.

- Sign Out:

    A link to a page that will log the user out. If the user is not logged in, this item will not appear.



= Course Pages Integration =


Buttons to enroll or in general take any action call on the courses are produced using the `edunext_enroll_button` shortcode.

The most simple example is using the shortcode giving it the course_id of the course only. E.g.:

    [edunext_enroll_button course_id="course-v1:edX+Demo+demo_course"]


To configure any of the settings per-button, you can also change the setting of any setting defined in the settings page specifically for a particular shortcode.


E.g: To change the label from "Enroll" which is the default, to "Enroll in the course now" you can use:

    [edunext_enroll_button course_id="course-v1:edX+Demo+demo_course" label_enroll="Enroll in the course now"]


Here is a list of all the properties you can override:

- button_class_generic
- container_class_generic
- color_class_generic

- label_enroll
- button_class_enroll
- container_class_enroll
- color_class_enroll

- label_go_to_course
- button_class_go_to_course
- container_class_go_to_course
- color_class_go_to_course

- label_course_has_not_started
- button_class_course_has_not_started
- container_class_course_has_not_started
- color_class_course_has_not_started

- label_invitation_only
- button_class_invitation_only
- container_class_invitation_only
- color_class_invitation_only

- label_enrollment_closed
- button_class_enrollment_closed
- container_class_enrollment_closed
- color_class_enrollment_closed



== Installation ==

= Automatic Installation =

1. Go to the Plugins menu from the dashboard.
2. Click on the "Add New" button on this page.
3. Search for "Open edX LMS and Wordpress integrator" in the search bar provided.
4. Click on "Install Now" once you have located the plugin.
5. On successful installation click the "Activate Plugin" link to activate the plugin.

= Manual Installation =

1. Download the "Open edX LMS and Wordpress integrator" plugin from wordpress.org.
2. Now unzip and upload the folder using the FTP application of your choice.
3. The plugin can then be activated by navigating to the Plugins menu in the admin dashboard.


== Changelog ==

= 1.1 =
* 2018-04-16
* Adding the navigation menu integration

= 1.0 =
* 2018-01-20
* Initial release

== Upgrade Notice ==

= 1.0 =
* 2018-01-20
* Initial release
