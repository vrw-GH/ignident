=== Auto Backup ===
Contributors: autobackup
Tags: Backup, AutoBackup, Sheduled Backup, Cloud Backup, Restore, WordPress Backup
Requires at least: 5.0
Tested up to: 6.5.0
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin for backup and restoration with cloud storage like NeevCloud, DropBox, AWS S3 etc.

== Description ==

Auto Backup is an all-inclusive and reliable WordPress backup plugin that ensures your website's data is safe, granting you peace of mind. It effortlessly generate backups of your WordPress website, encompassing all files, databases, themes, plugins, and media files.

The plugin offers diverse remote storage options for securely storing your backups. You have the flexibility to save backups on well-known cloud storage platforms such as NeevCloud, Dropbox, Amazon S3, and Local servers. This redundancy guarantees the safety of your backups, even in the event of technical issues with your website's server.

The paid version also allows you to backup to Microsoft OneDrive, pCloud, Google Drive, Backblaze and FTP.

With this plugin, you can establish automated backup schedules, ensuring your website's data is consistently backed up without any manual effort. You have the flexibility to select the backup frequency, ranging from every 2 hours, every 4 hours, daily, weekly, or monthly, according to your preferences.

Moreover, the option to take selective backups allows you to choose specific files or databases to include in the backup, making it convenient when you only need to back up certain parts of your website.

Whether you're new to WordPress or a experienced user, Auto Backup offers a user-friendly interface and powerful features to guarantee the safety and easy recovery of your website's data. Its intuitive interface, versatile backup options, and seamless restoration process make Auto Backup the ideal solution for safeguarding your WordPress website against unforeseen data loss.

Supported Cloud Storages:
1. NeevCloud
2. Dropbox
3. AWS S3
4. Local Server

== Installation ==

Auto Backup Plugin installation doesn’t differ from any other plugin installation process, so you might be familiar with this process already. If not, please follow instructions below.

1. First download the plugin file.

2. Login to wp-admin account.

3. Navigate to Plugins >> Click on Add New and upload plugin file.

4. Click “Install Now” button and wait while plugin is uploaded on your server.

5. Now, Activate the plugin by clicking on "Activate Plugin" button.

6. Once it will get Activated you will see Auto Backup on WordPress dashboard.

== Backup With AutoBackup ==

AutoBackup is a robust plugin that allows you to store backup to your preferred storage location within few clicks.

Backup to NeevCloud, Amazon S3, Localhost or Dropbox
Plugin allows to backup manually or schedule to run every 2, 4, 8 or 12 hours, daily, weekly, monthly or fortnightly.

== Restore with Auto Backup ==

Your WordPress website is at risk of vulnerabilities that could lead to hacking, update issues, or server crashes.  Whether it's a mistake, human error, or the need to reverse changes, you can effortlessly restore your website using Auto Backup.

Simply select the components you want to restore, such as files or the database, click to "Create" button to initiate the restoration process.

== Migrate with Auto Backup ==

Migrating your WordPress website to a different web host, server, or domain becomes very easy with the Autobackup plugin.

To migrate, simply download your backup files from the source site, then upload them into your destination site. This not only saves you time but also minimizes the chances of encountering broken links or missing files often associated with manual migrations.

= Why Auto Backup? =

Auto Backup offers a user freindly interface and powerfull features, to ensure the effortless recovery of your WordPress site. Whether you're a novice or an experienced WordPress user, the tool caters to users of all levels of expertise.

Auto Backup Feature:

* Backup, migration and restoration 
* Provide multiple remote storage locations
* Provide flexibility to schedule backups
* Display active scheduled tasks list
* Option to remove schedules
* Easy to use


== Used APIs ==
Auto Backup plugin is integrated with following APIs for backup, migration and restoration.

1. Amazon AWS : 
Terms of Use URL:  https://aws.amazon.com/terms/?nc1=f_pr
Privacy Policy URL: https://aws.amazon.com/privacy/?nc1=f_pr

2. Google Drive :
Terms of Use URL: https://support.google.com/drive/answer/2450387?hl=en
Privacy Policy URL: https://support.google.com/drive/answer/141702?hl=en&ref_topic=2428743&sjid=11462729920314988767-AP

3. Dropbox
Terms of Use and Privacy Policy URL: https://www.dropbox.com/terms
 

 == Frequently Asked Questions ==
 = How do I install the Auto Backup plugin? =

You can check the installation steps from the  "Installation" section of this file. 

= What if I have a problem / need support ? =

If you’re facing any issues with the plugin you can reach out to us over support email here plugin@autobackup.io
Before you get in touch, please make sure that you have updated the latest release of our plugin.

== Screenshots ==

1. Dashboard
2. Cloud Storage Settings
3. Schedule Your Backup
4. Import And Export

== Changelog ==
= 1.0.3 =
* Add the option to sort files by their size and date

= 1.0.2 =
* Add display active scheduled tasks list
* Add option to remove schedules

= 1.0.1 =
* PHP version dependency issue fixed in AWS composer. 

= 1.0.0 =
* Plugin released. 