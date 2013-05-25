<?php

    require_once("settings.inc");    
    
    if (file_exists($config_file_path)) {        
		header("location: ".$application_start_file);
        exit;
	}
       
?>	


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Installation Guide</title>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
	<link rel="stylesheet" type="text/css" href="img/install.css">
</head>


<body>
	<div id="wrap">
		<div id="top">
			<div class="title">
				New Installation of <?=$application_name;?>
			</div>
		</div>
		
			<div id="main">
				<div class="subtitle">Enter Database Information</div>
		
				<form method="post" action="install2.php">
					<input type="hidden" name="submit" value="step2" />
					<div id="formtxt">
						<div class="wraptxt">Database Host</div>
						<input type="text" class="input" value="localhost" name="database_host"/>
						<br class="spacer" />
					</div>
				
					<div id="formtxt">
						<div class="wraptxt">Database Name</div>
						<input type="text" class="input" name="database_name"/>
						<br class="spacer" />
					</div>
				
					<div id="formtxt">
						<div class="wraptxt">Database Username</div>
						<input type="text" class="input" name="database_username"/>
						<br class="spacer" />
					</div>
					
					<div id="formtxt">
						<div class="wraptxt">Database Password</div>
						<input type="text" class="input" name="database_password"/>
						<br class="spacer" />
					</div>
					
					<div class="subtitle">Enter Admin Panel Login Information</div>
					<div id="formtxt">
						<div class="wraptxt">User Name Admin</div>
						<input type="text" class="input" name="user_name"/>
						<br class="spacer" />
					</div>
					
					<div id="formtxt">
						<div class="wraptxt">Password Admin</div>
						<input type="text" class="input" class="password" name="password"/>
						<br class="spacer" />
					</div>
					
					<center><br />
					<input type="submit" class="button" name="btn_submit" value="Continue">
					</center>
					
				</form>
			</div>
	</div>


</body>
</html>
