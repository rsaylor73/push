<?php

class Reports {

	public $linkID;

	function __construct($linkID){ $this->linkID = $linkID; }

	public function new_mysql($sql) {
		$result = $this->linkID->query($sql) or die($this->linkID->error.__LINE__);
		return $result;
	}

	public function module($type) {
		if(method_exists('Reports', $type)) {
			$this->$type();
		}
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

	private function check_report_access() {
		if ($_SESSION['reports'] != "Yes") {
			$err = "1";
		}
		if ($_SESSION['super'] == "Yes") {
			$err = "";
		}
		if ($err == "1") {
			print "<font color=red>You do not have access to view reports.</font><br>";
			die;
		}
	}

	private function consumers() {
		$this->check_report_access();

		$DB = $this->get_proper_db('1'); // update TBD
		
		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			`c`.`customer_id`,
			`c`.`firstname`,
			`c`.`lastname`,
			`c`.`phonenumber`,
			`c`.`email`,
			`a`.`name`,
			`c`.`civility`,
			DATE_FORMAT(`a`.`created_at`,'%d %b %Y') AS 'registered'


		FROM
			".$DB.".`customer` c, ".$DB.".`application` a


		WHERE
			`c`.`app_id` = `a`.`app_id`
			$app_id

		ORDER BY `a`.`name` ASC

		";

		$result = $this->new_mysql($sql);
		$total_records = $result->num_rows;
		$total_records = $total_records / 20;
		$pages = ceil($total_records);

		if (($pages > 1) && ($_GET['h'] != "n")) {
			$page = $_GET['page'];
			if ($page == "") {
				$page = "1";
			}
			$html = "<div class=\"btn-group\" role=\"group\" aria-label=\"...\">";
			for ($i=0; $i < $pages; $i++) {
				$i2 = $i + 1;
				$html .= "<button type=\"button\" class=\"btn btn-default\">$i2</button>";
			}
			$html .= "</div>";
			print "$html";
		}

		if ($_GET['h'] != "n") {
			print "<h3>Registered Users</h3>";
			print "<i>Click a table heading to sort</i>&nbsp;&nbsp;&nbsp;";
			print "<button class=\"btn\" onclick=\"window.open('index.php?action=reports&type=consumers&h=n')\">
			<i class=\"fa fa-download\" aria-hidden=\"true\"></i>
			</button>
			";
		} else {
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=consumers.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		print "<table class=\"table tablesorter\" id=\"myTable\">";
		print "<thead>";
		print "<tr><th><b>Registration</b></th><th><b>First Name</b></th><th><b>Last Name</b></th><th><b>E-mail</b></th><th><b>Application</b></th><th>&nbsp;</th></tr>";
		print "</thead><tbody>";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[registered]</td><td>$row[firstname]</td><td>$row[lastname]</td><td>$row[email]</td><td>$row[name]</td>
			<td>
			<button class=\"btn\" onclick=\"document.location.href='index.php?action=reports&type=viewcustomer&id=$row[customer_id]'\">
				<i class=\"fa fa-search\" aria-hidden=\"true\"></i>
			</button>
			</td></tr>";
		}
		print "</tbody></table>";
		if ($_GET['h'] != "n") {
		?>
		<script>
		$(document).ready(function() { 
        	$("#myTable").tablesorter(); 
    	} 
		); 
		</script>
		<?php
		}
	}

	private function viewcustomer() {
		$this->check_report_access();

		$DB = $this->get_proper_db('1'); // update TBD

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			`c`.`customer_id`,
			`c`.`firstname`,
			`c`.`lastname`,
			`c`.`phonenumber`,
			`c`.`email`,
			`a`.`name`,
			`c`.`civility`,
			DATE_FORMAT(`a`.`created_at`,'%d %b %Y') AS 'registered'


		FROM
			".$DB.".`customer` c, ".$DB.".`application` a


		WHERE
			`c`.`customer_id` = '$_GET[id]'
			AND `c`.`app_id` = `a`.`app_id`
			$app_id

		";


		print "<h3>View Registered User</h3>
		<button class=\"btn\" onclick=\"window.history.go(-1); return false;\">
			<i class=\"fa fa-backward\" aria-hidden=\"true\"></i>
		</button>
		<table class=\"table\">";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "
			<tr><td>Registered:</td><td>$row[registered]</td></tr>
			<tr><td>Firstname:</td><td>$row[firstname]</td></tr>
			<tr><td>Lastname:</td><td>$row[lastname]</td></tr>
			<tr><td>E-mail:</td><td><a href=\"mailto:$row[email]\">$row[email]</a></td></tr>
			<tr><td>Phone:</td><td>$row[phonenumber]</td></tr>
			<tr><td>Application:</td><td>$row[name]</td></tr>

			";
		}
		print "</table>";

	}

	private function loyalty_stamps() {
		$DB = $this->get_proper_db('1');

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			`a`.`name` AS 'app_name',
			`lcp`.`name` AS 'employee',
			`lc`.`number_of_points`,
			`c`.`firstname`,
			`c`.`lastname`,
			`c`.`email`,
			`lc`.`name` AS 'card_name',
			`lc`.`created_at`,
			`lc`.`card_id`,
			`c`.`customer_id`



		FROM
			".$DB.".`loyalty_card` lc,
			".$DB.".`loyalty_card_customer_log` lccl,
			".$DB.".`loyalty_card_password` lcp,
			".$DB.".`application` a,
			".$DB.".`customer` c

		WHERE
			`lc`.`card_id` = `lccl`.`card_id`
			AND `lccl`.`password_id` = `lcp`.`password_id`
			AND `lcp`.`app_id` = `a`.`app_id`
			$app_id
			AND `lccl`.`customer_id` = `c`.`customer_id`

		GROUP BY `lccl`.`customer_id`

		ORDER BY `a`.`name` ASC
		";

		if ($_GET['h'] != "n") {
			print "<h3>Loyalty Programs</h3>";
			print "<i>Click a table heading to sort</i>&nbsp;&nbsp;&nbsp;";
			print "<button class=\"btn\" onclick=\"window.open('index.php?action=reports&type=loyalty_stamps&h=n')\">
			<i class=\"fa fa-download\" aria-hidden=\"true\"></i>
			</button>
			";
		} else {
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=layalty.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
		}

		print "<table class=\"table tablesorter\" id=\"myTable\">";
		print "<thead>";
		print "<tr><th>Created</th><th>Application</th><th>Employee</th><th>Points</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Card Name</th><th></th></tr>
		</thead><tbody>";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[created_at]</td><td>$row[app_name]</td><td>$row[employee]</td><td>$row[number_of_points]</td><td>$row[firstname]</td><td>$row[lastname]</td>
			<td>$row[email]</td><td>$row[card_name]</td><td>
			<button class=\"btn\" onclick=\"document.location.href='index.php?action=reports&type=loyalty_stamps_view&id=$row[customer_id]'\">
				<i class=\"fa fa-search\" aria-hidden=\"true\"></i>
			</button></td></tr>";
		}
		print "</tbody></table>";
		if ($_GET['h'] != "n") {
		?>
		<script>
		$(document).ready(function() { 
        	$("#myTable").tablesorter(); 
    	} 
		); 
		</script>
		<?php
		}
	}

	private function loyalty_stamps_view() {

		$DB = $this->get_proper_db('1');

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			`a`.`name` AS 'app_name',
			`lcp`.`name` AS 'employee',
			`lc`.`number_of_points`,
			`c`.`firstname`,
			`c`.`lastname`,
			`c`.`email`,
			`lc`.`name` AS 'card_name',
			`lc`.`created_at`



		FROM
			".$DB.".`loyalty_card` lc,
			".$DB.".`loyalty_card_customer_log` lccl,
			".$DB.".`loyalty_card_password` lcp,
			".$DB.".`application` a,
			".$DB.".`customer` c

		WHERE
			`lc`.`card_id` = `lccl`.`card_id`
			AND `lccl`.`password_id` = `lcp`.`password_id`
			AND `lcp`.`app_id` = `a`.`app_id`
			$app_id
			AND `lccl`.`customer_id` = `c`.`customer_id`
			AND `lccl`.`customer_id` = '$_GET[id]'

		GROUP BY `lccl`.`customer_id`

		";	


		print "<h3>View Loyalty Programs</h3>
		<button class=\"btn\" onclick=\"window.history.go(-1); return false;\">
			<i class=\"fa fa-backward\" aria-hidden=\"true\"></i>
		</button>
		<table class=\"table\">";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "
			<tr><td>Created:</td><td>$row[created_at]</td></tr>
			<tr><td>Application:</td><td>$row[name]</td></tr>
			<tr><td>Employee:</td><td>$row[employee]</td></tr>
			<tr><td>Points:</td><td>$row[number_of_points]</td></tr>
			<tr><td>Firstname:</td><td>$row[firstname]</td></tr>
			<tr><td>Lastname:</td><td>$row[lastname]</td></tr>
			<tr><td>E-mail:</td><td><a href=\"mailto:$row[email]\">$row[email]</a></td></tr>
			<tr><td>Card Name:</td><td>$row[card_name]</td></tr>

			";
		}
		print "</table>";
	}

	private function loyalty_awards() {
		$DB = $this->get_proper_db('1');

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			MAX(`lccl`.`created_at`) AS 'created_at',
			`lccl`.`customer_id`,
			`c`.`firstname`,
			`c`.`lastname`,
			`c`.`email`,
			`a`.`name`,
			`lcp`.`name` AS 'employee',
			SUM(`lccl`.`number_of_points`) AS 'points',
			`lc`.`name` AS 'card_name',
			`c`.`customer_id`


		FROM
			".$DB.".`loyalty_card_customer_log` lccl,
			".$DB.".`loyalty_card` lc,
			".$DB.".`customer` c,
			".$DB.".`application` a,
			".$DB.".`loyalty_card_password` lcp

		WHERE
			`lccl`.`card_id` = `lc`.`card_id`
			AND `lccl`.`customer_id` = `c`.`customer_id`
			AND `c`.`app_id` = `a`.`app_id`
			$app_id
			AND `lccl`.`password_id` = `lcp`.`password_id`

		GROUP BY `lccl`.`customer_id`

		ORDER BY `a`.`name` ASC
		";

		if ($_GET['h'] != "n") {
			print "<h3>Loyalty Program Detail</h3>";
			print "<i>Click a table heading to sort</i>&nbsp;&nbsp;&nbsp;";
			print "<button class=\"btn\" onclick=\"window.open('index.php?action=reports&type=loyalty_awards&h=n')\">
			<i class=\"fa fa-download\" aria-hidden=\"true\"></i>
			</button>
			";
		} else {
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=layalty.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		print "<table class=\"table tablesorter\" id=\"myTable\">";
		print "<thead>
		<tr><th>Last Used</th><th>Points</th><th>Firstname</th><th>Lastname</th><th>E-mail</th><th>Application</th><th>Card Name</th><th>Employee</th><th></th></tr>";
		print "<tbody>";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[created_at]</td><td>$row[points]</td><td>$row[firstname]</td><td>$row[lastname]</td><td>$row[email]</td>
			<td>$row[name]</td><td>$row[card_name]</td><td>$row[employee]</td>
			<td>
			<button class=\"btn\" onclick=\"document.location.href='index.php?action=reports&type=loyalty_awards_view&id=$row[customer_id]'\">
				<i class=\"fa fa-search\" aria-hidden=\"true\"></i>
			</button>
			</td>
			</tr>";
		}
		print "</tbody>";
		print "</table>";

		if ($_GET['h'] != "n") {
		?>
		<script>
		$(document).ready(function() { 
        	$("#myTable").tablesorter(); 
    	} 
		); 
		</script>
		<?php
		}
	}

	private function loyalty_awards_view() {
		$DB = $this->get_proper_db('1');

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			`lccl`.`created_at` AS 'created_at',
			`lccl`.`customer_id`,
			`c`.`firstname`,
			`c`.`lastname`,
			`c`.`email`,
			`a`.`name`,
			`lcp`.`name` AS 'employee',
			`lccl`.`number_of_points` AS 'points',
			`lc`.`name` AS 'card_name',
			`c`.`customer_id`


		FROM
			".$DB.".`loyalty_card_customer_log` lccl,
			".$DB.".`loyalty_card` lc,
			".$DB.".`customer` c,
			".$DB.".`application` a,
			".$DB.".`loyalty_card_password` lcp

		WHERE
			`lccl`.`card_id` = `lc`.`card_id`
			AND `lccl`.`customer_id` = '$_GET[id]'
			AND `lccl`.`customer_id` = `c`.`customer_id`
			AND `c`.`app_id` = `a`.`app_id`
			$app_id
			AND `lccl`.`password_id` = `lcp`.`password_id`

		ORDER BY `a`.`name` ASC
		";

		print "<h3>View Loyalty Program Detail</h3>
		<button class=\"btn\" onclick=\"window.history.go(-1); return false;\">
			<i class=\"fa fa-backward\" aria-hidden=\"true\"></i>
		</button>
		<table class=\"table\">";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "
			<tr><td>Last Used:</td><td>$row[created_at]</td></tr>
			<tr><td>Points:</td><td>$row[points]</td></tr>
			<tr><td>Firstname:</td><td>$row[firstname]</td></tr>
			<tr><td>Lastname:</td><td>$row[lastname]</td></tr>
			<tr><td>E-mail:</td><td><a href=\"mailto:$row[email]\">$row[email]</a></td></tr>
			<tr><td>Application:</td><td>$row[name]</td></tr>
			<tr><td>Employee:</td><td>$row[employee]</td></tr>
			<tr><td>Card Name:</td><td>$row[card_name]</td></tr>
			<tr><td colspan=2><hr></td></tr>

			";
		}
		print "</table>";
	}

	private function coupon() {

		$DB = $this->get_proper_db('1');

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			MAX(`pc`.`created_at`) AS 'used',
			`p`.`title`,
			`p`.`description`,
			`p`.`conditions`,
			`c`.`firstname`,
			`c`.`lastname`,
			`c`.`email`,
			`a`.`name`,
			`c`.`customer_id`

		FROM
			".$DB.".`promotion` p,
			".$DB.".`promotion_customer` pc,
			".$DB.".`customer` c,
			".$DB.".`application` a

		WHERE
			`p`.`promotion_id` = `pc`.`promotion_id`
			AND `pc`.`customer_id` = `c`.`customer_id`
			AND `c`.`app_id` = `a`.`app_id`
			$app_id

		GROUP BY `pc`.`customer_id`
		";

		if ($_GET['h'] != "n") {
			print "<h3>Coupon</h3>";
			print "<i>Click a table heading to sort</i>&nbsp;&nbsp;&nbsp;";
			print "<button class=\"btn\" onclick=\"window.open('index.php?action=reports&type=coupon&h=n')\">
			<i class=\"fa fa-download\" aria-hidden=\"true\"></i>
			</button>
			";
		} else {
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=coupon.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		print "<table class=\"table tablesorter\" id=\"myTable\">";
		print "<thead>
		<tr><th>Used</th><th>Coupon Name</th><th>Description</th><th>Firstname</th><th>Lastname</th><th>E-mail</th><th>Application</th><th></th></tr>
		<tbody>";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[used]</td><td>$row[title]</td><td>$row[description]</td><td>$row[firstname]</td><td>$row[lastname]</td><td>$row[email]</td><td>$row[name]</td>
			<td>
			<button class=\"btn\" onclick=\"document.location.href='index.php?action=reports&type=coupon_view&id=$row[customer_id]'\">
				<i class=\"fa fa-search\" aria-hidden=\"true\"></i>
			</button>
			</td>
			</tr>";
		}
		print "</tbody></table>";

		if ($_GET['h'] != "n") {
		?>
		<script>
		$(document).ready(function() { 
        	$("#myTable").tablesorter(); 
    	} 
		); 
		</script>
		<?php
		}
	}

	private function coupon_view() {

		$DB = $this->get_proper_db('1');

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			`pc`.`created_at` AS 'used',
			`p`.`title`,
			`p`.`description`,
			`p`.`conditions`,
			`c`.`firstname`,
			`c`.`lastname`,
			`c`.`email`,
			`a`.`name`,
			`c`.`customer_id`

		FROM
			".$DB.".`promotion` p,
			".$DB.".`promotion_customer` pc,
			".$DB.".`customer` c,
			".$DB.".`application` a

		WHERE
			`p`.`promotion_id` = `pc`.`promotion_id`
			AND `pc`.`customer_id` = `c`.`customer_id`
			AND `c`.`customer_id` = '$_GET[id]'
			AND `c`.`app_id` = `a`.`app_id`
			$app_id

		";

		print "<h3>View Coupon</h3>
		<button class=\"btn\" onclick=\"window.history.go(-1); return false;\">
			<i class=\"fa fa-backward\" aria-hidden=\"true\"></i>
		</button>
		<table class=\"table\">";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "
			<tr><td>Last Used:</td><td>$row[used]</td></tr>
			<tr><td>Coupon Name:</td><td>$row[title]</td></tr>
			<tr><td>Description:</td><td>$row[description]</td></tr>
			<tr><td>Firstname:</td><td>$row[firstname]</td></tr>
			<tr><td>Lastname:</td><td>$row[lastname]</td></tr>
			<tr><td>E-mail:</td><td><a href=\"mailto:$row[email]\">$row[email]</a></td></tr>
			<tr><td>Application:</td><td>$row[name]</td></tr>
			<tr><td colspan=2><hr></td></tr>

			";
		}
		print "</table>";
	}

	private function ecommerce() {
		$DB = $this->get_proper_db('1');

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			`a`.`name` AS 'app_name',
			`mo`.`number`,
			`mo`.`payment_method`,
			`mo`.`delivery_method`,
			`mo`.`customer_firstname`,
			`mo`.`customer_lastname`,
			`mo`.`customer_email`,
			`mo`.`customer_phone`,
			`mo`.`total`,
			`mo`.`created_at`,
			`mo`.`mcommerce_id`

		FROM
			".$DB.".`mcommerce_order` mo,
			".$DB.".`mcommerce` m,
			".$DB.".`mcommerce_store` ms,
			".$DB.".`application_option_value` aov,
			".$DB.".`application` a

		WHERE
			`mo`.`store_id` = `ms`.`store_id`
			AND `ms`.`mcommerce_id` = `m`.`mcommerce_id`
			AND `m`.`value_id` = `aov`.`value_id`
			AND `aov`.`app_id` = `a`.`app_id`
			$app_id

		ORDER BY `a`.`name` ASC, `mo`.`created_at` DESC
		";

		if ($_GET['h'] != "n") {
			print "<h3>Ecommerce</h3>";
			print "<i>Click a table heading to sort</i>&nbsp;&nbsp;&nbsp;";
			print "<button class=\"btn\" onclick=\"window.open('index.php?action=reports&type=ecommerce&h=n')\">
			<i class=\"fa fa-download\" aria-hidden=\"true\"></i>
			</button>
			";
		} else {
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=ecommerce.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		print "<table class=\"table tablesorter\" id=\"myTable\">";
		print "<thead>		
		<tr><th>Date</th><th>Number</th><th>Payment Method</th><th>Delivery Method</th><th>Firstname</th><th>Lastname</th><th>Phone</th><th>Total</th><th>Application</th><th></th></tr>
		<tbody>";

		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[created_at]</td><td>$row[number]</td><td>$row[payment_method]</td><td>$row[delivery_method]</td><td>$row[customer_firstname]</td><td>$row[customer_lastname]</td>
			<td>$row[customer_phone]</td><td>$row[total]</td><td>$row[app_name]</td>
			<td>
			<button class=\"btn\" onclick=\"document.location.href='index.php?action=reports&type=ecommerce_view&id=$row[mcommerce_id]&n=$row[number]'\">
				<i class=\"fa fa-search\" aria-hidden=\"true\"></i>
			</button>
			</td>
			</tr>";
		}
		print "</tbody></table>";

		if ($_GET['h'] != "n") {
		?>
		<script>
		$(document).ready(function() { 
        	$("#myTable").tablesorter(); 
    	} 
		); 
		</script>
		<?php
		}
	}

	private function ecommerce_view() {
		$DB = $this->get_proper_db('1');

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			`a`.`name` AS 'app_name',
			`mo`.`number`,
			`mo`.`payment_method`,
			`mo`.`delivery_method`,
			`mo`.`customer_firstname`,
			`mo`.`customer_lastname`,
			`mo`.`customer_email`,
			`mo`.`customer_phone`,
			`mo`.`total`,
			`mo`.`created_at`,
			`mo`.`mcommerce_id`

		FROM
			".$DB.".`mcommerce_order` mo,
			".$DB.".`mcommerce` m,
			".$DB.".`mcommerce_store` ms,
			".$DB.".`application_option_value` aov,
			".$DB.".`application` a

		WHERE
			`mo`.`mcommerce_id` = '$_GET[id]'
			AND `mo`.`number` = '$_GET[n]'
			AND `mo`.`store_id` = `ms`.`store_id`
			AND `ms`.`mcommerce_id` = `m`.`mcommerce_id`
			AND `m`.`value_id` = `aov`.`value_id`
			AND `aov`.`app_id` = `a`.`app_id`
			$app_id

		ORDER BY `a`.`name` ASC, `mo`.`created_at` DESC
		";

		print "<h3>View Ecommerce</h3>
		<button class=\"btn\" onclick=\"window.history.go(-1); return false;\">
			<i class=\"fa fa-backward\" aria-hidden=\"true\"></i>
		</button>
		<table class=\"table\">";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "
			<tr><td>Date:</td><td>$row[created_at]</td></tr>
			<tr><td>Number:</td><td>$row[number]</td></tr>
			<tr><td>Payment Method:</td><td>$row[payment_method]</td></tr>
			<tr><td>Delivery Method:</td><td>$row[delivery_method]</td></tr>
			<tr><td>Firstname:</td><td>$row[customer_firstname]</td></tr>
			<tr><td>Lastname:</td><td>$row[customer_lastname]</td></tr>
			<tr><td>Phone:</td><td>$row[customer_phone]</td></tr>
			<tr><td>E-mail:</td><td><a href=\"mailto:$row[customer_email]\">$row[customer_email]</a></td></tr>
			<tr><td>Total:</td><td>$row[total]</td></tr>
			<tr><td>Application:</td><td>$row[app_name]</td></tr>

			";
		}
		print "</table>";
	}

	private function log() {
		$DB = $this->get_proper_db('1');

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			`a`.`name` AS 'app_name',
			`l`.`remote_addr`,
			`l`.`visited_at`,
			`l`.`customer_id`,
			`l`.`device_name`,
			`l`.`log_id`

		FROM
			".$DB.".`log` l,
			".$DB.".`application` a


		WHERE
			`l`.`app_id` = `a`.`app_id`
			$app_id

		ORDER BY `a`.`name` ASC, `l`.`visited_at` DESC

		";

		if ($_GET['h'] != "n") {
			print "<h3>Log</h3>";
			print "<i>Click a table heading to sort</i>&nbsp;&nbsp;&nbsp;";
			print "<button class=\"btn\" onclick=\"window.open('index.php?action=reports&type=log&h=n')\">
			<i class=\"fa fa-download\" aria-hidden=\"true\"></i>
			</button>
			";
		} else {
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=log.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		print "<table class=\"table tablesorter\" id=\"myTable\">";
		print "<thead>			
		<tr><th>Visited</th><th>IP</th><th>Application</th><th>Device</th><th>Firstname</th><th>Lastname</th><th>E-mail</th><th></th></tr>
		<tbody>";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			if ($row['customer_id'] != "") {
				$sql2 = "
				SELECT
					`c`.`firstname`,
					`c`.`lastname`,
					`c`.`email`

				FROM
					`customer` c

				WHERE
					`c`.`customer_id` = '$row[customer_id]'
				";
				$firstname = "";
				$lastname = "";
				$email = "";

				$result2 = $this->new_mysql($sql2);
				while ($row2 = $result2->fetch_assoc()) {
					$firstname = $row2['firstname'];
					$lastname = $row2['lastname'];
					$email = $row2['email'];
				}
			}
			print "<tr><td>$row[visited_at]</td><td>$row[remote_addr]</td><td>$row[app_name]</td><td>$row[device_name]</td><td>$firstname</td><td>$lastname</td><td>$email</td>
			<td>
			<button class=\"btn\" onclick=\"document.location.href='index.php?action=reports&type=log_view&id=$row[log_id]'\">
				<i class=\"fa fa-search\" aria-hidden=\"true\"></i>
			</button>
			</td>
			</tr>";
		}
		print "</tbody></table>";

		if ($_GET['h'] != "n") {
		?>
		<script>
		$(document).ready(function() { 
        	$("#myTable").tablesorter(); 
    	} 
		); 
		</script>
		<?php
		}
	}

	private function log_view() {
		$DB = $this->get_proper_db('1');

		if ($_SESSION['app_id'] != "") {
			$app_id = "AND `a`.`app_id` = '$_SESSION[app_id]'";
		}

		$sql = "
		SELECT
			`a`.`name` AS 'app_name',
			`l`.`remote_addr`,
			`l`.`visited_at`,
			`l`.`customer_id`,
			`l`.`device_name`,
			`l`.`log_id`

		FROM
			".$DB.".`log` l,
			".$DB.".`application` a


		WHERE
			`l`.`log_id` = '$_GET[id]'
			AND `l`.`app_id` = `a`.`app_id`
			$app_id

		";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			if ($row['customer_id'] != "") {
				$sql2 = "
				SELECT
					`c`.`firstname`,
					`c`.`lastname`,
					`c`.`email`

				FROM
					`customer` c

				WHERE
					`c`.`customer_id` = '$row[customer_id]'
				";
				$firstname = "";
				$lastname = "";
				$email = "";

				$result2 = $this->new_mysql($sql2);
				while ($row2 = $result2->fetch_assoc()) {
					$firstname = $row2['firstname'];
					$lastname = $row2['lastname'];
					$email = $row2['email'];
				}
			}
			// here
			print "<h3>View Log</h3>
			<button class=\"btn\" onclick=\"window.history.go(-1); return false;\">
			<i class=\"fa fa-backward\" aria-hidden=\"true\"></i>
			</button>
			<table class=\"table\">";
			print "
			<tr><td>Visited:</td><td>$row[visited_at]</td></tr>
			<tr><td>IP:</td><td>$row[remote_addr]</td></tr>
			<tr><td>Application:</td><td>$row[app_name]</td></tr>
			<tr><td>Device:</td><td>$row[device_name]</td></tr>

			<tr><td>Firstname:</td><td>$firstname</td></tr>
			<tr><td>Lastname:</td><td>$lastname</td></tr>
			<tr><td>E-mail:</td><td><a href=\"mailto:$email\">$email</a></td></tr>

			";
			print "</table>";
		}
	}








}
?>