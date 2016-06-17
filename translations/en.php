<?php

/**
 * Please note: we can use unencoded characters like ö, é etc here as we use the html5 doctype with utf8 encoding
 * in the application's header (in views/_header.php). To add new languages simply copy this file,
 * and create a language switch in your root files.
 */

// login & registration classes
define("MESSAGE_ADMIN_STATUS_ADDED", "Add admin privileges to the following user: ");
define("MESSAGE_ADMIN_STATUS_REMOVAL_ERROR", "Can't remove your own admin status!");
define("MESSAGE_ADMIN_STATUS_REMOVED", "Remove the following user's admin privileges: ");
define("MESSAGE_COOKIE_INVALID", "Invalid cookie");
define("MESSAGE_DATABASE_ERROR", "Database connection problem.");
define("MESSAGE_DELETE_PERSONAL_ACCOUNT_ERROR", "Can't delete your own account!");
define("MESSAGE_DELETE_USER_CONFIRM", "Delete the following user: ");
define("MESSAGE_EMAIL_ALREADY_EXISTS", "This email address is already registered. Please visit the Account Management Page if you need to reset the account.");
define("MESSAGE_EMAIL_CHANGE_FAILED", "Sorry, your email changing failed.");
define("MESSAGE_EMAIL_CHANGED_SUCCESSFULLY", "Your email address has been changed successfully. New email address is ");
define("MESSAGE_EMAIL_EMPTY", "Email cannot be empty");
define("MESSAGE_EMAIL_INVALID", "Your email address is not in a valid email format");
define("MESSAGE_EMAIL_SAME_LIKE_OLD_ONE", "Sorry, that email address is the same as your current one. Please choose another one.");
define("MESSAGE_EMAIL_TOO_LONG", "Email cannot be longer than 64 characters");
define("MESSAGE_INVALID_ACCOUNT", "This account is no longer valid. Please email EAB's volunteer coordinator Hannah Bowers at <a href=\"mailto:hbowers@uab.edu\">hbowers@uab.edu</a> for a new account.");
define("MESSAGE_LINK_PARAMETER_EMPTY", "Empty link parameter data.");
define("MESSAGE_LOGGED_OUT", "You have been logged out.");
// The "login failed"-message is a security improved feedback that doesn't show a potential attacker if the user exists or not
define("MESSAGE_LOGIN_FAILED", "Login failed.");
define("MESSAGE_OLD_PASSWORD_WRONG", "Your OLD password was wrong.");
define("MESSAGE_NEW_ACCOUNT_MAIL_ERROR", "Sorry, we could not send an email with the account information.  The account has NOT been created.");
define("MESSAGE_NEW_ACCOUNT_MAIL_NOT_SENT", "New account creation email NOT successfully sent. Error: ");
define("MESSAGE_NEW_ACCOUNT_MAIL_SENT", "The email with the information of the newly created account has been sent.");
define("MESSAGE_NEW_ACCOUNT_NOT_VERIFIED", "This account has not been verified. Please email your administrator to reset the password.");
define("MESSAGE_NEW_ACCOUNT_VERIFIED_SUCCESSFULLY", "Your account has been verified and is now permanent.");
define("MESSAGE_PASSWORD_BAD_CONFIRM", "Password and password repeat are not the same");
define("MESSAGE_PASSWORD_CHANGE_FAILED", "Sorry, your password changing failed.");
define("MESSAGE_PASSWORD_CHANGED_SUCCESSFULLY", "Password successfully changed!");
define("MESSAGE_PASSWORD_EMPTY", "Password field was empty");
define("MESSAGE_PASSWORD_RESET_MAIL_FAILED", "Password reset mail NOT successfully sent! Error: ");
define("MESSAGE_PASSWORD_RESET_MAIL_SUCCESSFULLY_SENT", "Password reset mail successfully sent!");
define("MESSAGE_PASSWORD_TOO_SHORT", "Password has a minimum length of 6 characters");
define("MESSAGE_PASSWORD_WRONG", "Wrong password. Try again.");
define("MESSAGE_PASSWORD_WRONG_3_TIMES", "You have entered an incorrect password 3 or more times already. Please wait 30 seconds to try again.");
define("MESSAGE_REGISTRATION_ACTIVATION_NOT_SUCCESSFUL", "Sorry, no such id/verification code combination here...");
define("MESSAGE_REGISTRATION_ACTIVATION_SUCCESSFUL", "Activation was successful! You can now log in!");
define("MESSAGE_REGISTRATION_FAILED", "Sorry, your registration failed. Please go back and try again.");
define("MESSAGE_RESET_ACCOUNT_CONFIRM", "Reset the following user's account: ");
define("MESSAGE_RESET_ACCOUNT_FAILED", "Sorry, resetting the account failed.  Please go back and try again.");
define("MESSAGE_RESET_ACCOUNT_MAIL_ERROR", "Sorry, we could not send an email with the account information.  The account has NOT been reset.");
define("MESSAGE_RESET_ACCOUNT_MAIL_NOT_SENT", "Reset account email was NOT successfully sent. Error: ");
define("MESSAGE_RESET_ACCOUNT_MAIL_SENT", "The email with the information for the reset account has been sent.");
define("MESSAGE_RESET_LINK_HAS_EXPIRED", "Your reset link has expired. Please use the reset link within one hour.");
define("MESSAGE_RESET_PERSONAL_ACCOUNT_ERROR", "Can't reset your own account!");
define("MESSAGE_USER_DOES_NOT_EXIST", "This user does not exist");
define("MESSAGE_USERNAME_BAD_LENGTH", "Username cannot be shorter than 2 or longer than 64 characters");
define("MESSAGE_USERNAME_CHANGE_FAILED", "Sorry, your chosen username renaming failed");
define("MESSAGE_USERNAME_CHANGED_SUCCESSFULLY", "Your username has been changed successfully. New username is ");
define("MESSAGE_USERNAME_EMPTY", "Username field was empty");
define("MESSAGE_USERNAME_EXISTS", "Sorry, that username is already taken. Please choose another one.");
define("MESSAGE_USERNAME_INVALID", "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters");
define("MESSAGE_USERNAME_SAME_LIKE_OLD_ONE", "Sorry, that username is the same as your current one. Please choose another one.");

// views
define("WORDING_ADMIN_ADDITION", " has been made an administrator.");
define("WORDING_ADMIN_EDIT_ACCOUNTS", "you are logged in as an admin and can edit account permissions here.");
define("WORDING_ADMIN_LINK", "Admin");
define("WORDING_BACK_TO_LOGIN", "Back to Login Page");
define("WORDING_CHANGE_EMAIL", "Change Email");
define("WORDING_CHANGE_PASSWORD", "Change Password");
define("WORDING_CHANGE_USERNAME", "Change Username");
define("WORDING_CURRENTLY", "currently");
define("WORDING_EDIT_USER_DATA", "Edit User Data");
define("WORDING_EDIT_YOUR_CREDENTIALS", "you are logged in and can edit your credentials here");
define("WORDING_FORGOT_MY_PASSWORD", "I forgot my password");
define("WORDING_LOGIN", "Log In");
define("WORDING_LOGOUT", "Log Out");
define("WORDING_NEW_EMAIL", "New Email");
define("WORDING_NEW_PASSWORD", "New Password");
define("WORDING_NEW_PASSWORD_REPEAT", "Repeat New Password");
define("WORDING_NEW_USERNAME", "New Username");
define("WORDING_OLD_PASSWORD", "OLD Password");
define("WORDING_PASSWORD", "Password");
define("WORDING_PROFILE_PICTURE", "Your profile picture (from gravatar):");
define("WORDING_REGISTER", "Register");
define("WORDING_REGISTER_NEW_ACCOUNT", "Create New Account");
define("WORDING_REGISTRATION_EMAIL", "User's Email (sets up an account with a user name and temporary password by emailing this information to the given address)");
define("WORDING_REGISTRATION_PASSWORD", "Password (min. 6 characters!)");
define("WORDING_REGISTRATION_PASSWORD_REPEAT", "Password repeat");
define("WORDING_REGISTRATION_USERNAME", "Username (only letters and numbers, 2 to 64 characters)");
define("WORDING_REMEMBER_ME", "Keep me logged in (for 2 weeks)");
define("WORDING_REQUEST_PASSWORD_RESET", "Request a password reset. Enter your username and you'll get a mail with instructions:");
define("WORDING_RESET_PASSWORD", "Reset my password");
define("WORDING_SUBMIT_NEW_PASSWORD", "Submit New Password");
define("WORDING_UPDATE", "Update Accounts");
define("WORDING_USERNAME", "Username");
define("WORDING_YOU_ARE_LOGGED_IN_AS", "You are logged in as ");
define("WORDING_VERIFY_ACCOUNT", "Verify Account");
define("WORDING_VERIFY_ACCOUNT_REQUEST", "please verify your account by filling out this form");
