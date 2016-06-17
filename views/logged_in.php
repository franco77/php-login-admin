<?php include('_header.php'); ?>

<?php
// if you need the user's information, just put them into the $_SESSION variable and output them here
echo WORDING_YOU_ARE_LOGGED_IN_AS . $_SESSION['user_name'] . "<br />";
//echo WORDING_PROFILE_PICTURE . '<br/><img src="' . $login->user_gravatar_image_url . '" />;
echo WORDING_PROFILE_PICTURE . '<br/>' . $login->user_gravatar_image_tag;
?>

<div>
    <a href="index.php?logout"><?php echo WORDING_LOGOUT; ?></a>
<?php if($login->isUserVerified() == false) echo "    <a href=\"verify_account.php\">" . WORDING_VERIFY_ACCOUNT . "</a>\n"; ?>
<?php if($login->isUserVerified() == true) echo "    <a href=\"edit.php\">" . WORDING_EDIT_USER_DATA . "</a>\n"; ?>
<?php if($permissions->isUserAdmin() == true) echo "    <a href=\"admin.php\">" . WORDING_ADMIN_LINK . "</a>\n"; ?>
</div>

<?php include('_footer.php'); ?>
