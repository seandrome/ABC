<?php

    require_once("settings.inc");    
    
    if (file_exists($config_file_path)) {        
		header("location: ".$application_start_file);
        exit;
	}
    
	$completed = false;
	$error_mg  = array();	
	
	if ($_POST['submit'] == "step2") {

foreach ($_POST as $key => $value) {
$$key = $value;
}
		
		if (empty($database_host)){
			$error_mg[] = "Database host can not be empty! Please re-enter. <br />";	
		}
		
		if (empty($database_name)){
			$error_mg[] = "Database name can not be empty! Please re-enter.<br />";	
		}
		
		if (empty($database_username)){
			$error_mg[] = "Database username can not be empty! Please re-enter.<br />";	
		}
		
		if (empty($database_password)){
			//$error_mg[] = "Database password can not be empty! Please re-enter. <br />";	
		}
		
		if (empty($user_name)){
			$error_mg[] = "User Name can not be empty! Please re-enter. <br />";	
		}
		
		if (empty($password)){
			$error_mg[] = "password can not be empty! Please re-enter. <br />";	
		}
		
		


		
		if(empty($error_mg)){
		
			$config_file = file_get_contents($config_file_default);
			$config_file = str_replace("_DB_HOST_", $database_host, $config_file);
			$config_file = str_replace("_DB_NAME_", $database_name, $config_file);
			$config_file = str_replace("_DB_USER_", $database_username, $config_file);
			$config_file = str_replace("_DB_PASSWORD_", $database_password, $config_file);
			$config_file = str_replace("_USERNAME_", $user_name, $config_file);
			$config_file = str_replace("_PASSWORD_", $password, $config_file);

			
			$f = fopen($config_file_path, "w+");
			if (fwrite($f, $config_file) > 0){
                $link = mysql_connect($database_host, $database_username, $database_password);
				if($link){					
					if (mysql_select_db($database_name)) {       


                        if(false == ($db_error = apphp_db_install($database_name, $sql_dump))){
                            $error_mg[] = "Could not read file ".$sql_dump."! Please check if the file exists.";                            
                            unlink($config_file_path);
                        }else{
                            // additional operations, like setting up admin passwords etc.
							// ...
                            $completed = true;                            
                        }

					} else {
						$error_mg[] = "Database connecting error! Check your database exists.</span><br/>";
                        @unlink($config_file_path);
					}
				} else {
					$error_mg[] = "Database connecting error! Check your connection parameters.</span><br/>";
                    @unlink($config_file_path);
				}
			} else {				
				$error_mg[] = "Can not open configuration file ".$config_file_directory.$config_file_name;				
			}
			@fclose($f);			
		}
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
				<div class="subtitle">
					<center><strong>
					Follow the wizard to setup your database
					</strong></center>
				</div>
		
				<? if(!$completed){
					echo "<div id='warning' >";
					foreach($error_mg as $msg){
						echo $msg;
					}
					echo "</div>";
				?>
				
				<center>
				<input type="button" class="button" value="Back" name="submit" onclick="javascript: history.go(-1);">
				<input type="button" class="button" value="Retry" name="submit" onclick="javascript: location.reload();">
				</center>
				
				<? } else {?>
					<div id='warning' >
					<b>Step 2. Installation Completed</b><br />
					The <?=$config_file_path;?> file was sucessfully created.<br /><br />
					<b>!!! For security reasons, please remove install/ folder from your server.</b><br /><br />
					<? if($application_start_file != ""){ ?><a href="<?=$application_start_file;?>">Proceed to login page</a><? } ?>
					</div>
				<? } ?>

	
			</div>
	</div>

    
        
						
							
						                  
</body>
</html>
<? 


  function apphp_db_install($database, $sql_file) {
    $db_error = false;

    if (!@apphp_db_select_db($database)) {
      if (@apphp_db_query('create database ' . $database)) {
        apphp_db_select_db($database);
      } else {
        $db_error = mysql_error();
        return false;		
      }
    }

    if (!$db_error) {
      if (file_exists($sql_file)) {
        $fd = fopen($sql_file, 'rb');
        $restore_query = fread($fd, filesize($sql_file));
         fclose($fd);
      } else {
          $db_error = 'SQL file does not exist: ' . $sql_file;
          return false;
      }
		
      $sql_array = array();
      $sql_length = strlen($restore_query);
      $pos = strpos($restore_query, ';');
      for ($i=$pos; $i<$sql_length; $i++) {
        if ($restore_query[0] == '#') {
          $restore_query = ltrim(substr($restore_query, strpos($restore_query, "\n")));
          $sql_length = strlen($restore_query);
          $i = strpos($restore_query, ';')-1;
          continue;
        }
        if ($restore_query[($i+1)] == "\n") {
          for ($j=($i+2); $j<$sql_length; $j++) {
            if (trim($restore_query[$j]) != '') {
              $next = substr($restore_query, $j, 6);
              if ($next[0] == '#') {
                // find out where the break position is so we can remove this line (#comment line)
                for ($k=$j; $k<$sql_length; $k++) {
                  if ($restore_query[$k] == "\n") break;
                }
                $query = substr($restore_query, 0, $i+1);
                $restore_query = substr($restore_query, $k);
                // join the query before the comment appeared, with the rest of the dump
                $restore_query = $query . $restore_query;
                $sql_length = strlen($restore_query);
                $i = strpos($restore_query, ';')-1;
                continue 2;
              }
              break;
            }
          }
          if ($next == '') { // get the last insert query
            $next = 'insert';
          }
          if ( (eregi('create', $next)) || (eregi('insert', $next)) || (eregi('drop t', $next)) ) {
            $next = '';
            $sql_array[] = substr($restore_query, 0, $i);
            $restore_query = ltrim(substr($restore_query, $i+1));
            $sql_length = strlen($restore_query);
            $i = strpos($restore_query, ';')-1;
          }
        }
      }

      for ($i=0; $i<sizeof($sql_array); $i++) {
		apphp_db_query($sql_array[$i]);
      }
      return true;
    } else {
      return false;
    }
  }

  function apphp_db_select_db($database) {
    return mysql_select_db($database);
  }

  function apphp_db_query($query) {
    global $link;
    $res=mysql_query($query, $link);
    return $res;
  }

?>
