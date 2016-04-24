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
		print "<h3>Customer</h3>";
		print "<table class=\"table\">";
		print "<tr><td><b>Registration</b></td><td><b>First Name</b></td><td><b>Last Name</b></td><td><b>Civility</b></td><td><b>E-mail</b></td><td><b>Application</b></td><td>&nbsp;</td></tr>";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[registered]</td><td>$row[firstname]</td><td>$row[lastname]</td><td>$row[civility]</td><td>$row[email]</td><td>$row[name]</td>
			<td>
			<button class=\"btn\" onclick=\"document.location.href='index.php?action=reports&type=viewcustomer&id=$row[customer_id]'\">
				<i class=\"fa fa-search\" aria-hidden=\"true\"></i>
			</button>
			</td></tr>";
		}
		print "</table>";
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


		print "<h3>View Customer</h3>
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
			<tr><td>Civility:</td><td>$row[civility]</td></tr>
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

		GROUP BY `lccl`.`customer_id`

		ORDER BY `a`.`name` ASC
		";

		print "<h3>Loyalty Stamps</h3>";
		print "<table class=\"table\">";
		print "<tr><td>Created</td><td>Application</td><td>Employee</td><td>Points</td><td>First Name</td><td>Last Name</td><td>Email</td><td>Card Name</td></tr>";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[created_at]</td><td>$row[app_name]</td><td>$row[employee]</td><td>$row[number_of_points]</td><td>$row[firstname]</td><td>$row[lastname]</td><td>$row[email]</td><td>$row[card_name]</td></tr>";
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
			`lc`.`name` AS 'card_name'


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

		print "<h3>Loyalty Awards</h3>";
		print "<table class=\"table\">
		<tr><td>Last Used</td><td>Points</td><td>Firstname</td><td>Lastname</td><td>E-mail</td><td>Application</td><td>Card Name</td><td>Employee</td></tr>";

		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[created_at]</td><td>$row[points]</td><td>$row[firstname]</td><td>$row[lastname]</td><td>$row[email]</td>
			<td>$row[name]</td><td>$row[card_name]</td><td>$row[employee]</td></tr>";
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
			`a`.`name`

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

		print "<table class=\"table\">
		<tr><td>Used</td><td>Coupon Name</td><td>Description</td><td>Firstname</td><td>Lastname</td><td>E-mail</td><td>Application</td></tr>";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[used]</td><td>$row[title]</td><td>$row[description]</td><td>$row[firstname]</td><td>$row[lastname]</td><td>$row[email]</td><td>$row[name]</td></tr>";
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
			`mo`.`created_at`

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

		print "<table class=\"table\">
		<tr><td>Date</td><td>Number</td><td>Payment Method</td><td>Delivery Method</td><td>Firstname</td><td>Lastname</td><td>Phone</td><td>Total</td><td>Application</td></tr>";

		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[created_at]</td><td>$row[number]</td><td>$row[payment_method]</td><td>$row[delivery_method]</td><td>$row[customer_firstname]</td><td>$row[customer_lastname]</td>
			<td>$row[customer_phone]</td><td>$row[total]</td><td>$row[app_name]</td></tr>";
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
			`l`.`device_name`

		FROM
			".$DB.".`log` l,
			".$DB.".`application` a


		WHERE
			`l`.`app_id` = `a`.`app_id`
			$app_id

		ORDER BY `a`.`name` ASC, `l`.`visited_at` DESC

		";

		print "<table class=\"table\">
		<tr><td>Visited</td><td>IP</td><td>Application</td><td>Device</td><td>Firstname</td><td>Lastname</td><td>E-mail</td></tr>";
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
			print "<tr><td>$row[visited_at]</td><td>$row[remote_addr]</td><td>$row[app_name]</td><td>$row[device_name]</td><td>$firstname</td><td>$lastname</td><td>$email</td></tr>";
		}
		print "</table>";
	}










}
?>