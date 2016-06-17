<?php include('_header.php'); ?>

<?php
if($permissions->confirm_action_prompt)
{
	// This is just one way to trigger the $_POST['confirm_action'] variable in the Permissions class
	echo "
<form method=\"post\" action=\"admin.php\">
  <input type=\"submit\" name=\"confirm_action\" value=\"Confirm\" />
  <input type=\"submit\" value=\"No, Go Back!\" />
</form>
	";
}
?>
<?php
// Create a database connection or return error and terminate admin page
try
{
	$db_connection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
}
catch(PDOException $e)
{
	echo "Error: " . $e->getMessage();
	die();
}

// Set up query for entire table
$query = "SELECT * FROM `users`;";
$query_user_table = $db_connection->prepare($query);
$query_user_table->execute();
?>

<h2><?php echo $_SESSION['user_name']; ?> <?php echo WORDING_ADMIN_EDIT_ACCOUNTS; ?></h2>

<a href="register.php"><?php echo WORDING_REGISTER_NEW_ACCOUNT; ?></a>

<br /><br />

<!-- Table of all users encapsulated by a form to allow checkboxes for quickly modifying account permissions -->
<form method="post" action="admin.php">
  <input type="submit" name="update" value="<?php echo WORDING_UPDATE; ?>" /> 
  <table border="1">
    <tr>
      <th>User Name</th>
      <th>User Email</th>
      <th>User Registration Date</th>
      <th>Admin</th>
      <th>Reset Account</th>
      <th>Delete Account</th>
    </tr>
<?php
// Creates table
while($data = $query_user_table->fetchObject())
{
	echo "
    <tr>
      <td>$data->user_name</td>
      <td>$data->user_email</td>
      <td>$data->user_registration_datetime</td>\n";
	
	// Automatically checks admins so that they can be unchecked to remove admin privilege
	echo "      <td><input type=\"checkbox\" name=\"admin[]\" value=\"$data->user_id\" ";
	if($data->admin == 1) echo "checked";
	echo " /></td>\n";

// Creates checkbox arrays for "reset_account" and "delete_account" so that multiple actions can be selected at once
	echo "
      <td><input type=\"checkbox\" name=\"reset_account[]\" value=\"$data->user_id\" /></td>
      <td><input type=\"checkbox\" name=\"delete_account[]\" value=\"$data->user_id\" /></td>
    </tr>\n";
}
?>
  </table>
</form>

<br />

<!-- backlink -->
<a href="index.php"><?php echo WORDING_BACK_TO_LOGIN; ?></a>

<?php include('_footer.php'); ?>
