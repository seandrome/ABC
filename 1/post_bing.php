<?php
include("inc/functions.php");

include("inc/config.php");
include("amazon_api_class.php");

	$query = mysql_query('select * from blog_id  LIMIT 1');
	if(mysql_num_rows($query) == 0){
		echo 'No Blog to post';
		return;
	}
      

	$get_blog = mysql_query('select * from blog_id order by count ASC LIMIT 1');
	$get_blog = mysql_fetch_assoc($get_blog);
	$blog_id = $get_blog['blog_id'];
	
	//update count blogid
	$id = $get_blog['id'];
	mysql_query("UPDATE blog_id SET count=count+1 where id=$id");

	$user = get_options('blogspot_username');
	$pass = get_options('blogspot_password'); 	
	$blogID = $blog_id;
	$service = 'blogger';

        $asin=get_keyword_sql();
     
 	////////////////////Dynazone///////////////////////
        $opr = 'Lookup';
        include('inc/info_id.php');
        include("inc/functiondyna.php");
	
	
	$title=$judul;
	$content  ='';
	//$content .='<br /><center>'.$konten.'</center><br/>';
	//////////////////  Encode Hotlink Image ////////////////////////
	/*
	$estrimg = "<img src='".$gambar."' title='".$altimg."'</a>";
	$enc1=base64_encode($estrimg);
	$eimglev1="<div id='imglev1'>".$enc1."</div>";
	$eimglev2=base64_encode($eimglev1);
	*/
	///////////////////////////////////////////////////
	$altitle = str_replace('"',' ',$title);
	$content .='<center><dips id="img/'.$gambarprod.'" alt="'.$altitle.'" ></dips></center><br/>';
	$content .='<center>'.$konten1.'</center><br/>';
	$content .="<br/><b><u>Product Feature</u></b><br/><ul>";
	foreach($item[0]->ItemAttributes->Feature as $feature){
				$content .= '<li>' .$feature . '</li>';
			}
	$content .="</ul><b><u>Dimension</u></b><br/>";
	$content .="Height = ".$item_height." inch , Length = ".$item_length." inch ,  Width = ".$item_width." inch , Weight = ".$item_weight." pounds. <br/><br/>";
			////////////////////////////////////////////////////
			/*
	$estrlink="<a href='".$beli."' target='_blank'><font color='black'><img src='http://3.bp.blogspot.com/-CtKAZ88TiP4/URo1ktanH3I/AAAAAAAAAII/Medg8tTvMLQ/s1600/buynow-big.gif' border='0'></a>";
	$enc1ink=base64_encode($estrlink);
	$elinklev1="<div id='linklev1'>".$enc1ink."</div>";
	$elinklev2=base64_encode($elinklev1); */
	$content .='<center><blink><font color="blue"><b>Please check the actual price here, it could change.</b></font></blink></center><br/><center><dips id="asoc" ghre="'.$nmbrg.' cahelek '.$item_asin.'asoy"><dips id="img/tumbas.gif" alt="View" border="0"></dips></dips></center>';
	/////////////////////////////////////////////////////
	$keterangan = str_replace(array('Amazon.com:','http://','www.','.com','http','.org','.info','.net'),array('','','','','','','',''),$keterangan);
	$content .="<br/>".$keterangan."......<br/><br/>";
	
	//////////Snipet/////////
	$titleraws = preg_replace('/[^(\x20-\x7F)]*/', '', $judul);
	$agc_snipet = array();
    $xmlsnipet = fca_agc_snipet($titleraws);
    $itemsnipet = $xmlsnipet->channel->item;
    for ($x = 0; $x < (count($itemsnipet)); $x++)
    { 
	if ( $x <= 5 ){
        $agc_snipet[] = '<strong>' . $itemsnipet[$x]->title . '</strong> ' . $itemsnipet[$x]->description;
    }
	}
    shuffle($agc_snipet);
    $agctext = implode(' ', $agc_snipet);
    $agctext = trim(cleanchars($agctext));
	$agctext = str_replace(array('Amazon.com:','http://','www.','.com','http','.org','.info','.net'),array('','','','','','','',''),$agctext);
	
	/////////Snipet////////
	
	$content .="<blockquote>".$agctext."</blockquote>";

	
	//$lab = $item_brand;
	
	$label= $item_brand;
	
	
	
	require_once'Zend/Loader.php';
	Zend_Loader::loadClass('Zend_Gdata');
	Zend_Loader::loadClass('Zend_Gdata_Query');
	Zend_Loader::loadClass('Zend_Gdata_ClientLogin');

	//Checks if getHttpClient throws any exceptions
	try {
	   $client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, 'blogger', null,
	    Zend_Gdata_ClientLogin::DEFAULT_SOURCE, null, null,
	    Zend_Gdata_ClientLogin::CLIENTLOGIN_URI, 'GOOGLE');
	} catch (Zend_Gdata_App_AuthException $ae) {
	   echo 'Response 404 => Problem authenticating google account' . $ae->exception() . "\n";
	   exit;
	}
	$gdClient = new Zend_Gdata($client); 
	
	
	if(!empty($title)){
		createPublishedPost2($title, $content,$label);
		echo "<br />Response 200 : Success";
		
		
		$duplicate = 0;
	if ( $jumasin != 0 ) {
		for ($ii=0;$duplicate < 1; $ii++){
		if ( $ii < $jumasin ) {
			$duplicate = mysql_query("SELECT * FROM keyword where keyword='$asim[$ii]'");
			$duplicate = mysql_num_rows($duplicate);
			if($duplicate < 1){
				mysql_query("INSERT INTO keyword (id,keyword,count) VALUES ('','$asim[$ii]','0')");
				mysql_query("DELETE FROM keyword WHERE keyword='$item_asin'");
				$duplicate = 1 ;
				echo "<br />ASIN Berhasil Di Input : ".$asim[$ii] ;
			}
		} else {
		$duplicate = 1;
		echo "<br />Berhasil Posting Brow.... :D";
		}
		}
		
	}	
	}