<?php

class Reports {

	public $linkID;

	function __construct($linkID){ $this->linkID = $linkID; }

	public function new_mysql($sql) {
		$result = $this->linkID->query($sql) or die($this->linkID->error.__LINE__);
		return $result;
	}

	public function module($type) {
		switch ($type) {
			case "consumers":
				$this->consumers();
			break;

			case "loyalty_stamps":
				$this->loyalty_stamps();
			break;

			case "loyalty_awards":
				$this->loyalty_awards();
			break;

			case "coupon":
				$this->coupon();
			break;

			case "ecommerce":
				$this->ecommerce();
			break;
		}
	}



	private function get_proper_db($db) {
		switch ($db) {
			case "1":
			$name = DB1;
			break;

			case "2":
			$name = DB2;
			break;
		}
		return $name;
	}

	private function consumers() {
		$DB = $this->get_proper_db('1'); // update TBD
		
		$sql = "
		SELECT
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

		ORDER BY `a`.`name` ASC

		";
		print "<h3>Customer</h3>";
		print "<table class=\"table\">";
		print "<tr><td><b>Registration</b></td><td><b>First Name</b></td><td><b>Last Name</b></td><td><b>Civility</b></td><td><b>E-mail</b></td><td><b>Application</b></td></tr>";
		$result = $this->new_mysql($sql);
		while ($row = $result->fetch_assoc()) {
			print "<tr><td>$row[registered]</td><td>$row[firstname]</td><td>$row[lastname]</td><td>$row[civility]</td><td>$row[email]</td><td>$row[name]</td></tr>";
		}
		print "</table>";
	}

	private function loyalty_stamps() {
		$DB = $this->get_proper_db('1');

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

		ORDER BY `a`.`name` ASC, `mo`.`created_at` ASC
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













}
?>