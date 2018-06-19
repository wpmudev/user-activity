# User Activity

**INACTIVE NOTICE: This plugin is unsupported by WPMUDEV, we've published it here for those technical types who might want to fork and maintain it for their needs.**


Collect user activity data and make it available via a tab under the Site Admin.

* Monitor user activity 
* Adds User Activity stats page 
* See number of users currently online 
* View main site or network activity 
* Make informed marketing decisions 
* Great for single sites and networks 

## User Activity lets you easily monitor your number of active users.

This plugin provides you with the ability to collect user activity data for easily monitoring user activity.

##### Quickly see the number of users in the last:

* 5 minutes
* Hour
* Day
* Week
* Month

It also allows you to display the number of users currently online anywhere on your main site – or any site on a network.

### Get Real Time Reports

Monitor user activity via your new ‘User Activity’ page in the network admin dashboard.

![See at a glance who's been active in your network.][33]

See at a glance who's been active in your network.

Make smarter marketing decisions, better connect with your users and track participation with User Activity.

### **To install:**

1. Download the plugin file
2. Unzip the file into a folder on your hard drive
3. Upload **_/user-activity/_** folder to **/wp-content/plugins/** folder on your site
4. Login to your admin panel for WordPress or Multisite and activate the plugin: 
    * On regular WordPress installs – visit **Plugins** and **Activate **the plugin.
    * For WordPress Multisite installs – Activate it blog-by-blog (say if you wanted to make it a Supporter premium plugin), or visit **Network Admin ->  
Plugins** and **Network Activate** the plugin.

### **To Use:**

This plugin can be used two ways:

1. By monitoring user activity at **Users > User Activity** or in Multisite, at **Network Admin > Settings > User Activity**
2. To place the number of active users anywhere on your main site or another site.

#### Monitoring User Activity

1\. Go to **Users > User Activity** in your dashboard, or for Multisite, go to **Settings > User Activity** in your **Network Admin** dashboard

2\. Check number of users in last:

* 5 minutes
* Hour
* Day
* Week
* Month

![See at a glance who's been active in your network.][33]See at a glance who's been active in your network.

#### Using To Display Number of Active Users

The plugin also has 2 functions you can use to display user activity anywhere on your main site, or any other site in your network.

##### Functions:
    
    
    display_user_activity(PERIOD_IN_MINUTES)
    
    
    user_activity_output(PERIOD_IN_MINUTES, TOTAL_TO_DISPLAY, 'TEXT_BEFORE_ALL', 'TEXT_BEFORE_EACH', 'TEXT_AFTER_ALL', 'TEXT_AFTER_EACH', AVATARS 'yes' OR 'no', AVATAR_SIZE)

Below are some examples of how you might use them. Just pop either of these examples in a template of your theme, and adjust to suit your needs. Don’t forget to ensure they are wrapped in php tags. We’ve used images here instead of code to prevent it from executing on this site, so you’ll need to actually type this stuff in :)

![user-activity-functions][36]

The 1st example will display the number of users online in the last 24 hours.

The 2nd will output a nice list of users online right now.

Notice in the second example how you can also wrap the parameters in HTML tags so you can style ’em in your theme’s style-sheet. The output might look something like this with a bit of basic CSS applied:

![user-activity-functions-output][37]

[33]: https://premium.wpmudev.org/wp-content/uploads/2008/08/user-activity-settings-3.png
[36]: https://premium.wpmudev.org/wp-content/uploads/2008/08/user-activity-functions1.png
[37]: https://premium.wpmudev.org/wp-content/uploads/2008/08/user-activity-functions-output.png
