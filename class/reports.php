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
			`c`.`email`



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

		";

		print "<pre>$sql</pre>";

	}


















}
?>