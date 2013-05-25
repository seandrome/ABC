<?php
	include('inc/functions.php');
		session_start();
		unset($_SESSION["logged"]);
		session_destroy();
		redirect('login.php');
?>