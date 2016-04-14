<?php

include "settings.php";

if (($_GET['action'] == "") && ($_POST['action'] == "")) {
print "<form action=\"home.php\" method=\"post\">
<input type=\"hidden\" name=\"action\" value=\"login\">
<br><br><br>
<div align=center>
Please complete the form below to be re-directed to your personal login page.<br><br>
<table border=1 width=700>
<tr>
	<td>
		<table border=0 width=700>
			<tr>
				<td>Username:</td><td><input type=\"text\" name=\"uuname\" size=40></td>
			</tr>
			<tr>
				<td>Password:</td><td><input type=\"password\" name=\"uupass\" size=40></td>
			</tr>
			<tr><td></td><td><input type=\"submit\" value=\"Go To My Site\"></td></tr>
		</table>
	</td>
</tr>
</table>
</form>";
}

if ($_POST['action'] == "login") {
	$sql = "SELECT * FROM `push_push`.`sites` WHERE `uuname` = '$_POST[uuname]' AND `uupass` = '$_POST[uupass]'";
	$result = $push->new_mysql($sql);
	while ($row = $result->fetch_assoc()) {
		header('Location: http://' . $row['sub'] . '.push.theappwizards.com');
		$found = "1";
	}
	if ($found != "1") {
		print "<br><br>Login was incorrect. Please click back and try again.<br><br>";
	}
}
?>
