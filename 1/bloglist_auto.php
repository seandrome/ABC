<?php 
	include('inc/config.php');
	include('inc/functions.php');
	is_logged();
?>

			<?
				$user = get_options('blogspot_username');
				$pass = get_options('blogspot_password'); 	
				$service = 'blogger';
				
				require_once'Zend/Loader.php';
				Zend_Loader::loadClass('Zend_Gdata');
				Zend_Loader::loadClass('Zend_Gdata_Query');
				Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
			
			
				$client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service, null,
			            Zend_Gdata_ClientLogin::DEFAULT_SOURCE, null, null, 
			            Zend_Gdata_ClientLogin::CLIENTLOGIN_URI, 'GOOGLE');
			
				$gdClient = new Zend_Gdata($client); 
			  	$query = new Zend_Gdata_Query('http://www.blogger.com/feeds/default/blogs?max-results=100');
				$feed = $gdClient->getFeed($query);
				//$idText = split('-', $feed->entries[$index]->id->text); 
				//$blogID = $idText[2];
				
				
				mysql_query("DELETE FROM blog_id");
			  	foreach($feed->entries as $entry) {
				   	$blog = $entry->id->text;
					$blog = explode('-',$blog);
					$blog = end($blog);
					//echo end($blog) .br();
					mysql_query("INSERT INTO blog_id (id,blog_id,count) VALUES ('','$blog','0')");
			  	}
				redirect('blog.php');
			?>	
        