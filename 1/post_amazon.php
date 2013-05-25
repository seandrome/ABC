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
	
		
	$keyword = get_keyword_sql();
	$kw = str_replace(' ','%20',$keyword);
		
    
	$obj = new AmazonProductAPI();
    try
    {
        $result = $obj->getItemByKeyword($kw,"Electronics");
    }
    catch(Exception $e)
    {
        $error = $e->getMessage();
		if(!empty($error)){
			exit('Response 404 :  No Amazon Product Found');
		}
    }

	foreach($result->Items->Item as $r){
		$asin[] = $r->ASIN;
	}
	
	shuffle($asin);
	$asin = end($asin);

	//Posting
		try
	    {
	        $result_asin = $obj->getItemByAsin($asin);
	    }
	    catch(Exception $e)
	    {
	        echo $e->getMessage();
	    }	
			
			$product = $result_asin->Items->Item;
			foreach($product as $prod){
				$title = $prod->ItemAttributes->Title;
				$title = blog_title($title);

				$altimg = $title;
				$altimg = explode(' ',$altimg);
				$altimg[0] = ucwords($keyword); 
				shuffle($altimg);
				unset($altimg[0]);
				unset($altimg[0]);
				$altimg = implode(' ',$altimg);
				
				//$content = "<img src='" . $prod->LargeImage->URL . "' alt='" . $altimg . "' /><br />";
				$content ='';
				foreach($prod->ImageSets->ImageSet as $largeimage){
					$content .= "<center><img src='" . $largeimage->LargeImage->URL . "' alt='" . $altimg . "' /></center><br /><br />";
				}
				
				foreach($prod->EditorialReviews->EditorialReview->Content as $addcontent){
					$content .= '<p>' .$addcontent . '</p>';
				}
				
				$content .= '<ul>';	
				foreach($prod->ItemAttributes->Feature as $feature){
					$content .= '<li>' .$feature . '</li>';
				}			
				$content .= '</ul>';	
				
				$content .= br() . 'Price : ' . $prod->ItemAttributes->ListPrice->FormattedPrice .br(2);
				$content .= br(2) . "<a href='" . $prod->ItemLinks->ItemLink->URL . "' target='_blank' /><img src='http://g-ecx.images-amazon.com/images/G/01/marketing/prime/prime_add_to_cart_with_free_two_day._V192221409_.gif' alt='Buy Now' border='none' /></a>";
				
				
				//$label = $title;
				//$label = explode(' ',$label);
				//$label[0] = ucwords($keyword);
				//shuffle($label);
				//$label = implode(',',$label);
				$label = related_keyword($title);
				$label .= ',' .$keyword;
			}
	
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
		createPublishedPost($title, $content,$label);
		mysql_query("UPDATE blog_id SET count=count+1 where id=$id");
		echo "Response 200 : Success";
	}
	

