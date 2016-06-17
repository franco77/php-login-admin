<?php
error_reporting(E_ALL);
ini_set("diplay_errors", 1);
/**
 * handles the user login/logout/session
 * @author Panique
 * @link http://www.php-login.net
 * @link https://github.com/panique/php-login-advanced/
 * @license http://opensource.org/licenses/MIT MIT License
 */
class Login
{
	/**
	 * @var object $db_connection The database connection
	 */
	private $db_connection = null;
	/**
	 * @var int $user_id The user's id
	 */
	private $user_id = null;
	/**
	 * @var string $user_name The user's name
	 */
	private $user_name = "";
	/**
	 * @var string $user_email The user's mail
	 */
	private $user_email = "";
	/**
	 * @var boolean $user_is_logged_in The user's login status
	 */
	private $user_is_logged_in = false;
	/**
	 * @var boolean $user_account_verfied determines account verification status
	 */
	private $user_account_expired = false;
	/**
	 * @var boolean $user_account_verfied determines account verification status
	 */
	private $user_is_verified = false;
	/**
	 * @var string $user_gravatar_image_url The user's gravatar profile pic url (or a default one)
	 */
	public $user_gravatar_image_url = "";
	/**
	 * @var string $user_gravatar_image_tag The user's gravatar profile pic url with <img ... /> around
	 */
	public $user_gravatar_image_tag = "";
	/**
	 * @var boolean $password_reset_link_is_valid Marker for view handling
	 */
	private $password_reset_link_is_valid  = false;
	/**
	 * @var boolean $password_reset_was_successful Marker for view handling
	 */
	private $password_reset_was_successful = false;
	/**
	 * @var array $errors Collection of error messages
	 */
	public $errors = array();
	/**
	 * @var array $messages Collection of success / neutral messages
	 */
	public  $messages = array();

	/**
	 * the function "__construct()" automatically starts whenever an object of this class is created,
	 * you know, when you do "$login = new Login();"
	 */
	public function __construct()
	{
		// create/read session
		session_start();

		// TODO: organize this stuff better and make the constructor very small
		// TODO: unite Login and Registration classes ?

		// check the possible login actions:
		// 1. logout (happen when user clicks logout button)
		// 2. login via session data (happens each time user opens a page on your php project AFTER he has successfully logged in via the login form)
		// 3. login via cookie
		// 4. login via post data, which means simply logging in via the login form. after the user has submit his login/password successfully, his
		//	logged-in-status is written into his session data on the server. this is the typical behaviour of common login scripts.

		// if user tried to log out
		if (isset($_GET["logout"])) {
			$this->doLogout();

		// if user has an active session on the server
		} elseif (!empty($_SESSION['user_name']) && ($_SESSION['user_logged_in'] == 1)) {
			$this->loginWithSessionData();

			// checking for form submit from editing screen
			// user try to change his username
			if (isset($_POST["user_edit_submit_user_name"])) {
				// function below uses use $_SESSION['user_id'] et $_SESSION['user_email']
				$this->editUserName($_POST['user_name']);
			// user try to change his email
			} elseif (isset($_POST["user_edit_submit_email"])) {
				// function below uses use $_SESSION['user_id'] et $_SESSION['user_email']
				$this->editUserEmail($_POST['user_email']);
			// user try to change his password
			} elseif (isset($_POST["user_edit_submit_password"])) {
				// function below uses $_SESSION['user_name'] and $_SESSION['user_id']
				$this->editUserPassword($_POST['user_password_old'], $_POST['user_password_new'], $_POST['user_password_repeat']);
			// user tries to change their name
			} elseif(isset($_POST['user_edit_submit_name'])) {
				$this->editName($_SESSION['user_name'], $_POST['fname'], $_POST['mname'], $_POST['lname'], $_POST['suffname']);
			// user tries to change their gender
			} elseif (isset($_POST['user_edit_submit_gender'])) {
				$this->editGender($_SESSION['user_name'], $_POST['gender']);
			// user tries to edit their DOB
			} elseif(isset($_POST['user_edit_submit_dob'])) {
				$this->editDOB($_SESSION['user_name'], $_POST['dob_month'], $_POST['dob_day'], $_POST['dob_year']);
			// user tries to edit their phone number
			} elseif(isset($_POST['user_edit_submit_phone_number'])) {
				$this->editPhoneNumber($_SESSION['user_name'], $_POST['area_code'], $_POST['region_code'], $_POST['last_four']);
			// user tries to edit their school
			} elseif(isset($_POST['user_edit_submit_school'])) {
				$this->editSchool($_SESSION['user_name'], $_POST['school']);
			// user tries to edit their level
			} elseif(isset($_POST['user_edit_submit_level'])) {
				$this->editLevel($_SESSION['user_name'], $_POST['level']);
			// user tries to verify their account
			} elseif (isset($_POST['user_verify_account'])) {
				// Only if both the password and the personal information have been changed will everything go through
				// Taking advantage of short-circuit here--> if the user fails the password, then the user info is never inserted with the second function
				if($this->addVerificationInfo($_SESSION['user_id'], $_SESSION['user_name'], $_POST['user_password_old'], $_POST['user_password_new'], $_POST['user_password_repeat'], $_POST['fname'], $_POST['mname'], $_POST['lname'], $_POST['suffname'], $_POST['gender'], $_POST['dob_month'], $_POST['dob_day'], $_POST['dob_year'], $_POST['area_code'], $_POST['region_code'], $_POST['last_four'], $_POST['school'], $_POST['level']) == true) {
					$this->messages[] = MESSAGE_NEW_ACCOUNT_VERIFIED_SUCCESSFULLY;
				} else {
					// Account not verified error only displayed if the user has not already verified
					if($this->isUserVerified() == false) {
						$this->errors[] = "Your account has not been verified.  <a href=\"/account/verify.php\">Please re-enter your information</a>.";
					}
				}
			}

		// login with cookie
		} elseif (isset($_COOKIE['rememberme'])) {
			$this->loginWithCookieData();

		// if user just submitted a login form
		} elseif (isset($_POST["login"])) {
			if (!isset($_POST['user_rememberme'])) {
				$_POST['user_rememberme'] = null;
			}
			$this->loginWithPostData($_POST['user_name'], $_POST['user_password'], $_POST['user_rememberme']);
		}

		// checking if user requested a password reset mail
		if (isset($_POST["request_password_reset"]) && isset($_POST['user_name'])) {
			$this->setPasswordResetDatabaseTokenAndSendMail($_POST['user_name']);
		} elseif (isset($_GET["user_name"]) && isset($_GET["verification_code"])) {
			$this->checkIfEmailVerificationCodeIsValid($_GET["user_name"], $_GET["verification_code"]);
		} elseif (isset($_POST["submit_new_password"])) {
			$this->editNewPassword($_POST['user_name'], $_POST['user_password_reset_hash'], $_POST['user_password_new'], $_POST['user_password_repeat']);
		}

		// get gravatar profile picture if user is logged in
		if ($this->isUserLoggedIn() == true) {
			$this->getGravatarImageUrl($this->user_email);
		}

		// Verifies status of account on each page with $login object
		// This is last so that it will pick up any recent changes
		if(isset($_SESSION['user_name'])) {
			$this->verifyAccount($_SESSION['user_name']);
		}
	}

	/**
	 * Checks if database connection is opened. If not, then this method tries to open it.
	 * @return bool Success status of the database connecting process
	 */
	private function databaseConnection()
	{
		// if connection already exists
		if ($this->db_connection != null) {
			return true;
		} else {
			try {
				// Generate a database connection, using the PDO connector
				// @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
				// Also important: We include the charset, as leaving it out seems to be a security issue:
				// @see http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers#Connecting_to_MySQL says:
				// "Adding the charset to the DSN is very important for security reasons,
				// most examples you'll see around leave it out. MAKE SURE TO INCLUDE THE CHARSET!"
				$this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
				return true;
			} catch (PDOException $e) {
				$this->errors[] = MESSAGE_DATABASE_ERROR . $e->getMessage();
			}
		}
		// default return
		return false;
	}

	/**
	 * Searches database and returns all user information
	 * Uses login_relation_id, so this can't be used unless the user has verified their account
	 */
	private function getUserData($user_name)
	{
		// if database connection opened
		if ($this->databaseConnection()) {
			// database query, getting all the info of the selected user
			// If user has updated their information, get all of it
			$query = "SELECT `personal_info`.*, `school_relation_table`.`school_id`, `school_relation_table`.`level_id`
						FROM (
							SELECT `person_table`.`fname`, `person_table`.`mname`, `person_table`.`lname`, `person_table`.`suffname`, `person_table`.`gender_id`, `person_table`.`dob`, `person_table`.`phone_number`, `login_info`.*
								FROM (
									SELECT `login_relation_table`.`login_relation_id`, `login_relation_table`.`person_id`, `users`.*
										FROM `users`
										LEFT JOIN `login_relation_table`
										ON `users`.`user_id` = `login_relation_table`.`user_id`
										WHERE `users`.`user_name` = :user_name
								) AS `login_info`
								LEFT JOIN `person_table`
								ON `person_table`.`person_id` = `login_info`.`person_id`
						) AS `personal_info`
						LEFT JOIN `school_relation_table`
						ON `school_relation_table`.`login_relation_id` = `personal_info`.`login_relation_id`;";
			$query_user = $this->db_connection->prepare($query);
			$query_user->bindValue(':user_name', $user_name, PDO::PARAM_STR);
			$query_user->execute();
			// get result row (as an object)
			return $query_user->fetchObject();
		} else {
			return false;
		}
	}

	/**
	 * Logs in with $_SESSION data.
	 * Technically we are already logged in at that point of time, as the $_SESSION values already exist.
	 */
	private function loginWithSessionData()
	{
		$this->user_name = $_SESSION['user_name'];
		$this->user_email = $_SESSION['user_email'];

		// set logged in status to true, because we just checked for this:
		// !empty($_SESSION['user_name']) && ($_SESSION['user_logged_in'] == 1)
		// when we called this method (in the constructor)
		$this->user_is_logged_in = true;
	}

	/**
	 * Logs in via the Cookie
	 * @return bool success state of cookie login
	 */
	private function loginWithCookieData()
	{
		if (isset($_COOKIE['rememberme'])) {
			// extract data from the cookie
			list ($user_id, $token, $hash) = explode(':', $_COOKIE['rememberme']);
			// check cookie hash validity
			if ($hash == hash('sha256', $user_id . ':' . $token . COOKIE_SECRET_KEY) && !empty($token)) {
				// cookie looks good, try to select corresponding user
				if ($this->databaseConnection()) {
					// get real token from database (and all other data)
					$sth = $this->db_connection->prepare("SELECT user_id, user_name, user_email FROM users WHERE user_id = :user_id
														AND user_rememberme_token = :user_rememberme_token AND user_rememberme_token IS NOT NULL
														AND user_password_change != 0");
					$sth->bindValue(':user_id', $user_id, PDO::PARAM_INT);
					$sth->bindValue(':user_rememberme_token', $token, PDO::PARAM_STR);
					$sth->execute();
					// get result row (as an object)
					$result_row = $sth->fetchObject();

					if (isset($result_row->user_id)) {
						// write user data into PHP SESSION [a file on your server]
						$_SESSION['user_id'] = $result_row->user_id;
						$_SESSION['user_name'] = $result_row->user_name;
						$_SESSION['user_email'] = $result_row->user_email;
						$_SESSION['user_logged_in'] = 1;

						// declare user id, set the login status to true
						$this->user_id = $result_row->user_id;
						$this->user_name = $result_row->user_name;
						$this->user_email = $result_row->user_email;
						$this->user_is_logged_in = true;

						// Cookie token usable only once
						$this->newRememberMeCookie();
						return true;
					}
				}
			}
			// A cookie has been used but is not valid... we delete it
			$this->deleteRememberMeCookie();
			$this->errors[] = MESSAGE_COOKIE_INVALID;
		}
		return false;
	}

	/**
	 * Logs in with the data provided in $_POST, coming from the login form
	 * @param $user_name
	 * @param $user_password
	 * @param $user_rememberme
	 */
	private function loginWithPostData($user_name, $user_password, $user_rememberme)
	{
		if (empty($user_name)) {
			$this->errors[] = MESSAGE_USERNAME_EMPTY;
		} else if (empty($user_password)) {
			$this->errors[] = MESSAGE_PASSWORD_EMPTY;

		// if POST data (from login form) contains non-empty user_name and non-empty user_password
		} else {
			// user can login with his username or his email address.
			// if user has not typed a valid email address, we try to identify him with his user_name
			if (!filter_var($user_name, FILTER_VALIDATE_EMAIL)) {
				// database query, getting all the info of the selected user
				$result_row = $this->getUserData(trim($user_name));

			// if user has typed a valid email address, we try to identify him with his user_email
			} else if ($this->databaseConnection()) {
				// database query, getting all the info of the selected user
				$query_user = $this->db_connection->prepare('SELECT * FROM users WHERE user_email = :user_email');
				$query_user->bindValue(':user_email', trim($user_name), PDO::PARAM_STR);
				$query_user->execute();
				// get result row (as an object)
				$result_row = $query_user->fetchObject();
			}

			// if this user not exists
			if (!isset($result_row->user_id)) {
				// was MESSAGE_USER_DOES_NOT_EXIST before, but has changed to MESSAGE_LOGIN_FAILED
				// to prevent potential attackers showing if the user exists
				
				/*** I am controlling this with a special modal, just uncomment if you want this to display ***/
				//$this->errors[] = MESSAGE_LOGIN_FAILED;
			} else if (($result_row->user_failed_logins >= 3) && ($result_row->user_last_failed_login > (time() - 30))) {
				$this->errors[] = MESSAGE_PASSWORD_WRONG_3_TIMES;
			// using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
			} else if (! password_verify($user_password, $result_row->user_password_hash)) {
				// increment the failed login counter for that user
				$sth = $this->db_connection->prepare('UPDATE users '
						. 'SET user_failed_logins = user_failed_logins+1, user_last_failed_login = :user_last_failed_login '
						. 'WHERE user_name = :user_name OR user_email = :user_name');
				$sth->execute(array(':user_name' => $user_name, ':user_last_failed_login' => time()));

				/*** I am controlling this with a special modal, just uncomment if you want this to display ***/
				//$this->errors[] = MESSAGE_PASSWORD_WRONG;
			// does the user have a valid account
			} elseif ($this->denyLogin($user_name) == true) {
				
				// write user data into PHP SESSION [a file on your server]
				$_SESSION['user_id'] = $result_row->user_id;
				$_SESSION['user_name'] = $result_row->user_name;
				$_SESSION['user_email'] = $result_row->user_email;
				$_SESSION['user_logged_in'] = 1;

				// declare user id, set the login status to true
				$this->user_id = $result_row->user_id;
				$this->user_name = $result_row->user_name;
				$this->user_email = $result_row->user_email;
				$this->user_is_logged_in = true;

				// reset the failed login counter for that user
				$sth = $this->db_connection->prepare('UPDATE users '
						. 'SET user_failed_logins = 0, user_last_failed_login = NULL '
						. 'WHERE user_id = :user_id AND user_failed_logins != 0');
				$sth->execute(array(':user_id' => $result_row->user_id));

				// if user has check the "remember me" checkbox, then generate token and write cookie
				if (isset($user_rememberme)) {
					$this->newRememberMeCookie();
				} else {
					// Reset remember-me token
					$this->deleteRememberMeCookie();
				}
				
				// OPTIONAL: recalculate the user's password hash
				// DELETE this if-block if you like, it only exists to recalculate users's hashes when you provide a cost factor,
				// by default the script will use a cost factor of 10 and never change it.
				// check if the have defined a cost factor in config/hashing.php
				if (defined('HASH_COST_FACTOR')) {
					// check if the hash needs to be rehashed
					if (password_needs_rehash($result_row->user_password_hash, PASSWORD_DEFAULT, array('cost' => HASH_COST_FACTOR))) {

						// calculate new hash with new cost factor
						$user_password_hash = password_hash($user_password, PASSWORD_DEFAULT, array('cost' => HASH_COST_FACTOR));

						// TODO: this should be put into another method !?
						$query_update = $this->db_connection->prepare('UPDATE users SET user_password_hash = :user_password_hash WHERE user_id = :user_id');
						$query_update->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
						$query_update->bindValue(':user_id', $result_row->user_id, PDO::PARAM_INT);
						$query_update->execute();

						if ($query_update->rowCount() == 0) {
							// writing new hash was successful. you should now output this to the user ;)
						} else {
							// writing new hash was NOT successful. you should now output this to the user ;)
						}
					}
				}
			}
		}
	}

	/**
	 * Create all data needed for remember me cookie connection on client and server side
	 */
	private function newRememberMeCookie()
	{
		// if database connection opened
		if ($this->databaseConnection()) {
			// generate 64 char random string and store it in current user data
			$random_token_string = hash('sha256', mt_rand());
			$sth = $this->db_connection->prepare("UPDATE users SET user_rememberme_token = :user_rememberme_token WHERE user_id = :user_id");
			$sth->execute(array(':user_rememberme_token' => $random_token_string, ':user_id' => $_SESSION['user_id']));

			// generate cookie string that consists of userid, randomstring and combined hash of both
			$cookie_string_first_part = $_SESSION['user_id'] . ':' . $random_token_string;
			$cookie_string_hash = hash('sha256', $cookie_string_first_part . COOKIE_SECRET_KEY);
			$cookie_string = $cookie_string_first_part . ':' . $cookie_string_hash;

			// set cookie
			setcookie('rememberme', $cookie_string, time() + COOKIE_RUNTIME, "/", COOKIE_DOMAIN);
		}
	}

	/**
	 * Delete all data needed for remember me cookie connection on client and server side
	 */
	private function deleteRememberMeCookie()
	{
		// if database connection opened
		if ($this->databaseConnection()) {
			// Reset rememberme token
			$sth = $this->db_connection->prepare("UPDATE users SET user_rememberme_token = NULL WHERE user_id = :user_id");
			$sth->execute(array(':user_id' => $_SESSION['user_id']));
		}

		// set the rememberme-cookie to ten years ago (3600sec * 365 days * 10).
		// that's obivously the best practice to kill a cookie via php
		// @see http://stackoverflow.com/a/686166/1114320
		setcookie('rememberme', false, time() - (3600 * 3650), '/', COOKIE_DOMAIN);
	}

	/**
	 * Perform the logout, resetting the session
	 */
	public function doLogout()
	{
		$this->deleteRememberMeCookie();

		$_SESSION = array();
		session_destroy();

		$this->user_is_logged_in = false;
		//$this->messages[] = MESSAGE_LOGGED_OUT;
	}

	/**
	 * Simply return the current state of the user's login
	 * @return bool user's login status
	 */
	public function isUserLoggedIn()
	{
		return $this->user_is_logged_in;
	}

	/**
	 * Add's personal information for verification of account
	 */
	public function addVerificationInfo($user_id, $user_name, $user_password_old, $user_password_new, $user_password_repeat, $fname, $mname, $lname, $suffname, $gender, $dob_month, $dob_day, $dob_year, $area_code, $region_code, $last_four, $school_id, $level_id)
	{
		// This is just checking to see if the account already has personal information in it
		$result_row = $this->getUserData($user_name);
		if($result_row->fname && $result_row->lname && $result_row->dob && $result_row->phone_number && $result_row->school_id && $result_row->level_id) {
			$this->messages[] = "This account already has the personal information set up.  You can edit it later in the \"Edit Profile\" tab.";

			// This account already has personal information, so this part of the verfication process is over
			return true;
		}

		if(empty($user_id)) {
			$this->error[] = "The user id is missing.";
		} elseif(empty($fname)) {
			$this->errors[] = "The first name is missing.";
		} elseif(!preg_match("/^[a-zA-Z]{2,64}$/", $fname)) {
			$this->errors[] = "The first name is not valid.";
		} elseif(empty($lname)) {
			$this->errors[] = "The last name is missing.";
		} elseif(!preg_match("/^[a-zA-Z]{2,64}$/", $lname)) {
			$this->errors[] = "The last name is not valid.";
		} elseif(empty($gender)) {
			$this->errors[] = "The gender is missing.";
		} elseif(empty($dob_month)) {
			$this->errors[] = "The date of birth month is missing.";
		} elseif(!preg_match("/^[0-9]{1,2}$/", $dob_month)) {
			$this->errors[] = "The date of birth month is not valid.";
		} elseif(empty($dob_day)) {
			$this->errors[] = "The date of birth day is missing.";
		} elseif(!preg_match("/^[0-9]{1,2}$/", $dob_day)) {
			$this->errors[] = "The date of birth day is not valid.";
		} elseif(empty($dob_year)) {
			$this->errors[] = "The date of birth year is missing.";
		} elseif(!preg_match("/^[0-9]{4}$/", $dob_year)) {
			$this->errors[] = "The date of birth year is not valid.";
		} elseif(empty($area_code)) {
			$this->errors[] = "The area code of the phone number is missing.";
		} elseif(!preg_match("/^[0-9]{3}$/", $area_code)) {
			$this->errors[] = "The area code for the phone number is not valid.";
		} elseif(empty($region_code)) {
			$this->errors[] = "The region code of the phone number is missing.";
		} elseif(!preg_match("/^[0-9]{3}$/", $region_code)) {
			$this->errors[] = "The region code for the phone number is not valid.";
		} elseif(empty($last_four)) {
			$this->errors[] = "The last four digits of the phone number are missing.";
		} elseif(!preg_match("/^[0-9]{4}$/", $last_four)) {
			$this->errors[] = "The last four digits of the phone number are not valid.";
		} elseif(empty($school_id)) {
			$this->errors[] = "The school of attendance is missing.";
		} elseif(empty($level_id)) {
			$this->errors[] = "The academic level is missing.";
		} elseif($this->databaseConnection()) {
			// Format DOB to form YYYY-MM-DD to be received by database
			$dob = $dob_year . "-" . $dob_month . "-" . $dob_day;

			// Format phone number to (###) ###-#### to be received by database
			$phone_number = "(" . $area_code . ") " . $region_code . "-" . $last_four;
			
			// Begin transaction for insertion so that everything gets inserted at once
			$this->db_connection->beginTransaction();

			// Puts appropriate info in person_table
			$query = "INSERT INTO `person_table` VALUES ('', :fname, :mname, :lname, :suffname, :gender, :dob, :phone_number);";
			$query_personal_info = $this->db_connection->prepare($query);
			$query_personal_info->bindValue(':fname', $fname, PDO::PARAM_STR);
			$query_personal_info->bindValue(':mname', $mname, PDO::PARAM_STR);
			$query_personal_info->bindValue(':lname', $lname, PDO::PARAM_STR);
			$query_personal_info->bindValue(':suffname', $suffname, PDO::PARAM_STR);
			$query_personal_info->bindValue(':gender', $gender, PDO::PARAM_STR);
			$query_personal_info->bindValue(':dob', $dob, PDO::PARAM_STR);
			$query_personal_info->bindValue(':phone_number', $phone_number, PDO::PARAM_STR);
			$query_personal_info->execute();

			// Grab person_id for next insert queries
			$person_id = $this->db_connection->lastInsertId();

			// Inserts appropriate id's into the login_relation_table to relate a user_id to the personal info
			$query = "INSERT INTO `login_relation_table` (`person_id`, `user_id`) VALUES (:person_id, :user_id);";
			$query_login_info = $this->db_connection->prepare($query);
			$query_login_info->bindValue(':person_id', $person_id, PDO::PARAM_STR);
			$query_login_info->bindValue(':user_id', $user_id, PDO::PARAM_STR);
			$query_login_info->execute();

			// Grab login_relation_id for future insert queries
			$login_relation_id = $this->db_connection->lastInsertId();

			// Inserts academic information into school_relation_table (remember that the user is submitting ID's through a dropdown)
			$query = "INSERT INTO `school_relation_table` (`login_relation_id`, `school_id`, `level_id`) VALUES (:login_relation_id, :school_id, :level_id);";
			$query_academic_info = $this->db_connection->prepare($query);
			$query_academic_info->bindValue(':login_relation_id',  $login_relation_id, PDO::PARAM_STR);
			$query_academic_info->bindValue(':school_id', $school_id, PDO::PARAM_STR);
			$query_academic_info->bindValue(':level_id', $level_id, PDO::PARAM_STR);

			// Queries for the id of a volunteer position
			$query = "SELECT * FROM `position_table` WHERE `position_name` = 'Volunteer';";
			$query_position = $this->db_connection->prepare($query);
			$query_position->execute();
			$result = $query_position->fetchObject();
			$volunteer_id = $result->position_id;
			$query_academic_info->execute();

			// Inserts user as a volunteer
			$query = "INSERT INTO `position_relation_table` (`login_relation_id`, `position_id`) VALUES (:login_relation_id, :position_id);";
			$query_position_info = $this->db_connection->prepare($query);
			$query_position_info->bindValue(':login_relation_id', $login_relation_id, PDO::PARAM_STR);
			$query_position_info->bindValue(':position_id', $volunteer_id, PDO::PARAM_STR);
			$query_position_info->execute();

			// If everything, was inserted correctly, attempt to modify password
			if($query_personal_info->rowCount() && $query_login_info->rowCount() && $query_academic_info->rowCount() && $query_position_info->rowCount()) {
				if($this->editUserPassword($user_password_old, $user_password_new, $user_password_repeat)) {
					// Commit all insertions to the database, as everything has worked at this point
					$this->db_connection->commit();

					$this->messages[] = "Personal information was updated.";
					return true;
				} else {
					// Password failed to update, remove personal information
					$this->db_connection->rollBack();

					// Letting the editUserPassword function do error reporting here (i.e. I don't need more error reporting)
				}
			} else {
				// Personal info has failed to be uploaded, undo personal information insert
				$this->db_connection->rollBack();

				$this->errors[] = "Personal information could not be stored in the database.  Account has not been verified.";
			}
		}

		// Default return
		return false;
	}

	/**
	 * Edit the user's name, provided in the editing form
	 */
	public function editUserName($user_name)
	{
		// prevent database flooding
		$user_name = substr(trim($user_name), 0, 64);

		if (!empty($user_name) && $user_name == $_SESSION['user_name']) {
			$this->errors[] = MESSAGE_USERNAME_SAME_LIKE_OLD_ONE;

		// username cannot be empty and must be azAZ09 and 2-64 characters
		// TODO: maybe this pattern should also be implemented in Registration.php (or other way round)
		} elseif (empty($user_name) || !preg_match("/^(?=.{2,64}$)[a-zA-Z][a-zA-Z0-9]*(?: [a-zA-Z0-9]+)*$/", $user_name)) {
			$this->errors[] = MESSAGE_USERNAME_INVALID;

		} else {
			// check if new username already exists
			$result_row = $this->getUserData($user_name);

			if (isset($result_row->user_id)) {
				$this->errors[] = MESSAGE_USERNAME_EXISTS;
			} else {
				// write user's new data into database
				$query_edit_user_name = $this->db_connection->prepare('UPDATE users SET user_name = :user_name WHERE user_id = :user_id');
				$query_edit_user_name->bindValue(':user_name', $user_name, PDO::PARAM_STR);
				$query_edit_user_name->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
				$query_edit_user_name->execute();

				if ($query_edit_user_name->rowCount()) {
					$_SESSION['user_name'] = $user_name;
					$this->messages[] = MESSAGE_USERNAME_CHANGED_SUCCESSFULLY . $user_name;
				} else {
					$this->errors[] = MESSAGE_USERNAME_CHANGE_FAILED;
				}
			}
		}
	}

	/**
	 * Edit the user's email, provided in the editing form
	 */
	public function editUserEmail($user_email)
	{
		// prevent database flooding
		$user_email = substr(trim($user_email), 0, 64);

		if (!empty($user_email) && $user_email == $_SESSION["user_email"]) {
			$this->errors[] = MESSAGE_EMAIL_SAME_LIKE_OLD_ONE;
		// user mail cannot be empty and must be in email format
		} elseif (empty($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
			$this->errors[] = MESSAGE_EMAIL_INVALID;

		} else if ($this->databaseConnection()) {
			// check if new email already exists
			$query_user = $this->db_connection->prepare('SELECT * FROM users WHERE user_email = :user_email');
			$query_user->bindValue(':user_email', $user_email, PDO::PARAM_STR);
			$query_user->execute();
			// get result row (as an object)
			$result_row = $query_user->fetchObject();

			// if this email exists
			if (isset($result_row->user_id)) {
				$this->errors[] = MESSAGE_EMAIL_ALREADY_EXISTS;
			} else {
				// write users new data into database
				$query_edit_user_email = $this->db_connection->prepare('UPDATE users SET user_email = :user_email WHERE user_id = :user_id');
				$query_edit_user_email->bindValue(':user_email', $user_email, PDO::PARAM_STR);
				$query_edit_user_email->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
				$query_edit_user_email->execute();

				if ($query_edit_user_email->rowCount()) {
					$_SESSION['user_email'] = $user_email;
					$this->messages[] = MESSAGE_EMAIL_CHANGED_SUCCESSFULLY . $user_email;
				} else {
					$this->errors[] = MESSAGE_EMAIL_CHANGE_FAILED;
				}
			}
		}
	}

	/**
	 * Edit the user's password, provided in the editing form
	 */
	public function editUserPassword($user_password_old, $user_password_new, $user_password_repeat)
	{
		if (empty($user_password_new) || empty($user_password_repeat) || empty($user_password_old)) {
			$this->errors[] = MESSAGE_PASSWORD_EMPTY;
		// is the repeat password identical to password
		} elseif ($user_password_new !== $user_password_repeat) {
			$this->errors[] = MESSAGE_PASSWORD_BAD_CONFIRM;
		// password need to have a minimum length of 6 characters
		} elseif (strlen($user_password_new) < 6) {
			$this->errors[] = MESSAGE_PASSWORD_TOO_SHORT;

		// all the above tests are ok
		} else {
			// database query, getting hash of currently logged in user (to check with just provided password)
			$result_row = $this->getUserData($_SESSION['user_name']);

			// if this user exists
			if (isset($result_row->user_password_hash)) {

				// using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
				if (password_verify($user_password_old, $result_row->user_password_hash)) {

					// now it gets a little bit crazy: check if we have a constant HASH_COST_FACTOR defined (in config/hashing.php),
					// if so: put the value into $hash_cost_factor, if not, make $hash_cost_factor = null
					$hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

					// crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
					// the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
					// compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
					// want the parameter: as an array with, currently only used with 'cost' => XX.
					$user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

					// write users new hash into database
					$query_update = $this->db_connection->prepare('UPDATE users SET user_password_hash = :user_password_hash WHERE user_id = :user_id');
					$query_update->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
					$query_update->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
					$query_update->execute();

					// check if exactly one row was successfully changed:
					if ($query_update->rowCount()) {
						$this->messages[] = MESSAGE_PASSWORD_CHANGED_SUCCESSFULLY;
						
						// if account has not already been verified
						if($result_row->user_password_change == 0) {
							// verify account after password has been changed
							// since this is the first time, this is a change from the random password
							$query = "UPDATE `users` SET `user_password_change` = 1 WHERE `user_name` = :user_name;";
							$query_verify_account = $this->db_connection->prepare($query);
							$query_verify_account->bindValue(":user_name", $_SESSION['user_name'], PDO::PARAM_STR);
							$query_verify_account->execute();

							if($query_verify_account->rowCount()) {
								// Allows detection of password change
								return true;
							} else {
								// Password successfully changed, but account not verified due to database error
								$this->errors[] = MESSAGE_DATABASE_ERROR;
							}
						} else {
							// Allows the function to return true even if it's not user's first password change but the database was still updated
							return true;
						}
					} else {
						$this->errors[] = MESSAGE_PASSWORD_CHANGE_FAILED;
					}
				} else {
					$this->errors[] = MESSAGE_OLD_PASSWORD_WRONG;
				}
			} else {
				$this->errors[] = MESSAGE_USER_DOES_NOT_EXIST;
			}
		}

		// Default return (function only returns true if the password was updated)
		return false;
	}

	public function editName($user_name, $fname, $mname, $lname, $suffname)
	{
		if(empty($user_name)) {
			$this->errors[] = MESSAGE_USERNAME_EMPTY;
		} elseif(empty($fname)) {
			$this->errors[] = "The first name is missing";
		} elseif(!preg_match("/^[a-zA-Z]{2,64}$/", $fname)) {
			$this->errors[] = "The first name is not valid";
		} elseif(!preg_match("/^[a-zA-Z]{0,64}$/", $mname)) {
			$this->errors[] = "The middle name is not valid";
		} elseif(empty($lname)) {
			$this->errors[] = "The last name is missing";
		} elseif(!preg_match("/^[a-zA-Z]{2,64}$/", $lname)) {
			$this->errors[] = "The last name is not valid";
		} elseif(!preg_match("/^[a-zA-Z\.]{0,64}$/", $suffname)) {
			$this->errors[] = "The suffix is not valid";
		} else {
			// Everything is hunky dory at this point
			// Acquiring person_id from the user data
			$result_row = $this->getUserData($user_name);

			// Updating the name in the database
			$query = "UPDATE `person_table` SET `fname` = :fname, `mname` = :mname, `lname` = :lname, `suffname` = :suffname WHERE `person_id` = :person_id;";
			$query_update_name = $this->db_connection->prepare($query);
			$query_update_name->bindValue(':fname', $fname, PDO::PARAM_STR);
			$query_update_name->bindValue(':mname', $mname, PDO::PARAM_STR);
			$query_update_name->bindValue(':lname', $lname, PDO::PARAM_STR);
			$query_update_name->bindValue(':suffname', $suffname, PDO::PARAM_STR);
			$query_update_name->bindValue(':person_id', $result_row->person_id, PDO::PARAM_STR);
			$query_update_name->execute();

			// Checking to make sure that the database was actually updated
			if($query_update_name->rowCount()) {
				$this->messages[] = "Name was successfully updated";
			} else {
				$this->errors[] = "Name was unable to be updated";
			}
		}
	}

	public function editGender($user_name, $gender_id)
	{
		if(empty($user_name)) {
			$this->errors[] = MESSAGE_USERNAME_EMPTY;
		} elseif(empty($gender_id)) {
			$this->errors[] = "The gender is empty";
		} else {
			// Everything is hunky dory at this point
			// Acquiring user information for the purpose of getting the person_id
			$result_row = $this->getUserData($user_name);

			// Updating gender in the database
			$query = "UPDATE `person_table` SET `gender_id` = :gender_id WHERE `person_id` = :person_id;";
			$query_update_gender = $this->db_connection->prepare($query);
			$query_update_gender->bindValue(':gender_id', $gender_id, PDO::PARAM_STR);
			$query_update_gender->bindValue(':person_id', $result_row->person_id, PDO::PARAM_STR);
			$query_update_gender->execute();

			// Checking to make sure that the database was actually updated
			if($query_update_gender->rowCount()) {
				$this->messages[] = "Gender was successfully updated";
			} else {
				$this->errors[] = "Gender was unable to be updated";
			}

		}
	}

	public function editDOB($user_name, $dob_month, $dob_day, $dob_year)
	{
		if(empty($user_name)) {
			$this->errors[] = MESSAGE_USERNAME_EMPTY;
		} elseif(empty($dob_month)) {
			$this->errors[] = "The date of birth month is missing";
		} elseif(!preg_match("/^[0-9]{1,2}$/", $dob_month)) {
			$this->errors[] = "The date of birth month is not valid";
		} elseif(empty($dob_day)) {
			$this->errors[] = "The date of birth day is missing";
		} elseif(!preg_match("/^[0-9]{1,2}$/", $dob_day)) {
			$this->errors[] = "The date of birth day is not valid";
		} elseif(empty($dob_year)) {
			$this->errors[] = "The date of birth year is missing";
		} elseif(!preg_match("/^[0-9]{4}$/", $dob_year)) {
			$this->errors[] = "The date of birth year is not valid";
		} else {
			// Everything is hunky dory at this point
			// Putting dob_month, dob_day, and dob_year into format for MySQL
			$dob = $dob_year . "-" . $dob_month . "-" . $dob_day;

			// getting the person_id
			$result_row = $this->getUserData($user_name);

			// Updating DOB in the database
			$query = "UPDATE `person_table` SET `dob` = :dob WHERE `person_id` = :person_id;";
			$query_update_dob = $this->db_connection->prepare($query);
			$query_update_dob->bindValue(':dob', $dob, PDO::PARAM_STR);
			$query_update_dob->bindValue(':person_id', $result_row->person_id, PDO::PARAM_STR);
			$query_update_dob->execute();

			// Checking to see if the DOB was actually updated
			if($query_update_dob->rowCount()) {
				$this->messages[] = "Date of birth was successfully updated";
			} else {
				$this->errors[] = "Date of birth was unable to be updated";
			}
		}
	}

	public function editPhoneNumber($user_name, $area_code, $region_code, $last_four)
	{
		if(empty($user_name)) {
			$this->errors[] = MESSAGE_USERNAME_EMPTY;
		} elseif(empty($area_code)) {
			$this->errors[] = "The area code of the phone number is missing";
		} elseif(!preg_match("/^[0-9]{3}$/", $area_code)) {
			$this->errors[] = "The area code for the phone nubmer is not valid";
		} elseif(empty($region_code)) {
			$this->errors[] = "The region code of the phone number is missing";
		} elseif(!preg_match("/^[0-9]{3}$/", $region_code)) {
			$this->errors[] = "The region code for the phone nubmer is not valid";
		} elseif(empty($last_four)) {
			$this->errors[] = "The last four digits of the phone number are missing";
		} elseif(!preg_match("/^[0-9]{4}$/", $last_four)) {
			$this->errors[] = "The last four digits of the phone nubmer is not valid";
		} else {
			// Everything is hunky dory at this point
			// Putting phone number into proper format
			$phone_number = "(" . $area_code . ") " . $region_code . "-" . $last_four;

			// Getting person_id from user data
			$result_row = $this->getUserData($user_name);

			// Updating database with new phone number
			$query = "UPDATE `person_table` SET `phone_number` = :phone_number WHERE `person_id` = :person_id;";
			$query_update_phone_number = $this->db_connection->prepare($query);
			$query_update_phone_number->bindValue(':phone_number', $phone_number, PDO::PARAM_STR);
			$query_update_phone_number->bindValue(':person_id', $result_row->person_id, PDO::PARAM_STR);
			$query_update_phone_number->execute();

			// Checking to make sure that the phone number was updated
			if($query_update_phone_number->rowCount()) {
				$this->messages[] = "Phone number was successfully updated";
			} else {
				$this->errors[] = "Phone number was unable to updated";
			}
		}
	}

	public function editSchool($user_name, $school_id)
	{
		if(empty($user_name)) {
			$this->errors[] = MESSAGE_USERNAME_EMPTY;
		} elseif(empty($school_id)) {
			$this->errors[] = "The school is blank";
		} else {
			// Everything is hunky dory at this point
			// Getting person_id from user data
			$result_row = $this->getUserData($user_name);

			// Updating database with new school
			$query = "UPDATE `school_relation_table` SET `school_id` = :school_id WHERE `login_relation_id` = :login_relation_id;";
			$query_update_school = $this->db_connection->prepare($query);
			$query_update_school->bindValue(':school_id', $school_id, PDO::PARAM_STR);
			$query_update_school->bindValue(':login_relation_id', $result_row->login_relation_id, PDO::PARAM_STR);
			$query_update_school->execute();

			// Checking to make sure that the school was updated
			if($query_update_school->rowCount()) {
				$this->messages[] = "School was successfully updated";
			} else {
				$this->errors[] = "School was unable to be updated";
			}
		}
	}

	public function editLevel($user_name, $level_id)
	{
		if(empty($user_name)) {
			$this->errors[] = MESSAGE_USERNAME_EMPTY;
		} elseif(empty($level_id)) {
			$this->errors[] = "The level is blank";
		} else {
			// Everything is hunky dory at this point
			// Getting person_id from user data
			$result_row = $this->getUserData($user_name);

			// Updating database with new level
			$query = "UPDATE `school_relation_table` SET `level_id` = :level_id WHERE `login_relation_id` = :login_relation_id;";
			$query_update_level = $this->db_connection->prepare($query);
			$query_update_level->bindValue(':level_id', $level_id, PDO::PARAM_STR);
			$query_update_level->bindValue(':login_relation_id', $result_row->login_relation_id, PDO::PARAM_STR);
			$query_update_level->execute();

			// Checking to make sure that the school was updated
			if($query_update_level->rowCount()) {
				$this->messages[] = "Level was successfully updated";
			} else {
				$this->errors[] = "Level was unable to be updated";
			}
		}
	}

	/**
	 * Checks to see that user's account is valid for login based on a password change and addition of personal information
	 */
	private function verifyAccount($user_name)
	{
		// Note that there is no check for a blank user_name as this function shouldn't ever be filled with
		// a blank user name since it is only to be used internally

		// Get user info on selected user
		$result_row = $this->getUserData($user_name);
		// Determine if the account is valid
		if($result_row->user_password_change == 0 || !$result_row->fname || !$result_row->lname || !$result_row->gender_id || !$result_row->dob || !$result_row->phone_number || !$result_row->school_id || !$result_row->level_id) {
			// User has not changed password and account is not valid
			return false;
		} else {
			// User has changed password and filled out the appropriate personal info and accound it valid
			$this->user_is_verified = true;
		}
		// Default return
		return true;
	}


	/**
	 * Denies login based on verification and time frame
	 * Login is denied if
	 ** verifyAccount function is false
	 ** Time is greater than three days since account setup
	 */
	private function denyLogin($user_name)
	{
		// Get all info on selected user (this is done for the sign_up date)
		$result_row = $this->getUserData($user_name);
		
		// Place date of account creation and current date in DateTime objects in order to calculate the difference between the two
		$sign_up_date = new DateTime($result_row->user_registration_datetime);
		$current_date = new DateTime("now");
		
		// Calculates the difference between the account creation date and the current date
		$date_interval = $current_date->diff($sign_up_date);
		
		// Determine if the account is valid or within the grace period
		if($this->verifyAccount($user_name) == false && $date_interval->format("%d")>= 3) {
			// User has not changed password and password is more than three days old
			$this->user_account_expired = true;
			$this->errors[] = MESSAGE_INVALID_ACCOUNT;
			return false;
		} elseif($this->verifyAccount($user_name) == false && $date_interval->format("%d") < 3) {
			// Password is not greater than three days old but user has not changed the password
			// Set a $_SESSION variable for display to user if necessary
			$_SESSION['verify_time'] = (3 - $date_interval->format("%d"));
		}

		// default return
		return true;
	}

	public function isAccountExpired()
	{
		return $this->user_account_expired;
	}

	/**
	 * Returns the verfication status of the user's account
	 */
	public function isUserVerified()
	{
		return $this->user_is_verified;
	}
	
	/**
	 * Sets a random token into the database (that will verify the user when he/she comes back via the link
	 * in the email) and sends the according email.
	 */
	public function setPasswordResetDatabaseTokenAndSendMail($user_name)
	{
		$user_name = trim($user_name);

		if (empty($user_name)) {
			$this->errors[] = MESSAGE_USERNAME_EMPTY;

		} else {
			// generate timestamp (to see when exactly the user (or an attacker) requested the password reset mail)
			// btw this is an integer ;)
			$temporary_timestamp = time();
			// generate random hash for email password reset verification (40 char string)
			$user_password_reset_hash = sha1(uniqid(mt_rand(), true));
			// database query, getting all the info of the selected user
			$result_row = $this->getUserData($user_name);

			// if this user exists
			if (isset($result_row->user_id)) {
			
				// if the random password has been changed to verify the account
				// no one is logged in, so use verify_account here rather than isUserVerified
				if($this->verifyAccount($user_name) == true) {
					// database query:
					$query_update = $this->db_connection->prepare('UPDATE users SET user_password_reset_hash = :user_password_reset_hash,
																   user_password_reset_timestamp = :user_password_reset_timestamp
																   WHERE user_name = :user_name');
					$query_update->bindValue(':user_password_reset_hash', $user_password_reset_hash, PDO::PARAM_STR);
					$query_update->bindValue(':user_password_reset_timestamp', $temporary_timestamp, PDO::PARAM_INT);
					$query_update->bindValue(':user_name', $user_name, PDO::PARAM_STR);
					$query_update->execute();
	
					// check if exactly one row was successfully changed:
					if ($query_update->rowCount() == 1) {
						// send a mail to the user, containing a link with that token hash string
						$this->sendPasswordResetMail($user_name, $result_row->user_email, $user_password_reset_hash);
						return true;
					} else {
						$this->errors[] = MESSAGE_DATABASE_ERROR;
					}
				} else {
					$this->errors[] = MESSAGE_NEW_ACCOUNT_NOT_VERIFIED;
				}
			} else {
				$this->errors[] = MESSAGE_USER_DOES_NOT_EXIST;
			}
		}
		// return false (this method only returns true when the database entry has been set successfully)
		return false;
	}

	/**
	 * Sends the password-reset-email.
	 */
	public function sendPasswordResetMail($user_name, $user_email, $user_password_reset_hash)
	{
		$mail = new PHPMailer;

		// please look into the config/config.php for much more info on how to use this!
		// use SMTP or use mail()
		if (EMAIL_USE_SMTP) {
			// Set mailer to use SMTP
			$mail->IsSMTP();
			//useful for debugging, shows full SMTP errors
			//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
			// Enable SMTP authentication
			$mail->SMTPAuth = EMAIL_SMTP_AUTH;
			// Enable encryption, usually SSL/TLS
			if (defined(EMAIL_SMTP_ENCRYPTION)) {
				$mail->SMTPSecure = EMAIL_SMTP_ENCRYPTION;
			}
			// Specify host server
			$mail->Host = EMAIL_SMTP_HOST;
			$mail->Username = EMAIL_SMTP_USERNAME;
			$mail->Password = EMAIL_SMTP_PASSWORD;
			$mail->Port = EMAIL_SMTP_PORT;
		} else {
			$mail->IsMail();
		}

		$mail->From = EMAIL_PASSWORDRESET_FROM;
		$mail->FromName = EMAIL_PASSWORDRESET_FROM_NAME;
		$mail->AddAddress($user_email);
		$mail->Subject = EMAIL_PASSWORDRESET_SUBJECT;

		$link	= EMAIL_PASSWORDRESET_URL.'?user_name='.urlencode($user_name).'&verification_code='.urlencode($user_password_reset_hash);
		$mail->Body = EMAIL_PASSWORDRESET_CONTENT . ' ' . $link;

		if(!$mail->Send()) {
			$this->errors[] = MESSAGE_PASSWORD_RESET_MAIL_FAILED . $mail->ErrorInfo;
			return false;
		} else {
			$this->messages[] = MESSAGE_PASSWORD_RESET_MAIL_SUCCESSFULLY_SENT;
			return true;
		}
	}

	/**
	 * Checks if the verification string in the account verification mail is valid and matches to the user.
	 */
	public function checkIfEmailVerificationCodeIsValid($user_name, $verification_code)
	{
		$user_name = trim($user_name);

		if (empty($user_name) || empty($verification_code)) {
			$this->errors[] = MESSAGE_LINK_PARAMETER_EMPTY;
		} else {
			// database query, getting all the info of the selected user
			$result_row = $this->getUserData($user_name);

			// if this user exists and have the same hash in database
			if (isset($result_row->user_id) && $result_row->user_password_reset_hash == $verification_code) {

				$timestamp_one_hour_ago = time() - 3600; // 3600 seconds are 1 hour

				if ($result_row->user_password_reset_timestamp > $timestamp_one_hour_ago) {
					// set the marker to true, making it possible to show the password reset edit form view
					$this->password_reset_link_is_valid = true;
				} else {
					$this->errors[] = MESSAGE_RESET_LINK_HAS_EXPIRED;
				}
			} else {
				$this->errors[] = MESSAGE_USER_DOES_NOT_EXIST;
			}
		}
	}

	/**
	 * Checks and writes the new password.
	 */
	public function editNewPassword($user_name, $user_password_reset_hash, $user_password_new, $user_password_repeat)
	{
		// TODO: timestamp!
		$user_name = trim($user_name);

		if (empty($user_name) || empty($user_password_reset_hash) || empty($user_password_new) || empty($user_password_repeat)) {
			$this->errors[] = MESSAGE_PASSWORD_EMPTY;
		// is the repeat password identical to password
		} else if ($user_password_new !== $user_password_repeat) {
			$this->errors[] = MESSAGE_PASSWORD_BAD_CONFIRM;
		// password need to have a minimum length of 6 characters
		} else if (strlen($user_password_new) < 6) {
			$this->errors[] = MESSAGE_PASSWORD_TOO_SHORT;
		// if database connection opened
		} else if ($this->databaseConnection()) {
			// now it gets a little bit crazy: check if we have a constant HASH_COST_FACTOR defined (in config/hashing.php),
			// if so: put the value into $hash_cost_factor, if not, make $hash_cost_factor = null
			$hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

			// crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
			// the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
			// compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
			// want the parameter: as an array with, currently only used with 'cost' => XX.
			$user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

			// write users new hash into database
			$query_update = $this->db_connection->prepare('UPDATE users SET user_password_hash = :user_password_hash,
														   user_password_reset_hash = NULL, user_password_reset_timestamp = NULL
														   WHERE user_name = :user_name AND user_password_reset_hash = :user_password_reset_hash');
			$query_update->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
			$query_update->bindValue(':user_password_reset_hash', $user_password_reset_hash, PDO::PARAM_STR);
			$query_update->bindValue(':user_name', $user_name, PDO::PARAM_STR);
			$query_update->execute();

			// check if exactly one row was successfully changed:
			if ($query_update->rowCount() == 1) {
				$this->password_reset_was_successful = true;
				$this->messages[] = MESSAGE_PASSWORD_CHANGED_SUCCESSFULLY;
			} else {
				$this->errors[] = MESSAGE_PASSWORD_CHANGE_FAILED;
			}
		}
	}

	/**
	 * Gets the success state of the password-reset-link-validation.
	 * TODO: should be more like getPasswordResetLinkValidationStatus
	 * @return boolean
	 */
	public function passwordResetLinkIsValid()
	{
		return $this->password_reset_link_is_valid;
	}

	/**
	 * Gets the success state of the password-reset action.
	 * TODO: should be more like getPasswordResetSuccessStatus
	 * @return boolean
	 */
	public function passwordResetWasSuccessful()
	{
		return $this->password_reset_was_successful;
	}

	/**
	 * Gets the username
	 * @return string username
	 */
	public function getUsername()
	{
		return $this->user_name;
	}

	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 * Gravatar is the #1 (free) provider for email address based global avatar hosting.
	 * The URL (or image) returns always a .jpg file !
	 * For deeper info on the different parameter possibilities:
	 * @see http://de.gravatar.com/site/implement/images/
	 *
	 * @param string $email The email address
	 * @param string $s Size in pixels, defaults to 50px [ 1 - 2048 ]
	 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 * @source http://gravatar.com/site/implement/images/php/
	 */
	public function getGravatarImageUrl($email, $s = 50, $d = 'mm', $r = 'g', $atts = array() )
	{
		$url = 'http://www.gravatar.com/avatar/';
		$url .= md5(strtolower(trim($email)));
		$url .= "?s=$s&d=$d&r=$r&f=y";

		// the image url (on gravatarr servers), will return in something like
		// http://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50?s=80&d=mm&r=g
		// note: the url does NOT have something like .jpg
		$this->user_gravatar_image_url = $url;

		// build img tag around
		$url = '<img src="' . $url . '"';
		foreach ($atts as $key => $val)
			$url .= ' ' . $key . '="' . $val . '"';
		$url .= ' />';

		// the image url like above but with an additional <img src .. /> around
		$this->user_gravatar_image_tag = $url;
	}
}
