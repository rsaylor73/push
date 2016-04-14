<?php
include "settings.php";

$settings = $push->get_settings();
if (($settings[0] == $_GET['uuname']) && ($settings[1] == $_GET['uupass'])) {
	$_SESSION['uuname'] = $_GET['uuname'];
	$_SESSION['uupass'] = $_GET['uupass'];
	print $settings[5]."index.php?action=dashboard";
} else {
	$push->login('<font color=red>The username and or password was incorrect.</font>');
}
?>
