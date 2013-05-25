<?php

	$domain = $_SERVER['HTTP_HOST'];
	echo $domain;
	return;
	
	include('config.php');
	if(!isset($_GET['key'])){
		exit;
	}
	
	if(isset($_GET['key'])){
		if(empty($_GET['key'])){
			exit;
		}
		else {
			$key = $_GET['key'];
			$query = mysql_query("SELECT * FROM blog_id where blog_id='$key'");
			$checkrow = mysql_num_rows($query);
			if($checkrow > 0){
				echo '1';
			} else {
				exit;
			}
		}
	}
?>