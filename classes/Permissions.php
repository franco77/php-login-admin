<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/**
 * Checks and modifies the permissions of users
 * This object uses the $_SESSION variables from the Login object (it is dependent on the Login object to work)
 * @author tikenn
 * @license http://opensource.org/licenses/MIT MIT License
 */

class Permissions
{
	/**
	 * @var object $db_connection The database connection
	 */
	private $db_connection = null;
	/**
	 * @var boolean $user_is_admin The user's admin status
	 */
	private $user_is_admin = false;
	/**
	 * @var boolean $confirm_action_prompt Checks whether user has tried to delete user(s)
	 */
	public $confirm_action_prompt = false;
	/**
	 * @var array $errors Collection of error messages
	 */
	public $errors = array();
	/**
	 * @var array $messages Collection of success / neutral messages
	 */
	public $messages = array();
	
	/**
	 * the function "__construct()" automatically starts whenever an object of this class is created,
	 * you know, when you do "$login = new Login();"
	 */
	public function __construct()
	{
		// This statement only allows this object to work if a user is logged in
		if(!empty($_SESSION['user_name']) && ($_SESSION['user_logged_in'] == 1))
		{
			// Automatically checks admin status of user on pages supplied with the Permissions object
			$this->checkAdminStatus($_SESSION['user_name']);
			
			// Checks to see if admin has chosen to update users
			if(isset($_POST['update_accounts']))
			{
				// Updates admin status each time
					// Runs every time because AJAX might mean that "all" admins are removed or that $_POST['admin'] never gets set
					// This isn't a problem though because the current admins are just resubmitted (also, there is no update to the database if no change needs to be made)
				$this->modifyAdminStatusConfirm($_POST['admin'], $_POST['admin_check_submit']);
				
				// Prompts for resetting accounts
				// Receives 'reset_account' array from html
				if(isset($_POST['reset_account']))
				{
					$this->resetAccountConfirm($_POST['reset_account']);
				}
				
				// Starts deletion process
				if(isset($_POST['delete_account']))
				{
					// Stores delete_array in Session variable to keep it between page refreshes
					$this->deleteAccountConfirm($_POST['delete_account']);
				}
			}
			
			// Shows confirmation messages if they have been triggered (delete, reset, permission changes)
			// This is only triggered with a confirmation
			// Outside 'update' POST as page will refresh due to use of PHP
			elseif(isset($_POST['confirm_action']))
			{
				if(isset($_SESSION['admin_delete_list']) && isset($_SESSION['admin_add_list']))
				{
					$this->modifyAdminStatus($_SESSION['admin_delete_list'], $_SESSION['admin_add_list']);
				}

				if(isset($_SESSION['delete_users_array']))
				{
					foreach($_SESSION['delete_users_array'] as $delete_id)
					{
						$this->deleteAccount($delete_id);
					}
				}
				
				if(isset($_SESSION['reset_user_accounts_array']))
				{
					foreach($_SESSION['reset_user_accounts_array'] as $reset_id)
					{
						$this->resetAccount($reset_id);
					}
				}
			}
			elseif(isset($_POST['dismiss_action']))
			{
				// Unsets all session variables so that a second action won't trigger them
				unset($_SESSION['admin_delete_list']);
				unset($_SESSION['admin_add_list']);
				unset($_SESSION['delete_users_array']);
				unset($_SESSION['reset_user_accounts_array']);
			}
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
	 * Search into database for the user data of user_name specified as parameter
	 * @return user data as an object if existing user
	 * @return false if user_name is not found in the database
	 * TODO: @devplanete This returns two different types. Maybe this is valid, but it feels bad. We should rework this.
	 * TODO: @devplanete After some resarch I'm VERY sure that this is not good coding style! Please fix this.
	 */
	private function getUserData($user_name)
	{
		// if database connection opened
		if($this->databaseConnection()) {
			// database query, getting all the info of the selected user
			$query_user = $this->db_connection->prepare('SELECT * FROM users WHERE user_name = :user_name');
			$query_user->bindValue(':user_name', $user_name, PDO::PARAM_STR);
			$query_user->execute();
			// get result row (as an object)
			return $query_user->fetchObject();
		} else {
			return false;
		}
	}

	/**
	 * Searches database for user data by user ID
	 * @return user data as an object if user exists
	 * @return false if user ID does not exist
	 */
	private function getUserDataById($user_id)
	{
		if($this->databaseConnection())
		{
			// Database query selecting all user info using chosen user_id
			$query = "SELECT * FROM `users` WHERE `user_id` = :user_id;";
			$query_user_id = $this->db_connection->prepare($query);
			$query_user_id->bindValue(":user_id", $user_id, PDO::PARAM_STR);
			$query_user_id->execute();
			// get result row (as an object)
			return $query_user_id->fetchObject();
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Searches database and returns all user information
	 * Uses login_relation_id, so this can't be used unless the user has verified their account
	 */
	private function getAllUserData($user_name)
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
										FROM `login_relation_table`
										JOIN `users`
										ON `users`.`user_id` = `login_relation_table`.`user_id`
										WHERE `users`.`user_name` = :user_name
								) AS `login_info`
								JOIN `person_table`
								ON `person_table`.`person_id` = `login_info`.`person_id`
						) AS `personal_info`
						JOIN `school_relation_table`
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
	 * Searches database and returns all id's associated with selected user
	 * Uses login_relation_id, so this can't be used unless the user has verified their account
	 */
	private function getAllUserIds($user_id)
	{
		// if database connection opened
		if($this->databaseConnection()) {
			// Query for all id's of user
			// Note that this will return nothing if the user has not verified their account
			$query = "SELECT `academic_info`.*, `position_relation_table`.`position_relation_id`
						FROM (
							SELECT `personal_info`.*, `school_relation_table`.`school_relation_id`, `school_relation_table`.`school_id`, `school_relation_table`.`level_id`
								FROM (
									SELECT `users`.`user_id`, `login_relation_table`.`login_relation_id`, `login_relation_table`.`person_id`
										FROM `users`
										INNER JOIN `login_relation_table`
										ON `login_relation_table`.`user_id` = `users`.`user_id`
								) AS `personal_info`
								INNER JOIN `school_relation_table`
								ON `personal_info`.`login_relation_id` = `school_relation_table`.`login_relation_id`
						) AS `academic_info`
						INNER JOIN `position_relation_table`
						ON `academic_info`.`login_relation_id` = `position_relation_table`.`login_relation_id`
						WHERE `academic_info`.`user_id` = :user_id";
			$query_ids = $this->db_connection->prepare($query);
			$query_ids->bindValue(':user_id', $user_id, PDO::PARAM_STR);
			$query_ids->execute();
			return $query_ids->fetchObject();
		} else {
			return false;
		}
	}

	/**
	 * Returns user data with limitations (for AJAX)
	 * This is built for admin management of the account
	 * Note: since this is displayed data, the query in the function will not return password hashes or any other unnecessary user data
	 */
	public function getEveryUsersData($user_name, $fname, $lname, $school, $level)
	{
		if($this->databaseConnection()) {
			/**
			 * Query selects
			 ** login_relation_id
			 ** person_id (this guy is just along for the ride)
			 ** user_name
			 ** user_email
			 ** admin
			 ** fname
			 ** lname
			 ** school_id
			 ** level_id
			 ** school_name
			 ** level_name
			 *
			 * Note that the collation type for the fname and lname columns makes the LIKE case-insensitive
			 */

			$query = "SELECT `school_info`.*, `level_table`.`level_name`
						FROM (
							SELECT `academic_info`.*, `school_table`.`school_name`
								FROM (
									SELECT `user_info`.*, `school_relation_table`.`school_id`, `school_relation_table`.`level_id`
										FROM (
											SELECT `login_info`.*, `person_table`.`fname`, `person_table`.`lname`
												FROM (
													SELECT `login_relation_table`.`login_relation_id`, `login_relation_table`.`person_id`, `users`.`user_id`, `users`.`user_name`, `users`.`user_email`, `users`.`admin`
														FROM `users`
														LEFT JOIN `login_relation_table`
														ON `login_relation_table`.`user_id` = `users`.`user_id`
												) AS `login_info`
												LEFT JOIN `person_table`
												ON `login_info`.`person_id` = `person_table`.`person_id`
										) AS `user_info`
										LEFT JOIN `school_relation_table`
										ON `user_info`.`login_relation_id` = `school_relation_table`.`login_relation_id`
										WHERE `user_info`.`user_name` COLLATE UTF8_GENERAL_CI LIKE :user_name";

			// Since every user must start with a user name, this will never be null and cannot prevent rows from being displayed if other information is used for the search
			
			// Adds fname query limitation
			if($fname)
			{
				$query .= " AND `user_info`.`fname` COLLATE UTF8_GENERAL_CI LIKE :fname";
			}
			
			// Add lname query limitation
			if($lname)
			{
				$query .= " AND `user_info`.`lname` COLLATE UTF8_GENERAL_CI LIKE :lname";
			}
			
			// Adds school query limitation
			if($school)
			{
				$query .= " AND `school_relation_table`.`school_id` LIKE :school_id";
			}

			// Add level query limitation
			if($level)
			{
				$query .= " AND `school_relation_table`.`level_id` LIKE :level_id";
			}

			// Finishes the query
			$query .= "	) AS `academic_info`
						LEFT JOIN `school_table`
						ON `academic_info`.`school_id` = `school_table`.`school_id`
				) AS `school_info`
				LEFT JOIN `level_table`
				ON `school_info`.`level_id` = `level_table`.`level_id`
				ORDER BY `school_info`.`lname` ASC;";

			$query_user_info = $this->db_connection->prepare($query);
			$query_user_info->bindValue(':user_name', $user_name."%", PDO::PARAM_STR);
			// Binding values as needed based on the above query statement
			if($fname)
			{
				$query_user_info->bindValue(':fname', $fname."%", PDO::PARAM_STR);
			}
			if($lname)
			{
				$query_user_info->bindValue(':lname', $lname."%", PDO::PARAM_STR);
			}
			if($school != "")
			{
				$query_user_info->bindValue(':school_id', $school, PDO::PARAM_STR);
			}

			if($level != "")
			{
				$query_user_info->bindValue(':level_id', $level, PDO::PARAM_STR);
			}
			$query_user_info->execute();

			return $query_user_info->fetchAll(PDO::FETCH_OBJ);
		} else {
			return false;
		}
	}

	private function verifyAccount($user_name)
	{
		// Note that there is no check for a blank user_name as this function shouldn't ever be filled with
		// a blank user name since it is only to be used internally

		// Get user info on selected user
		$result_row = $this->getAllUserData($user_name);
		// Determine if the account is valid
		if($result_row->user_password_change == 0 || !$result_row->fname || !$result_row->lname || !$result_row->gender_id || !$result_row->dob || !$result_row->phone_number || !$result_row->school_id || !$result_row->level_id) {
			// User has not changed password and account is not valid
			return false;
		}
		// Default return
		return true;
	}


	/**
	 * checks whether the user is admin and set $this->user_is_admin = true if user is admin --> __construct()
	 */
	private function checkAdminStatus($user_name)
	{
		// Uses the getUserData() function from the Login object to retrieve user data via user name
		// specifically, acquires the admin status of the user
		$result_row = $this->getUserData(trim($user_name));
		$admin_status = $result_row->admin;
		
		// Sets the user admin variable to true if the user is an admin
		if($admin_status == 1)
		{
			$this->user_is_admin = true;
		}
	}
	
	/**
	 * Returns the admin status of the user
	 */
	public function isUserAdmin()
	{
		return $this->user_is_admin;
	}
	
	/**
	 * Places a prompt into $this->messages for confirmation of admin's choice to add/remove admin status to/from an account
	 * This function depends on the admins having their checkboxes automatically selected upon loading the user table
	 */
	public function modifyAdminStatusConfirm($admin_confirm_array, $admin_confirm_submission_array)
	{
		// Stores user_id's of users to have admin status deleted or added respectively
		$admin_delete_list = array();
		$admin_add_list = array();

		if($this->databaseConnection())
		{
			/**
			 * This portion creates the list of users to have admin status deleted
			 */

			// Selects all admins in the database
			$query = "SELECT * FROM `users` WHERE `admin` = 1;";
			$query_current_admins = $this->db_connection->prepare($query);
			$query_current_admins->execute();

			while($admin_data = $query_current_admins->fetchObject())
			{
				// Compares the current admins with the array of user submitted for admin status update
					// The first part checks to see if a current admin is in the submitted list
					// The second part checks to see if the current admin was even submitted (this is for the AJAX part of the form)
				if(!in_array($admin_data->user_id, $admin_confirm_array) && in_array($admin_data->user_id, $admin_confirm_submission_array))
				{
					// User is not allowed to remove their own admin status
					if($admin_data->user_name == $_SESSION['user_name'])
					{
						$this->errors[] = MESSAGE_ADMIN_STATUS_REMOVAL_ERROR;
					}
					else
					{
						// If current admin is not in submitted list, then they have been removed from admin status
						// Note that this is heavily dependent on automatic selection of all admins in the displayed user table
						$this->messages[] = MESSAGE_ADMIN_STATUS_REMOVED . $admin_data->user_name;
						$admin_delete_list[] = $admin_data->user_id;
					}
				}
			}

			/**
			 * This part adds administrative status
			 */
			
			// Goes through the submitted admin list from admin table
			foreach($admin_confirm_array as $user_id)
			{
				$result_row = $this->getUserDataById($user_id);
				
				// If the user is not already an admin ...
				if($result_row->admin == 0)
				{
					// Prompt to make the user an admin
					$this->messages[] = MESSAGE_ADMIN_STATUS_ADDED . $result_row->user_name;
					$admin_add_list[] = $result_row->user_id;
				}
			}

			// variable holds information that a update_account (action) has been selected (for display of confirm button in html perhaps)
			$this->confirm_action_prompt = true;
		
			// Sets session variables to be used for actual action of deleting or adding admin status
			$_SESSION['admin_delete_list'] = $admin_delete_list;
			$_SESSION['admin_add_list'] = $admin_add_list;
		}
		else
		{
			$this->errors[] = MESSAGE_DATABASE_ERROR;
		}
	}

	/**
	 * Adds/removes admin status to user upon selection
	 * This function depends on the admins having their checkboxes automatically selected upon loading the user table
	 */
	private function modifyAdminStatus($admin_delete_list, $admin_add_list)
	{
		if($this->databaseConnection())
		{
			/**
			 * This part removes administrative status
			 */
			 
			foreach($admin_delete_list as $deleted_admin)
			{
				$query = "UPDATE `users` SET `admin` = 0 WHERE `user_id` = :user_id;";
				$query_remove_admin = $this->db_connection->prepare($query);
				$query_remove_admin->bindValue(":user_id", $deleted_admin, PDO::PARAM_STR);
				$query_remove_admin->execute();
			}
			
			/**
			 * This part adds administrative status
			 */
			
			foreach($admin_add_list as $added_admin)
			{
				// Make the user an admin
				$query = "UPDATE `users` SET `admin` = 1 WHERE `user_id` = :user_id;";
				$query_admin = $this->db_connection->prepare($query);
				$query_admin->bindValue(':user_id', $added_admin, PDO::PARAM_STR);
				$query_admin->execute();
			}

			// Unsets variables so that they can't be re-triggered with a page refresh
			unset($_SESSION['admin_delete_list']);
			unset($_SESSION['admin_add_list']);
		}
		else
		{
			$this->errors[] = MESSAGE_DATABASE_ERROR;
		}
	}
	
	/**
	 * Places a prompt into $this->messages for confirmation of admin's choice to reset the accounts
	 */
	public function resetAccountConfirm($user_array)
	{
		for($i = 0; $i < count($user_array); $i++)
		{
			$result_row = $this->getUserDataById($user_array[$i]);

			// Prevents admin from resetting their own account
			if($result_row->user_name == $_SESSION['user_name'])
			{
				$this->errors[] = MESSAGE_RESET_PERSONAL_ACCOUNT_ERROR;
				array_splice($user_array, $i, 1);
			}
			else
			{
				$this->messages[] = MESSAGE_RESET_ACCOUNT_CONFIRM . $result_row->user_name;
			}
		}
		
		// variable holds information that a reset_account (action) has been selected (for display of confirm button in html perhaps)
		$this->confirm_action_prompt = true;
		
		// Gives back the array of selected users for resetting account in a $_SESSION variable
		$_SESSION['reset_user_accounts_array'] = $user_array;
	}
	
	/**
	 * Resets a user's account and password upon selection (used when user allows temp password to expire or doesn't remember password)
	 * Deletes personal information if user has verified account
	 */
	public function resetAccount($user_id)
	{
		// Checking for valid user_id input
		if(empty($user_id)) {
			$this->errors[] = "The user ID can't be empty.";
		} else if(!preg_match('/^[0-9]*$/', $user_id)) {
			$this->errors[] = "The user ID must be a number";
		} else if($this->databaseConnection()) {
			// Get all user information in user table (need user_email and user_name)
			$result_row = $this->getUserDataById($user_id);

			// Start transaction to ensure all deletions and updates go through at once
			$this->db_connection->beginTransaction();

			// Variable to determine if everything in transaction is successful
			$transaction_ok = true;

			// Check to see if user has set personal information and verified account
			// Delete information if so for complete account reset
			if($this->verifyAccount($result_row->user_name))
			{
				// Get all user_ids
				$database_ids = $this->getAllUserIds($user_id);

				// Deleting the person info in the person_table
				// This is all that is necessary to delete personal information as the other info (login_relation_id, school_relation_id, and position_relation_id) is controlled by cascading foreign keys
				$query = "DELETE FROM `person_table` WHERE `person_id` = :person_id;";
				$query_delete_person_info = $this->db_connection->prepare($query);
				$query_delete_person_info->bindValue(':person_id', $database_ids->person_id, PDO::PARAM_STR);
				$query_delete_person_info->execute();

				// If the above query fails, the transaction fails
				if(!$query_delete_person_info->rowCount())
				{
					$transaction_ok = false;
				}
			}

			// Creates a new random 10 character password for the user
			$user_password = $this->createRandomPassword();
			
			// check if we have a constant HASH_COST_FACTOR defined (in config/hashing.php),
			// if so: put the value into $hash_cost_factor, if not, make $hash_cost_factor = null
			$hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

			// crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
			// the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
			// compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
			// want the parameter: as an array with, currently only used with 'cost' => XX.
			$user_password_hash = password_hash($user_password, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));
			
			// Reset password (this is done every time even if the account isn't verified)
			$query = "UPDATE `users` SET `user_password_hash` = :user_password_hash, `user_registration_datetime` = now(), `user_password_change` = 0 WHERE `user_id` = :user_id;";
			$query_reset_account_update = $this->db_connection->prepare($query);
			$query_reset_account_update->bindValue(":user_password_hash", $user_password_hash, PDO::PARAM_STR);
			$query_reset_account_update->bindValue(":user_id", $user_id, PDO::PARAM_STR);
			$query_reset_account_update->execute();

			// If password could not be updated, transaction invalid
			if(!$query_reset_account_update->rowCount())
			{
				$transaction_ok = false;
			}

			// unsets the variable so that it cannot be re-triggered through refreshing
			unset($_SESSION['reset_user_accounts_array']);
			
			// If the transaction is ok
			if($transaction_ok)
			{
				// Send an email with the user name and password for the account
				if($this->sendResetAccountEmail($result_row->user_name, $result_row->user_email, $user_password))
				{
					// commit changes to database
					$this->db_connection->commit();
					$this->messages[] = MESSAGE_RESET_ACCOUNT_MAIL_SENT;
				}
				// Email could not send
				else
				{
					// Undo database changes because mail was not sent and send error
					$this->db_connection->rollBack();
					$this->errors[] = MESSAGE_RESET_ACCOUNT_MAIL_ERROR;
				}
			}
			// transaction invalid
			else
			{
				// Undo database changes because all queries were not successful and send error
				$this->db_connection->rollBack();
				$this->errors[] = MESSAGE_RESET_ACCOUNT_FAILED;
			}
		}
		else
		{
			// Could not connect to database
			$this->errors[] = MESSAGE_DATABASE_ERROR;
		}
	}
	
	/**
	 * Creates a cryptographically random password
	 */
	private function createRandomPassword()
	{
		// Creates a cryptographically random set of bytes for a random password
		$crypt_strong = false;
		while(!$crypt_strong)
		{
			$random_password_bytes = openssl_random_pseudo_bytes(5, $crypt_strong);
		}
		
		// Converts those bytes to hexadecimals (letter & numbers) for the actual password
		$random_password_str = bin2hex($random_password_bytes);
		
		
		return $random_password_str;
	}
	
	/**
	 * Creates an email for an account reset
	 */
	public function sendResetAccountEmail($user_name, $user_email, $user_password)
	{
		$mail = new PHPMailer;
		
		// please look into the config/config.php for much more info on how to use this!
		// use SMTP or use mail()
		if(EMAIL_USE_SMTP) {
			// Set mailer to use SMTP
			$mail->IsSMTP();
			//useful for debugging, shows full SMTP errors
			//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
			// Enable SMTP authentication
			$mail->SMTPAuth = EMAIL_SMTP_AUTH;
			// Enable encryption, usually SSL/TLS
			if(defined(EMAIL_SMTP_ENCRYPTION)) {
				$mail->SMPTSecure = EMAIL_SMTP_ENCRYPTION;
			}
			// Specify host server
			$mail->Host = EMAIL_SMTP_HOST;
			$mail->Username = EMAIL_SMTP_USERNAME;
			$mail->Password = EMAIL_SMTP_PASSWORD;
			$mail->Port = EMAIL_SMTP_PORT;
		} else {
			$mail->IsMail();
		}
		
		$mail->From = EMAIL_RESET_ACCOUNT_FROM;
		$mail->FromName = EMAIL_RESET_ACCOUNT_FROM_NAME;
		$mail->AddAddress($user_email);
		$mail->Subject = EMAIL_RESET_ACCOUNT_SUBJECT;
		
		// Body of the email
		$mail->Body = EMAIL_RESET_ACCOUNT_BODY . "User Name: $user_name\nPassword: $user_password" . EMAIL_FOOTER_AND_SIGNATURE;

		if(!$mail->Send()) {
			$this->errors[] = MESSAGE_RESET_ACCOUNT_MAIL_NOT_SENT . $mail->ErrorInfo;
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * The sole purpose of this function is to create a prompt for deletion in case of accidental selection
	 * The function simply accepts the array of selected users for deletion and adds them to the $this->messages array
	 * Changes the $this->confirm_action_prompt to true for use in html and returns entire array for use in deleteAccount function
	 */
	public function deleteAccountConfirm($user_array)
	{
		// Creates a warning message for each user selected for deletion
		for($i = 0; $i < count($user_array); $i++)
		{
			$result_row = $this->getUserDataById($user_array[$i]);

			// Prevents an admin from deleting themselves
			if($result_row->user_name == $_SESSION['user_name'])
			{
				$this->errors[] = MESSAGE_DELETE_PERSONAL_ACCOUNT_ERROR;
				array_splice($user_array, $i, 1);
			}
			else
			{
				$this->messages[] = MESSAGE_DELETE_USER_CONFIRM . $result_row->user_name;
			}
		}
		
		// variable holds information that a deletion (action) has been selected (for display of confirm button in html perhaps)
		$this->confirm_action_prompt = true;
		
		// Gives back the array of selected users for deletion in a $_SESSION variable
		$_SESSION['delete_users_array'] = $user_array;
	}
	
	/**
	 * Permanently deletes an account
	 */
	private function deleteAccount($user_id)
	{
		// Get the user name of the select user_id
		$query = "SELECT * FROM `users` WHERE `user_id` = :user_id;";
		$query_user_name = $this->db_connection->prepare($query);
		$query_user_name->bindValue(':user_id', $user_id, PDO::PARAM_STR);
		$query_user_name->execute();
		$result_user_name = $query_user_name->fetchObject();

		// Check to see if personal information exists
		// If so, delete it first with impunity!
		$result_row = $this->getAllUserData($result_user_name->user_name);
		if(is_object($result_row) > 0) {
			$query = "DELETE FROM `person_table` WHERE `person_id` = :person_id;";
			$query_delete_personal_info = $this->db_connection->prepare($query);
			$query_delete_personal_info->bindValue(':person_id', $result_row->person_id, PDO::PARAM_STR);
			$query_delete_personal_info->execute();
		}

		// The account (user) is always set to be deleted
		$query = "DELETE FROM `users` WHERE `user_id` = :user_id;";
		$query_delete_account = $this->db_connection->prepare($query);
		$query_delete_account->bindValue(":user_id", $user_id, PDO::PARAM_STR);
		$query_delete_account->execute();
		
		/**
		 * The above delete statements are all that should be necessary since the database
		 * is set to cascade through the foreign keys, and the major primary keys have just 
		 * been blown away!
		 */

		// unsets the variable so that it cannot be re-triggered through refreshing
		unset($_SESSION['delete_users_array']);
	}
}