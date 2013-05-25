<?php 
	include('inc/config.php');
	include('inc/functions.php');
	is_logged();
	include('inc_header.php');
	include('inc_top.php');
	include('inc_menu.php');
?>
<!-- Content wrapper -->
<div class="wrapper">
<?php include('inc_sidebar.php')?>	
	
    <!-- Content -->
    <div class="content">
    	<div class="title"><h5>Auto Blogspot Dashboard</h5></div>

        <!-- Headings -->        
        <div class="widget first">
            <div class="head"><h5 class="iCreate">Blog ID List</h5></div>
            <div class="body">
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
				
				$i = 0;
			  	foreach($feed->entries as $entry) {
				   	$blog = $entry->id->text;
					$blog = explode('-',$blog);
					echo end($blog) .br();
					$i++;
			  	}
			  	echo br(2) . '<h5>Total Blog ID : ' . $i . '</h5>';
			?>	
            </div>
        </div>	

        
    </div><!-- End Content -->
    <div class="fix"></div>
</div>
<?php include('inc_footer.php')?>