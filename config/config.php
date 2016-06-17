<?php

/**
 * Configuration for: Database Connection
 * This is the place where your database login constants are saved
 *
 * For more info about constants please @see http://php.net/manual/en/function.define.php
 * If you want to know why we use "define" instead of "const" @see http://stackoverflow.com/q/2447791/1114320
 *
 * DB_HOST: database host, usually it's "127.0.0.1" or "localhost", some servers also need port info
 * DB_NAME: name of the database. please note: database and database table are not the same thing
 * DB_USER: user for your database. the user needs to have rights for SELECT, UPDATE, DELETE and INSERT.
 *          by the way, it's bad style to use "root", but for development it will work.
 * DB_PASS: the password of the above user
 */
define("DB_HOST", "localhost");
define("DB_NAME", "eabdb");
define("DB_USER", "eabdb");
define("DB_PASS", "Kd3Zgr46gwlQgbavgC4f116F6bv2P1SP");

/**
 * Configuration for: Cookies
 * Please note: The COOKIE_DOMAIN needs the domain where your app is,
 * in a format like this: .mydomain.com
 * Note the . in front of the domain. No www, no http, no slash here!
 * For local development .127.0.0.1 or .localhost is fine, but when deploying you should
 * change this to your real domain, like '.mydomain.com' ! The leading dot makes the cookie available for
 * sub-domains too.
 * @see http://stackoverflow.com/q/9618217/1114320
 * @see http://www.php.net/manual/en/function.setcookie.php
 *
 * COOKIE_RUNTIME: How long should a cookie be valid ? 1209600 seconds = 2 weeks
 * COOKIE_DOMAIN: The domain where the cookie is valid for, like '.mydomain.com'
 * COOKIE_SECRET_KEY: Put a random value here to make your app more secure. When changed, all cookies are reset.
 */
define("COOKIE_RUNTIME", 1209600);
define("COOKIE_DOMAIN", ".eabham.org");
define("COOKIE_SECRET_KEY", "hldI4exn9bdN2M9y0jP2219M");

/**
 * Configuration for: Email server credentials
 *
 * Here you can define how you want to send emails.
 * If you have successfully set up a mail server on your linux server and you know
 * what you do, then you can skip this section. Otherwise please set EMAIL_USE_SMTP to true
 * and fill in your SMTP provider account data.
 *
 * An example setup for using gmail.com [Google Mail] as email sending service,
 * works perfectly in August 2013. Change the "xxx" to your needs.
 * Please note that there are several issues with gmail, like gmail will block your server
 * for "spam" reasons or you'll have a daily sending limit. See the readme.md for more info.
 *
 * define("EMAIL_USE_SMTP", true);
 * define("EMAIL_SMTP_HOST", "ssl://smtp.gmail.com");
 * define("EMAIL_SMTP_AUTH", true);
 * define("EMAIL_SMTP_USERNAME", "xxxxxxxxxx@gmail.com");
 * define("EMAIL_SMTP_PASSWORD", "xxxxxxxxxxxxxxxxxxxx");
 * define("EMAIL_SMTP_PORT", 465);
 * define("EMAIL_SMTP_ENCRYPTION", "ssl");
 *
 * It's really recommended to use SMTP!
 *
 */
define("EMAIL_USE_SMTP", true);
define("EMAIL_SMTP_HOST", "ssl://smtp.gmail.com");
define("EMAIL_SMTP_AUTH", true);
define("EMAIL_SMTP_USERNAME", "developmentdummi");
define("EMAIL_SMTP_PASSWORD", "loVrXeMen9Ra3VpD2MahR74K91uIK83f");
define("EMAIL_SMTP_PORT", 465);
define("EMAIL_SMTP_ENCRYPTION", "ssl");
define("EMAIL_FOOTER_AND_SIGNATURE", "\n\nIf you have any questions, please contact the Volunteer Coordinator Hannah Bowers at hbowers@uab.edu or EAB's IT Team at eabitteam@gmail.com.\n\nSincerely,\nEAB's MOM (Medical Office Manager)");

/**
 * Configuration for: password reset email data
 * Set the absolute URL to password_reset.php, necessary for email password reset links
 */
define("EMAIL_PASSWORDRESET_URL", "http://192.168.56.103/account/password_reset.php");
define("EMAIL_PASSWORDRESET_FROM", "no-reply@eab.com");
define("EMAIL_PASSWORDRESET_FROM_NAME", "No Reply | Equal Access Birmingham");
define("EMAIL_PASSWORDRESET_SUBJECT", "Password reset for EAB Website");
define("EMAIL_PASSWORDRESET_CONTENT", "Please click on this link to reset your password:");

/**
 * Configuration for: New Account Email
 * Emails the new user with a user name and random password (see Registration.php for more info)
 */
define("EMAIL_NEW_ACCOUNT_FROM", "no-reply@eab.com");
define("EMAIL_NEW_ACCOUNT_FROM_NAME", "No Reply | Equal Access Birmingham");
define("EMAIL_NEW_ACCOUNT_SUBJECT", "New Account for Equal Access Birmingham");
define("EMAIL_NEW_ACCOUNT_BODY", "You have been signed up for a new account at Equal Access Birmingham (eab.path.uab.edu).  Below is your user name and temporary password, which will expire in 3 days.\n\n");

/**
 * Configuration for: account reset email
 * Much the same as registering, but this is for an outdated account
 */
define("EMAIL_RESET_ACCOUNT_FROM", "no-reply@eab.com");
define("EMAIL_RESET_ACCOUNT_FROM_NAME", "No Reply | Equal Access Birmingham");
define("EMAIL_RESET_ACCOUNT_SUBJECT", "Your Account with Equal Access Birmingham Has Been Reset");
define("EMAIL_RESET_ACCOUNT_BODY", "Your account has been reset.  Below are your new user name and temporary password, which will expire in 3 days.\n\n");

/**
 * Configuration for: Hashing strength
 * This is the place where you define the strength of your password hashing/salting
 *
 * To make password encryption very safe and future-proof, the PHP 5.5 hashing/salting functions
 * come with a clever so called COST FACTOR. This number defines the base-2 logarithm of the rounds of hashing,
 * something like 2^12 if your cost factor is 12. By the way, 2^12 would be 4096 rounds of hashing, doubling the
 * round with each increase of the cost factor and therefore doubling the CPU power it needs.
 * Currently, in 2013, the developers of this functions have chosen a cost factor of 10, which fits most standard
 * server setups. When time goes by and server power becomes much more powerful, it might be useful to increase
 * the cost factor, to make the password hashing one step more secure. Have a look here
 * (@see https://github.com/panique/php-login/wiki/Which-hashing-&-salting-algorithm-should-be-used-%3F)
 * in the BLOWFISH benchmark table to get an idea how this factor behaves. For most people this is irrelevant,
 * but after some years this might be very very useful to keep the encryption of your database up to date.
 *
 * Remember: Every time a user registers or tries to log in (!) this calculation will be done.
 * Don't change this if you don't know what you do.
 *
 * To get more information about the best cost factor please have a look here
 * @see http://stackoverflow.com/q/4443476/1114320
 *
 * This constant will be used in the login and the registration class.
 */
define("HASH_COST_FACTOR", "10");
