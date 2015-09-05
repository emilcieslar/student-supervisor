# Student Project Supervision Management System
University of Glasgow Masters Project 2015

## INSTALLATION GUIDE:

1. Create a new database
2. Modify config_sample.json and change the name to config.json
3. Run install.sql in your database
4. Change RewriteBase in .htaccess file according to your website, for example: http://mywebsite.co.uk/my-folder
   In addition, if your server doesn't support -MultiViews, comment this option out, otherwise it won't work
5. You're good to go


### Notes:
1. There are two default users: emil and rose, password for both is: heslo
   - emil is a student and rose is a supervisor
2. Google login won't work unless you create your own google credentials and set up the correct redirect uri (guide for setting up the credentials can be found here: https://www.youtube.com/watch?v=oxa581kKBNg)
