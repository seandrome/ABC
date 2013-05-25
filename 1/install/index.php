<?php
    require_once("settings.inc");    
    if (file_exists($config_file_path)) {        
		header("location: ".$application_start_file);
        exit;
	}
        
    ob_start();
    phpinfo(-1);
    $phpinfo = array('phpinfo' => array());
    if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
    foreach($matches as $match){
        if(strlen($match[1]))
            $phpinfo[$match[1]] = array();
        elseif(isset($match[3]))
            $phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
        else
            $phpinfo[end(array_keys($phpinfo))][] = $match[2];
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
			<div class="subtitle">Getting System & Server Information</div>
			PHP version: <?=$phpinfo['phpinfo']['PHP Version'];?><br />
            Server API: <?=$phpinfo['phpinfo']['Server API'];?><br />
            Safe Mode: <?=$phpinfo['PHP Core']['safe_mode'][0];?><br />
			
			<center>
			<input type="button" class="button" value="Start" name="submit" title="Click to start installation" onclick="document.location.href='install.php'">
			</center>
			</div>
		
	</div>
</body>
</html>

