<?php

include "settings.php";
include "templates/header.phtml";
include "templates/nav.phtml";
$check_login = $push->check_login();
if ($check_login != "TRUE") {
	$push->login($null);
	die;
}

if (($_GET['action'] == "") && ($_POST['action'] == "")) {
        $push->dashboard();
}

if ($_GET['action'] == "dashboard") {
	$push->dashboard();
}

if ($_GET['action'] == "push") {
	$push->push_form();
}

if ($_GET['action'] == "android") {
	$push->push_form_android();
}

if ($_POST['action'] == "send_push") {
	$push->send_push();
}
if ($_POST['action'] == "send_push_android") {
	$push->send_push_android();
}

if ($_GET['action'] == "new_push_site") {
	$push->new_push_site();
}
if ($_POST['action'] == "create_new_site") {
	$push->create_new_site();
}
if ($_GET['action'] == "manage") {
	$push->manage();
}
if ($_GET['action'] == "edit") {
	$push->edit_site();
}
if ($_POST['action'] == "update_site") {
	$push->update_site();
}
if ($_GET['action'] == "delete") {
	$push->delete_site();
}
if ($_GET['action'] == "logout") {
	$push->logout();
}


if ($_GET['action'] == "get_template_block") {
	$push->get_template_block($_GET['id']);
}
if ($_POST['action'] == "update_block") {
	$push->update_block();
}

include "templates/footer.phtml";
?>
