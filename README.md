totem-api
========

This is the API and Database server that powers [totem.fm](totem.fm). This is designed to work in conjunction with the websocket server, available [here](http://github.com/williamtdr/totem-server).


**Installation Instructions**

 1. Clone this repository into a directory publicly accessible on the internet. The root of the repository is designed to be the public web root.
 2. Install dependencies: PHP 5, MySQL, PHPMyAdmin recommended.
 3. Sign up for a Youtube API key. There's a good tutorial [here](http://help.dimsemenov.com/kb/wordpress-royalslider-tutorials/wp-how-to-get-youtube-api-key). Also sign up for Google plus/login. If you want email integration, also sign up for [mandrill](https://www.mandrill.com/).
 4. Copy config.sample.live.php to config.live.php, and fill in the Google credentials. Set up the database if you haven't done so yet, and fill in the credentials to the configuration file. You'll also want to import totem.sql into your database.
 5. Clone totem-web and totem-static. In totem-web, replace api.totem.fm in the config file with your API endpoint. Fill in the google client ID with the one you retrieved from the login form.

If everything went well, you should now have your own copy of totem! If you have any questions, contact me via [twitter](twitter.com/williamtdr), the email on my GitHub profile, or in the [Radiant slack](slack.radiant.dj). Happy listening!