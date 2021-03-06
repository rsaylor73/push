<?php

class Push {

	public $linkID;

	function __construct($linkID){ $this->linkID = $linkID; }

	public function new_mysql($sql) {
		$result = $this->linkID->query($sql) or die($this->linkID->error.__LINE__);
		return $result;
	}

        private function get_proper_db($db) {
                if ($_SESSION['database'] == DB1) {
                        $name = $_SESSION['database'];
                }
                if ($_SESSION['database'] == DB2) {
                        $name = $_SESSION['database'];
                }

                return $name;
        }

	public function get_settings() {
		$server = explode(".",$_SERVER['HTTP_HOST']);
		/*
		if ($server[0] == "push") {
			if ($_SERVER['REQUEST_URI'] != "/home.php") {
				print "<br><font color=red>URL CALL ERROR</font><br>";
				die;
			}
		}
		*/

		
		$sql = "SELECT * FROM ".LOCAL_DB.".`sites` WHERE `sub` = '$server[0]'";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {

			$pem = $row['pem']; 
			$id = $row['app_id']; 
			$crypto = $row['crypto']; 
			$uuname = $row['uuname']; 
			$uupass = $row ['uupass'];
			$_SESSION['database'] = $row['database'];
			$_SESSION['reports'] = $row['reports'];
			if ($row['super_admin'] != "Yes") {
				$_SESSION['app_id'] = $id;
				$_SESSION['reports'] = $row['reports'];
				$_SESSION['push'] = $row['push'];
				$_SESSION['chat'] = $row['chat'];
			} else {
				// super admin
				$_SESSION['push'] = $row['push'];
				$_SESSION['app_id'] = "";
				$_SESSION['reports'] = "";
				$_SESSION['chat'] = "";
			}

			switch ($server[0]) {
				case "pushdev":
				case "push":
				case "pushv2":
				$sub = "";

				break;

				default:
				$sub = $row['sub'];
				$sub .= ".";
				break;
			}
	
			$domain = "http://$sub".SUBPATH.".theappwizards.com/".ENDPATH;
			$path = PATH;
			$logo = $row['logo'];
			$found = "1";
			$super = $row['super_admin'];
			$_SESSION['super'] = $super;
		}
	
		if ($found != "1") {
                        if ($_SERVER['REQUEST_URI'] != "/home.php") {
				print "<br><font color=red>INCORRECT URL UNABLE TO LOCATE CONFIG</font><br>";
			}
		}

		/*
		$sql = "SELECT * FROM ".LOCAL_DB.".`settings` WHERE `id` = '1'";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			$api_gcm = $row['api_gcm'];
		}
		*/

		$sql = "
		SELECT
			`pc`.`path` AS 'api_gcm'

		FROM
			".$_SESSION['database'].".`push_certificate` pc

		WHERE
			`pc`.`type` = 'android_key'
		";

		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			$api_gcm = $row['api_gcm'];
		}

		$data[] = $uuname;
		$data[] = $uupass;
		$data[] = $pem;
		$data[] = $id;
		$data[] = $crypto;
		$data[] = $domain;
		$data[] = $path;
		$data[] = $api_gcm;
		$data[] = $logo;
		$data[] = $super;

		return $data; // settings returned as an array
	}

        public function check_login() {

		$settings = $this->get_settings();
		if (($settings[0] == $_SESSION['uuname']) && ($settings[1] == $_SESSION['uupass'])) {
                        return "TRUE";
                } else {
                        return "FALSE";
                }
        }


        // Login form
        public function login($msg) {

                if ($msg != "") {
                        print "<center><font color=red>$msg</font></center><br>";
                }

                print "
                <br>
                <div align=\"center\" id=\"login-scr\">
                <form name=\"myform\" id=\"myform\">
                <table border=0 width=700>
                <tr><td>
                        <table border=0 width=700>
                                <tr><td>Username:</td><td><input type=\"text\" name=\"uuname\" size=20></td></tr>
                                <tr><td>Password:</td><td><input type=\"password\" name=\"uupass\" onkeypress=\"if(event.keyCode==13) { login(this.form); return false;}\" size=20></td></tr>
                                <tr><td>&nbsp;</td><td><input type=\"button\" value=\"Login\" onclick=\"login(this.form)\"></td></tr>
                                <tr><td>&nbsp;</td><td>";

                                print "</td></tr>
                        </table>
                </td></tr>
                </table>
                </form>
                </div>
                <br>";

                ?>
                                <script>

                                function login(myform) {
                                        $.get('login.php',
                                        $(myform).serialize(),
                                        function(php_msg) {
                                          if (php_msg.substring(0,4) == "http") {
                                             $("#login-scr").html('<span class="details-description"><font color=green>Login successful. Loading please wait...</font><br></span>');
                                             setTimeout(function()
                                                {
                                                window.location.replace(php_msg)
                                                }
                                             ,2000);
                                          } else {
                                             $("#login-scr").html(php_msg);
                                          }
                                        });
                                }
                                </script>
                <?php

        }

	public function dashboard() {
		$settings = $this->get_settings();
		include "templates/dashboard.phtml";
	}

	public function get_template_block($id) {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }
                $DB = $this->get_proper_db('1');

		$sql = "SELECT * FROM ".$DB.".`template_block` WHERE `block_id` = '$id'";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<h2>$row[name]</h2>
			<form name=\"myform\" action=\"index.php\" method=\"post\">
			<input type=\"hidden\" name=\"action\" value=\"update_block\">
			<input type=\"hidden\" name=\"block_id\" value=\"$row[block_id]\">
			<table border=0 width=500>
			<tr><td>Color:</td><td><input type=\"text\" class=\"jscolor\" name=\"color\" value=\"$row[color]\" size=15></td></tr>
			<tr><td>Background Color:</td><td><input type=\"text\" class=\"jscolor\" name=\"background_color\" value=\"$row[background_color]\" size=15></td></tr>
			<tr><td>Color On Hover</td><td><input type=\"text\" class=\"jscolor\" name=\"color_on_hover\" value=\"$row[color_on_hover]\" size=15></td></tr>
			<tr><td>Background Color On Hover</td><td><input type=\"text\" class=\"jscolor\" name=\"background_color_on_hover\" value=\"$row[background_color_on_hover]\" size=15></td></tr>
			<tr><td>Color On Active</td><td><input type=\"text\" class=\"jscolor\" name=\"color_on_active\" value=\"$row[color_on_active]\" size=15></td></tr>
			<tr><td>Background Color On Active</td><td><input type=\"text\" class=\"jscolor\" name=\"background_color_on_active\" value=\"$row[background_color_on_active]\" size=15></td></tr>
			<tr><td>Image Color</td><td><input type=\"text\" name=\"image_color\" class=\"jscolor\" value=\"$row[image_color]\" size=15></td></tr>
			<tr><td colspan=2><font color=blue><b>Take a screen shot before changing the values. Once they are saved the values will be set.</font></b></td></tr>
			<tr><td colspan=2><input type=\"submit\" class=\"btn btn-primary\" value=\"Update\"></td></tr>
			</table>
			</form>";
		}
	}

	public function update_block() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }
		foreach ($_POST as $key=>$value) {
			switch ($key) {
				case "action":
				case "block_id":
				// do nothing
				break;

				default:
				$_POST[$key] = "#" . $value;
				break;
			}
		}
		$sql = "UPDATE ".APP_DB.".`template_block` SET
		`color` = '$_POST[color]',
		`background_color` = '$_POST[background_color]',
		`color_on_hover` = '$_POST[color_on_hover]',
		`background_color_on_hover` = '$_POST[background_color_on_hover]',
		`color_on_active` = '$_POST[color_on_active]',
		`background_color_on_active` = '$_POST[background_color_on_active]',
		`image_color` = '$_POST[image_color]'
		WHERE `block_id` = '$_POST[block_id]'
		";
		$result = $this->new_mysql($sql);
		if ($result == "TRUE") {
			print "<br><font color=green>The template was updated.</font><br>";
		} else {
			print "<br><font color=red>The template failed to update.</font><br>";
		}
		$this->dashboard();
	}

	public function push_form() {
		if ($_SESSION['push'] != "Yes") {
			print "<br><font color=red>Sorry, but you do not have access to Push Notification.</font>";
			die;
		}
		$names = $this->get_tokens('name');
		print "<h2><span class=\"fa fa-apple\"> Push Form</a></h2>";

		?>
		<script type="text/javascript" language="javascript">
		// <![CDATA[
		function checkAll(formname, checktoggle)
		{
		  var checkboxes = new Array(); 
		  checkboxes = document[formname].getElementsByTagName('input');
 
		  for (var i=0; i<checkboxes.length; i++)  {
		    if (checkboxes[i].type == 'checkbox')   {
		      checkboxes[i].checked = checktoggle;
		    }
		  }
		}
		// ]]>
		</script>
		<?php


		print "
		<form name=\"myform\" action=\"index.php\" method=\"post\">
		<input type=\"hidden\" name=\"action\" value=\"send_push\">
		<table class=\"table\">
		<tr><td>
			<table class=\"table\">
			<tr>
				<td>
					<textarea name=\"push\" id=\"push\" cols=\"70\" rows=\"5\" maxlength=\"200\"></textarea>
					<br>
					<div id=\"message\"></div>
					";
					?>
					<script>
					var area = document.getElementById("push");
					var message = document.getElementById("message");
					var maxLength = 200;
					var checkLength = function() {
					    if(area.value.length < maxLength) {
					        message.innerHTML = (maxLength-area.value.length) + " characters remainging";
					    }
					}
					setInterval(checkLength, 300);
					</script>
					<?php


					/*
					print "
					<br><input type=\"checkbox\" name=\"testmode\" value=\"checked\" onclick=\"return confirm('The push notification will only be sent to John and Robert registered devices')\">&nbsp;<b>Test Mode</b> (Send push to John and Robert)<br>
					*/

					print "
					<br><input type=\"submit\" class=\"btn btn-primary\" value=\"Send Push Message\">
				</td>
				<td>
					<b>The following people will receive your push message:<br></b>
					<a onclick=\"javascript:checkAll('myform', true);\" href=\"javascript:void();\"><span class=\"btn btn-success\">check all</span></a> 
					<a onclick=\"javascript:checkAll('myform', false);\" href=\"javascript:void();\"><span class=\"btn btn-danger\">uncheck all</span></a><br>
					$names

				</td>
			</tr>
			</table>
		</td></tr>
		</table>
		</form>";
	}

        public function push_form_android() {
        	if ($_SESSION['push'] != "Yes") {
				print "<br><font color=red>Sorry, but you do not have access to Push Notification.</font>";
				die;
			}
                $names = $this->get_tokens_android('name');
                print "<h2><span class=\"fa fa-android\"> Push Form</span></h2>";

                print "
                <form name=\"myform\" action=\"index.php\" method=\"post\">
                <input type=\"hidden\" name=\"action\" value=\"send_push_android\">
                <table class=\"table\">
                <tr><td>
                        <table class=\"table\">
                        <tr>
                                <td>
                                        <textarea name=\"push\" id=\"push\" cols=\"70\" rows=\"5\" maxlength=\"200\"></textarea>
                                        <br>
                                        <div id=\"message\"></div>
                                        ";
                                        ?>
                                        <script>
                                        var area = document.getElementById("push");
                                        var message = document.getElementById("message");
                                        var maxLength = 200;
                                        var checkLength = function() {
                                            if(area.value.length < maxLength) {
                                                message.innerHTML = (maxLength-area.value.length) + " characters remainging";
                                            }
                                        }
                                        setInterval(checkLength, 300);
                                        </script>
                                        <?php


					/*
                                        print "
                                        <br><input type=\"checkbox\" name=\"testmode\" value=\"checked\" onclick=\"return confirm('The push notification will only be sent to John and Robert registered devices')\">&nbsp;<b>Test Mode</b> (Send push to John and Robert)<br>
					*/
					print "
                                        <br><input type=\"submit\" class=\"btn btn-primary\" value=\"Send Push Message\">
                                </td>
                                <td>
                                        <b>The number of devices will receive your push message:<br></b>
                                        $names

                                </td>
                        </tr>
                        </table>
                </td></tr>
                </table>
                </form>";
        }


	public function send_push() {
		$settings = $this->get_settings();
		$pem = $settings[2];
		$pw = $settings[4];
                $DB = $this->get_proper_db('1');
		
		if ($_POST['testmode'] == "checked") {
	        $deviceToken = '9bd48932ae6f9723f3c10d0d586c5c2159f56b6d530f5c2d15ca67fcb501f1d6'; // robert
			$counter[] = $this->push_loop_with_token($pem,$pw,$_POST['push'],$deviceToken);

			$deviceToken = '43a3685c5ef144b888a914698951560c0f8e02f5463c006816036974d97c2009'; // john
            $counter[] = $this->push_loop_with_token($pem,$pw,$_POST['push'],$deviceToken);
		} else {
	        $sql = "SELECT * FROM `$DB`.`push_apns_devices` WHERE `app_id` = '$settings[3]' AND `status` = 'active'";
			$result = $this->new_mysql($sql);
			while ($row = $result->fetch_assoc()) {
				$i = "device_id_";
				$i .= $row['device_id'];
				if ($_POST[$i] == "checked") {
					$deviceToken = $row['device_token'];
		            $counter[] = $this->push_loop_with_token($pem,$pw,$_POST['push'],$deviceToken);
				}
			}

		}

		$total = "0";
		if(is_array($counter)) {
			foreach ($counter as $result) {
				$total = $total + $result;
			}
		}
		print "<br>The push notification was delivered to $total devices.<br>";


	}

	public function send_push_android() {
        $settings = $this->get_settings();
		$api = $settings[7];
		$message = $_POST['push'];
                $DB = $this->get_proper_db('1');


        if ($_POST['testmode'] == "checked") {
			//$deviceToken = "APA91bH2x-yLJzTRKF9mqsPRPapYaTIM2gYLkPFshbfzDKkKM6dCMvd4QDOB_y4VRtOaK04Z0Q2tX2PntevsGk4QmAA3qYd70QIN62RjzrKsQcjh37hrIujqQebvLaTZHjZDkFN81L8k8A8iNediMTUaxPHogt3UYw";
			$deviceToken = "APA91bGVIwy7Ef7Y9va_gBI46nmWIFmQrHqjdyqCc-DJwNa-Js9jZONnDUvPFMoQngOQDk8Wj43sXOacs1hve2CC2pIEG-gtRgkQtW8WngCjNAwNZGs8ok1qu5icd3P_EsGHJtf5E-0bhJTYdrGT7hCb8tW1Vg_XdA"; // John
			$counter[] = $this->push_android_loop_with_token($api,$message,$deviceToken);

			$deviceToken = "APA91bGjuEPO4QH_fw6Tijqvb56hHo3NQkYx4OWKXqMlkAJJizQBTPJ9Whb4_IHStlUx2jT3Rc5mBfVJDDb5yVrtNGkFEUDoJe4VJvkAhkvS0qo768jeogeRd8sYwqSnD-OTAepB5IaWkHtogXt-lEJJ5FIgxVhUTA"; // Robert
            $counter[] = $this->push_android_loop_with_token($api,$message,$deviceToken);

		} else {
			$sql = "SELECT * FROM `$DB`.`push_gcm_devices` WHERE `app_id` = '$settings[3]' AND `development` = 'production' AND `status` = 'active'";
            $result = $this->new_mysql($sql);
            while ($row = $result->fetch_assoc()) {
	        	$deviceToken = $row['registration_id'];
	            $counter[] = $this->push_android_loop_with_token($api,$message,$deviceToken);
			}

		}
        $total = "0";
        foreach ($counter as $result) {
            $total = $total + $result;
        }
		if ($total < 0) {
			print "<br><font color=red>The push notification failed to deliver.<br></font>";
		} else {
	        print "<br>The push notification was delivered to $total devices.<br>";
		}

	}

	public function push_android_loop_with_token($api,$message,$deviceToken) {
		$registrationIds = array( $deviceToken ); // see table siberian_appwizards2.push_gcm_devices

		// prep the bundle
		$msg = array
		(
		        'message'       => $message,
		        'title'         => $message,
		        'subtitle'      => $message,
		        'tickerText'    => $message,
		        'vibrate'       => 1,
		        'sound'         => 1,
		        'largeIcon'     => 'large_icon',
		        'smallIcon'     => 'small_icon'
		);

		$fields = array
		(
		        'registration_ids'      => $registrationIds,
		        'data'                  => $msg
		);

		$headers = array
		(
		        'Authorization: key=' . $api,
		        'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );
		curl_close( $ch );

		//var_dump(json_decode($result));
		/*
		print "Debug View:<br>
		<pre>";
		print_r($var_dump);
		print "</pre>";
		*/

		$obj = json_decode($result);
		$success = $obj->{'success'};
		$failure = $obj->{'failure'};
		if ($success == "1") {
			return "1";
		} else {
			return "-1";
		}
	}

	public function push_loop_with_token($pem,$pw,$message,$deviceToken) {
		    $counter = "1";
	        $ctx = stream_context_create();
	        stream_context_set_option($ctx, 'ssl', 'local_cert', $pem);
	        stream_context_set_option($ctx, 'ssl', 'passphrase', $pw);
	        // Open a connection to the APNS server
	        $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
	        //$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
	        if (!$fp)
	        exit("Failed to connect: $err $errstr" . PHP_EOL);
	        //echo 'Connected to APNS' . PHP_EOL;
	        // Create the payload body
	        $body['aps'] = array(
	        'alert' => array(
        	        'body' => $message,
	                'action-loc-key' => 'TheAppWizards',
	        ),
	        'badge' => 0,
	        'sound' => 'default',
	        );
	        // Encode the payload as JSON
	        $payload = json_encode($body);
	        // Build the binary notification
	        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
	        // Send it to the server
	        $result = fwrite($fp, $msg, strlen($msg));
	        if (!$result)
		    $stat = "-1";
	        else
		     $stat = "1";
	        // Close the connection to the server
	        fclose($fp);
		    return $stat;
	}


	public function get_tokens($column) {
		$settings = $this->get_settings();
		$DB = $this->get_proper_db('1');

		$sql = "SELECT * FROM `$DB`.`push_apns_devices` WHERE `app_id` = '$settings[3]' AND `status` = 'active'";
		$result = $this->new_mysql($sql);
		switch ($column) {
			case "name":
			while ($row = $result->fetch_assoc()) {
				$names .= "<input type=\"checkbox\" name=\"device_id_$row[device_id]\" value=\"checked\" checked> $row[device_name]<br>";
			}
			$data = $names;
			break;
		}
		return $data;
	}

        public function get_tokens_android($column) {
                $settings = $this->get_settings();
                $DB = $this->get_proper_db('1');

                $sql = "SELECT * FROM `$DB`.`push_gcm_devices` WHERE `app_id` = '$settings[3]' AND `status` = 'active' AND `development` = 'production'";
                $result = $this->new_mysql($sql);
                switch ($column) {
                        case "name":
                        while ($row = $result->fetch_assoc()) {
				$total++;
                        }
                        break;
                }
                return $total;
        }

	public function get_server_settings() {
		$sql = "SELECT * FROM ".LOCAL_DB.".`settings` WHERE `id` = '1'";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			$server_ip = $row['server_ip'];
			$domain_user = $row['domain_user'];
			$domain_pw = $row['domain_pw'];
			$domain = $row['domain'];
		}

		$data[] = $server_ip;
		$data[] = $domain_user;
		$data[] = $domain_pw;
		$data[] = $domain;

		return $data;

	}

	public function new_push_site() {
		$settings = $this->get_settings();
		if ($settings[9] != "Yes") {
			print "<br><font color=red>ACCESS DENIED</font><br>";
			die;
		}

		print "<h2>Create New User:</h2>";

		print "<form action=\"index.php\" method=\"post\" enctype=\"multipart/form-data\">
		<input type=\"hidden\" name=\"action\" value=\"create_new_site\">
		<table class=\"table\">
		<tr><td>Sub Domain</td><td>http://<input type=\"text\" name=\"sub\" size=40 required>.".SUBPATH.".theappwizards.com</td></tr>
		<tr><td>PEM File:</td><td><input type=\"file\" name=\"pem\" required></td></tr>
		<tr><td>PEM Password:</td><td><input type=\"text\" name=\"crypto\" size=40 required></td></tr>
		<tr><td>Siberian App ID:</td><td><input type=\"text\" name=\"app_id\" size=20 required></td></tr>
		<tr><td>Logo</td><td><input type=\"file\" name=\"logo\" required></td></tr>
		<tr><td>Username (unique):</td><td><input type=\"text\" name=\"uuname\" size=40 required></td></tr>
		<tr><td>Password:</td><td><input type=\"text\" name=\"uupass\" size=40 required></td></tr>
		<tr><td>Siberian Database:</td><td><select name=\"database\"><option value=\"siberian_appwizard2\">Version 1</option><option value=\"wwsib2_siberian\">Version 2</option>
        </select></td></tr>
        <tr><td>Push Notification</td><td><select name=\"push\"><option>No</option><option>Yes</option></select></td></tr>
        <tr><td>Reports:</td><td><select name=\"reports\"><option>No</option><option>Yes</option></select></td></tr>
	<tr><td>Chat:</td><td><select name=\"chat\"><option>No</option><option>Yes</option></select></td></tr>
		<tr><td colspan=2><input type=\"submit\" class=\"btn btn-primary\" value=\"Add User\"><br><font color=blue><b>NOTE: Please wait after clicking the button above. We are actually talking to cPanel to create the sub sub domain. This could take a few minutes for the API to get a return value.</font></b></td></tr>
		</table>
		</form>";
	}

	public function chat_users() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }
		print "<h2>Manage Chat Users</h2>
		<table class=\"table\">
		<tr><td><b>Name</b></td><td><b>Email</b></td><td><b>Date Registered</b></td><td><b>Alias</b></td><td>&nbsp;</td></tr>";

		$sql = "
		SELECT 
			DATE_FORMAT(`u`.`date_registered`, '%m/%d/%Y') AS 'date_registered',
			`u`.`fname`,
			`u`.`lname`,
			`u`.`email`,
			`u`.`id`,
			`u`.`alias`

		FROM `push_chat`.`users` u

		ORDER BY `u`.`lname` ASC, `u`.`fname` ASC
		";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[fname] $row[lname]</td><td>$row[email]</td><td>$row[date_registered]</td><td>$row[alias]</td>
			<td>
			<input type=\"button\" class=\"btn btn-primary\" value=\"Edit\" onclick=\"document.location.href='index.php?action=edit_chat_user&id=$row[id]'\">&nbsp;
			<input type=\"button\" class=\"btn btn-danger\" value=\"Delete\" onclick=\"if(confirm('You are about to delete this user')) { document.location.href='index.php?action=delete_chat_user&id=$row[id]' };\">
			</td></tr>";
			$found = "1";
		}
		if ($found != "1") {
			print "<tr><td colspan=5><font color=blue>There are no chat users</font></td></tr>";
		}
		print "</table>";

	}

        public function new_chat_user() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }
                
                print "<h2>Add Chat User</h2>";
                print "<form action=\"index.php\" method=\"post\">
                <input type=\"hidden\" name=\"action\" value=\"save_chat_user\">
                <input type=\"hidden\" name=\"id\" value=\"$_GET[id]\">
                <table class=\"table\">
                <tr><td>First Name:</td><td><input type=\"text\" name=\"fname\" value=\"$row[fname]\" size=40 required></td></tr>
                <tr><td>Last Name:</td><td><input type=\"text\" name=\"lname\" value=\"$row[lname]\" size=40 required></td></tr>
                <tr><td>Email:</td><td><input type=\"text\" name=\"email\" value=\"$row[email]\" size=40 required></td></tr>
                <tr><td>Username:</td><td><input type=\"text\" name=\"uuname\" size=20 required></td></tr>
                <tr><td>Alias:</td><td><input type=\"text\" name=\"alias\" size=40 required></td></tr>
                <tr><td>Password:</td><td><input type=\"password\" name=\"uupass\" size=40 placeholder=\"********\" required></td></tr>
                <tr><td colspan=2><input type=\"submit\" class=\"btn btn-primary\" value=\"Add\"></td></tr>
                </table>
                </form>";
        }

	public function save_chat_user() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }

                $sql = "SELECT `email` FROM `push_chat`.`users` WHERE `email` = '$_POST[email]'";
                $result = $this->new_mysql($sql);
                while ($row = $result->fetch_assoc()) {
                        print "<br><font color=red>Sorry, the email <b>$_POST[email]</b> is registered with another chat user.</font><br>";
                        die;
                }

                $sql = "SELECT `uuname` FROM `push_chat`.`users` WHERE `uuname` = '$_POST[uuname]'";
                $result = $this->new_mysql($sql);
                while ($row = $result->fetch_assoc()) {
                        print "<br><font color=red>Sorry, the username <b>$_POST[uuname]</b> is registered with another chat user.</font><br>";
                        die;
                }

                $sql = "SELECT `alias` FROM `push_chat`.`users` WHERE `alias` = '$_POST[alias]'";
                $result = $this->new_mysql($sql);
                while ($row = $result->fetch_assoc()) {
                        print "<br><font color=red>Sorry, the email <b>$_POST[alias]</b> is registered with another chat user.</font><br>";
                        die;
                }

		$new_pass = md5($_POST['uupass']);
		$date = date("Ymd");
		$sql = "INSERT INTO `push_chat`.`users` (`uuname`,`uupass`,`fname`,`lname`,`email`,`date_registered`,`alias`) VALUES
		('$_POST[uuname]','$new_pass','$_POST[fname]','$_POST[lname]','$_POST[email]','$date','$_POST[alias]')";
                $result = $this->new_mysql($sql);
                if ($result == "TRUE") {
                        print "<br><font color=green>The chat user was added.</font><br>";
                } else {
                        print "<br><font color=red>The chat user failed to add.</font><br>";
                }
                $this->chat_users();
	}

	public function edit_chat_user() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }

		$sql = "SELECT * FROM `push_chat`.`users` WHERE `id` = '$_GET[id]'";
		$result = $this->new_mysql($sql);
		$row = $result->fetch_assoc();

		print "<h2>Edit Chat User</h2>";
		print "<form action=\"index.php\" method=\"post\">
		<input type=\"hidden\" name=\"action\" value=\"update_chat_user\">
		<input type=\"hidden\" name=\"id\" value=\"$_GET[id]\">
		<table class=\"table\">
		<tr><td>First Name:</td><td><input type=\"text\" name=\"fname\" value=\"$row[fname]\" size=40 required></td></tr>
		<tr><td>Last Name:</td><td><input type=\"text\" name=\"lname\" value=\"$row[lname]\" size=40 required></td></tr>
		<tr><td>Email:</td><td><input type=\"text\" name=\"email\" value=\"$row[email]\" size=40 required></td></tr>
		<tr><td>Username:</td><td>$row[uuname]</td></tr>
		<tr><td>Alias:</td><td>$row[alias]</td></tr>
		<tr><td>Password:</td><td><input type=\"password\" name=\"uupass\" size=40 placeholder=\"********\"></td></tr>
		<tr><td colspan=2><input type=\"submit\" class=\"btn btn-primary\" value=\"Update\"></td></tr>
		</table>
		</form>";	
	}

	public function update_chat_user() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }

		$sql = "SELECT `email` FROM `push_chat`.`users` WHERE `email` = '$_POST[email]' AND `id` != '$_POST[id]'";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<br><font color=red>Sorry, the email <b>$_POST[email]</b> is registered with another chat user.</font><br>";
			die;
		}

		if ($_POST['uupass'] != "") {
			$new_pass = md5($_POST['uupass']);
			$pass_sql = ",`uupass` = '$new_pass'";
		}
		$sql = "UPDATE `push_chat`.`users` SET `fname` = '$_POST[fname]', `lname` = '$_POST[lname]', `email` = '$_POST[email]' $pass_sql WHERE `id` = '$_POST[id]'";
                $result = $this->new_mysql($sql);
                if ($result == "TRUE") {
                        print "<br><font color=green>The chat user was updated.</font><br>";
                } else {
                        print "<br><font color=red>The chat user failed to update.</font><br>";
                }
                $this->chat_users();
	}

	public function delete_chat_user() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }

		$sql = "DELETE FROM `push_chat`.`users` WHERE `id` = '$_GET[id]'";
		$result = $this->new_mysql($sql);
                if ($result == "TRUE") {
                        print "<br><font color=green>The chat user was deleted.</font><br>";
                } else {
                        print "<br><font color=red>The chat user failed to delete.</font><br>";
                }
                $this->chat_users();
	}

	public function manage() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }

		print "<h2>Manage Users</h2>
		<table class=\"table\">
		<tr>
			<td><b>Site</b></td>
			<td><b>URL</b></td>
			<td><b>Push Notifications</b></td>
			<td><b>Reports</b></td>
			<td><b>Chat</b></td>
			<td><b>Database</b></td>
			<td><b>Username</b></td>
			<td><b>Password</b></td>
			<td>&nbsp;</td>
		</tr>";
		$sql = "SELECT * FROM ".LOCAL_DB.".`sites` ORDER BY `sub` ASC";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			$i++;
			if ($i % 2) {
				$bgcolor="#D6D6D6";
			} else {
				$bgcolor="#FFFFFF";
			}

			$sub = "$row[sub].";

			print "<tr bgcolor=$bgcolor><td>$row[sub]</td>";

			if ($row['uuname'] == "admin") {
				print "<td>N/A</td>";
			} else {
				print "<td><a href=\"http://$sub".SUBPATH.".theappwizards.com/push/\" target=_blank>$row[sub].".SUBPATH.".theappwizards.com/push/</a></td>";
			}

			switch ($row['database']) {
				case "siberian_appwizard2":
				$d = "Version 1";
				break;

				case "wwsib2_siberian":
				$d = "Version 2";
				break;
			}

			print "
			<td>$row[push]</td>
			<td>$row[reports]</td>
			<td>$row[chat]</td>
			<td>$d</td>
			<td>$row[uuname]</td>
			<td>$row[uupass]</td>
			<td>
			<input type=\"button\" class=\"btn btn-primary\" value=\"Edit\" onclick=\"document.location.href='index.php?action=edit&id=$row[id]'\">
			&nbsp;
			";
			if ($row['uuname'] != "admin") {
				print "<input type=\"button\" class=\"btn btn-danger\" value=\"Delete\" onclick=\"if(confirm('WARNING: You are about to delete $row[sub]')){document.location.href='index.php?action=delete&id=$row[id]'};\">";
			}
			print "
			</td></tr>";
		}
		print "</table>";
	}

	public function edit_site() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }
		$sql = "SELECT * FROM ".LOCAL_DB.".`sites` WHERE `id` = '$_GET[id]'";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {

		if ($row['database'] == "siberian_appwizard2") {
			$app_version = "app";
		} else {
			$app_version = "app2";
		}

                print "<h2>Edit User:</h2>";

                print "<form action=\"index.php\" method=\"post\" enctype=\"multipart/form-data\">
                <input type=\"hidden\" name=\"action\" value=\"update_site\">
		<input type=\"hidden\" name=\"id\" value=\"$_GET[id]\">
                <table class=\"table\">
                <tr><td>Sub Domain</td><td>http://$row[sub].push.theappwizards.com</td></tr>
                <tr><td>PEM File:</td><td><input type=\"file\" name=\"pem\"></td></tr>
		<tr><td colspan=2><font color=blue>The PEM file is required only if you are uploading a new certificate.</font></td></tr>
                <tr><td>PEM Password:</td><td><input type=\"text\" name=\"crypto\" size=40 value=\"$row[crypto]\" required></td></tr>
                <tr><td>Siberian App ID:</td><td><input type=\"text\" name=\"app_id\" size=20 value=\"$row[app_id]\" required></td></tr>
                <tr><td>Logo</td><td><input type=\"file\" name=\"logo\"></td></tr>
		<tr><td colspan=2><font color=blue>The logo is required only if you are updating the logo.</font></td></tr>
                <tr><td>Username (unique):</td><td>$row[uuname]</td></tr>
                <tr><td>Password:</td><td><input type=\"text\" name=\"uupass\" value=\"$row[uupass]\" size=40 required></td></tr>
                <tr><td>Siberian Database:</td><td><select name=\"database\"><option selected>$row[database]</option>
                	<option value=\"siberian_appwizard2\">Version 1</option>
                	<option value=\"wwsib2_siberian\">Version 2</option>
                </select></td></tr>
                <tr><td>Push Notification</td><td><select name=\"push\"><option selected>$row[push]</option><option>No</option><option>Yes</option></select></td></tr>

                <tr><td>Reports:</td><td><select name=\"reports\"><option selected>$row[reports]</option><option>No</option><option>Yes</option></select></td></tr>
		<tr><td>Chat:</td><td><select name=\"chat\"><option selected>$row[chat]</option><option>No</option><option>Yes</option></select></td></tr>
		<tr><td colspan=2>Chat Link: http://push.theappwizards.com/chat/$app_version/$row[app_id]</td></tr>
                <tr><td colspan=2><input type=\"submit\" class=\"btn btn-primary\" value=\"Update User\"><br>
		</td></tr>
                </table>
                </form>";

		}

	}

	public function delete_site() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }

		$sql = "DELETE FROM ".LOCAL_DB.".`sites` WHERE `id` = '$_GET[id]'";
		$result = $this->new_mysql($sql);
		if ($result == "TRUE") {
			print "<br><font color=green>The site was deleted.</font><br>";
		} else {
			print "<br><font color=red>The site failed to delete.</font><br>";
		}
		$this->manage();
	}

	public function update_site() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }


                $fileName = $_FILES['pem']['name'];
                $tmpName  = $_FILES['pem']['tmp_name'];
                $fileSize = $_FILES['pem']['size'];
                $fileType = $_FILES['pem']['type'];

                $fileName2 = $_FILES['logo']['name'];
                $tmpName2  = $_FILES['logo']['tmp_name'];
                $fileSize2 = $_FILES['logo']['size'];
                $fileType2 = $_FILES['logo']['type'];

		if ($fileName != "") {
                        move_uploaded_file("$tmpName", "$fileName");
			$pem = ",`pem` = '$fileName'";

		}

		if ($fileName2 != "") {
                        move_uploaded_file("$tmpName2", "img/$fileName2");
			$logo = ",`logo` = '$fileName2'";
		}
		$sql = "UPDATE ".LOCAL_DB.".`sites` SET `app_id` = '$_POST[app_id]', `crypto` = '$_POST[crypto]', `uupass` = '$_POST[uupass]',`database` = '$_POST[database]',
		`reports` = '$_POST[reports]', `push` = '$_POST[push]', `chat` = '$_POST[chat]' $pem $logo WHERE `id` = '$_POST[id]'";
		$result = $this->new_mysql($sql);
		if ($result == "TRUE") {
			print "<br><font color=green>The site was updated.</font><br>";
		} else {
			print "<br><font color=ed>The site failed to update.</font><br>";
		}
		$this->manage();
	}

	public function create_new_site() {
                $settings = $this->get_settings();
                if ($settings[9] != "Yes") {
                        print "<br><font color=red>ACCESS DENIED</font><br>";
                        die;
                }
		$server_settings = $this->get_server_settings();

		// check for username
		$sql = "SELECT `uuname` FROM ".LOCAL_DB.".`sites` WHERE `uuname` = '$_POST[uuname]'";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<br><font color=red>ERROR: USERNAME already taken. Please click back and select a different username.</font><br>";
			die;
		}

		// check for sub
                $sql = "SELECT `sub` FROM ".LOCAL_DB.".`sites` WHERE `sub` = '$_POST[sub]'";
                $result = $this->new_mysql($sql);
                while ($row = $result->fetch_assoc()) {
                        print "<br><font color=red>ERROR: SUB DOMAIN already taken. Please click back and select a different sub domain.</font><br>";
                        die;
                }


		// check for duplicate files:
                $fileName = $_FILES['pem']['name'];
                $tmpName  = $_FILES['pem']['tmp_name'];
                $fileSize = $_FILES['pem']['size'];
                $fileType = $_FILES['pem']['type'];
				$theFile = PATH . "/" . $fileName;
				if (file_exists($theFile)) {
					print "<br><font color=red>ERROR: PEM file exists. Please rename the file then upload again.</font><br>";
					die;
				}

                $fileName2 = $_FILES['logo']['name'];
                $tmpName2  = $_FILES['logo']['tmp_name'];
                $fileSize2 = $_FILES['logo']['size'];
                $fileType2 = $_FILES['logo']['type'];
                $theFile2 = PATH . "/img/" . $fileName2;
                if (file_exists($theFile2)) {
                        print "<br><font color=red>ERROR: LOGO file exists. Please rename the file then upload again.</font><br>";
						die;
				}



                include_once 'class/xmlapi.php';
				$server_ip = $server_settings[0];
				$xmlapi = new xmlapi($server_ip);
				$domain_user = $server_settings[1];
				$domain_pw = $server_settings[2];

                # switch to cPanel
                $xmlapi->set_debug(1);
                $xmlapi->set_output('json');
                $xmlapi->set_port(2083);
                $xmlapi->password_auth($domain_user,$domain_pw);


                $domain = $_POST['sub'];
                $domain .= ".";
                $domain .= $server_settings[3];  // UPDATE THE DOMAIN
                $result = $xmlapi->api1_query( $domain_user, 'Park', 'park', array($domain));
                $json_data = json_decode($result);

                $new_array = $this->objectToArray($json_data);

                $new_result = $new_array['data']['result'];
                if (preg_match('/successfully/i', $new_result)) {
                        print "<br><font color=green>The domain was created sucessfully.<br></font>";
						// save files now and add to DB
                        move_uploaded_file("$tmpName", "$fileName");
                        move_uploaded_file("$tmpName2", "img/$fileName2");

			$sql = "INSERT INTO ".LOCAL_DB.".`sites` (`sub`,`pem`,`app_id`,`crypto`,`logo`,`uuname`,`uupass`,`database`,`reports`,`push`,`chat`) VALUES
			('$_POST[sub]','$fileName','$_POST[app_id]','$_POST[crypto]','$fileName2','$_POST[uuname]','$_POST[uupass]','$_POST[database]','$_POST[reports]','$_POST[push]','$_POST[chat]')";
			$result = $this->new_mysql($sql);
			if ($result == "TRUE") {
				print "<font color=blue>Config was updated.</font><br>";
			} else {
				print "<font color=red>Config failed to update.</font><br>";
			}

                } else {
                        print "<br><font color=red>The domain failed to be created.</font><br>\n";
                        print "<pre>";
                        print_r($json_data);
                        print "</pre>";

                }



	}

        public function objectToArray( $object )
           {
               if( !is_object( $object ) && !is_array( $object ) )
               {
                   return $object;
               }
               if( is_object( $object ) )
               {
                   $object = get_object_vars( $object );
               }
               //return array_map( 'objectToArray', $object ); // non PHP class way
                return array_map(array($this, 'objectToArray'), $object); // in a class you have to return array as the namespace


           }


        public function logout() {
                session_destroy();
                $this->login('<font color=green><br>You have been logged out.</font>');
        }



}
?>
