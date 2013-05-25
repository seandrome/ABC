<?php  
function amazonEncode($text)  { 
   $encodedText = "";
   $j = strlen($text);
   for($i=0;$i<$j;$i++)    {
		$c = substr($text,$i,1);      
		if (!preg_match("/[A-Za-z0-9\-_.~]/",$c)) {
        $encodedText .= sprintf("%%%02X",ord($c));      
		} else {
		$encodedText .= $c; 
		}   
	}    
	return $encodedText;  
}  
function amazonSign($url,$secretAccessKey)  {
	// 0. Append Timestamp parameter
	$url .= "&Timestamp=".gmdate("Y-m-d\TH:i:s\Z");    
	// 1a. Sort the UTF-8 query string components by parameter name    
	$urlParts = parse_url($url);    
	parse_str($urlParts["query"],$queryVars);    
	ksort($queryVars);    
	// 1b. URL encode the parameter name and values    
	$encodedVars = array();    
	
	foreach($queryVars as $key => $value)    {
			$encodedVars[amazonEncode($key)] = amazonEncode($value);    
		}    // 1c. 1d. Reconstruct encoded query    

		$encodedQueryVars = array();    
		foreach($encodedVars as $key => $value)    {
			$encodedQueryVars[] = $key."=".$value;    
		}    
		$encodedQuery = implode("&",$encodedQueryVars);    
		// 2. Create the string to sign    
		$stringToSign  = "GET";    
		$stringToSign .= "\n".strtolower($urlParts["host"]);    
		$stringToSign .= "\n".$urlParts["path"];    
		$stringToSign .= "\n".$encodedQuery;    
		// 3. Calculate an RFC 2104-compliant HMAC with the string you just created,    
		//    your Secret Access Key as the key, and SHA256 as the hash algorithm.    
		if (function_exists("hash_hmac"))    {
			$hmac = hash_hmac("sha256",$stringToSign,$secretAccessKey,TRUE);    
		} elseif(function_exists("mhash"))    {      
			$hmac = mhash(MHASH_SHA256,$stringToSign,$secretAccessKey);    
		}    else    {      
		die("No hash function available!");    
		}    
		// 4. Convert the resulting value to base64    
		$hmacBase64 = base64_encode($hmac);    
		// 5. Use the resulting value as the value of the Signature request parameter    
		// (URL encoded as per step 1b)    
		$url .= "&Signature=".amazonEncode($hmacBase64);    
		return $url;  
}
?>
<?  
if ($opr=='Lookup')  {
    $url  = "http://webservices.amazon.com/onca/xml?Service=AWSECommerceService";    
	$url .= "&Version=2011-08-01";    
	$url .= "&Operation=ItemLookup";    
	$url .= "&AWSAccessKeyId=".$amazonAWSAccessKeyId;    
	$url .= "&AssociateTag=".$amazonAssociateTag;    
	$url .= "&ResponseGroup=Large";    
	$url .= "&IncludeReviewsSummary=True";    
	$url .= "&ItemId=".$asin;    
	$url = amazonSign($url,$amazonSecretAccessKey);    
	$xml = simplexml_load_file($url);    
	$item = $xml->Items->Item;  
}?>

<?  
$item_asin 	= $item[0]->ASIN;  
$item_url	= $item[0]->DetailPageURL;  
$item_image1  = $item[0]->MediumImage->URL;  
$item_image2	= $item[0]->LargeImage->URL;  
$item_title	= $item[0]->ItemAttributes->Title;  
$item_price	= $item[0]->OfferSummary->LowestNewPrice->FormattedPrice;  
if ($item_price=='') { 
	$item_price = $item[0]->ItemAttributes->ListPrice->FormattedPrice; 
}  $item_list	= $item[0]->ItemAttributes->ListPrice->FormattedPrice;  
if ($item_list=='') {
	$item_list = 'only '; 
} else {
	$item_list = '<font color="red"><del>'.$item_list.'</del></font>';
}    
$item_save	= $item[0]->Offers->Offer->OfferListing->AmountSaved->FormattedPrice;  
$item_model	= $item[0]->ItemAttributes->Model;  
$item_brand	= $item[0]->ItemAttributes->Brand;  
$item_binding	= $item[0]->ItemAttributes->Binding;  
$item_color	= $item[0]->ItemAttributes->Color;  
$item_unit	= $item[0]->OfferSummary->TotalNew;  
$item_warranty= $item[0]->ItemAttributes->Warranty;  
$item_height	= $item[0]->ItemAttributes->ItemDimensions->Height;  
$item_length	= $item[0]->ItemAttributes->ItemDimensions->Length;  
$item_weight	= $item[0]->ItemAttributes->ItemDimensions->Weight;  
$item_width	= $item[0]->ItemAttributes->ItemDimensions->Width;  
$gambarprod = $item_asin."_300.jpg";
if ($item_height!=0) { 
	$item_height	= $item_height/100; 
	} else {
		$item_height = 0; 
	}  if ($item_length!=0) {
		$item_length	= $item_length/100; 
	} else {
		$item_length = 0; 
	}  if ($item_weight!=0) {
		$item_weight	= $item_weight/100; 
	} else { 
		$item_weight = 0; 
	}  
if ($item_width!=0) { 
	$item_width	= $item_width/100; 
	} else { 
		$item_width = 0; 
	}  
//featured  
//$item_feat	= $item[0]->ItemAttributes->Feature[0];  
//$item_feat1	= $item[0]->ItemAttributes->Feature[1];  
//$item_feat2	= $item[0]->ItemAttributes->Feature[2];  
//$item_feat3	= $item[0]->ItemAttributes->Feature[3];  
//$item_feat4	= $item[0]->ItemAttributes->Feature[4];  
$item_avail	= $item[0]->Offers->Offer->OfferListing->Availability;  
$item_cust	= $item[0]->CustomerReviews->IFrameURL;  
$item_edit	= $item[0]->EditorialReviews->EditorialReview->Content; 
$customersreviewsurl = '';
if (isset($item[0]->ItemLinks->ItemLink[5]->URL))
    $customersreviewsurl = $item[0]->ItemLinks->ItemLink[5]->URL;
	if ($customersreviewsurl != ''){
    require_once("include/functions.php");
    $links = $customersreviewsurl;
    $target = trim($links);
    $raws   = crawl($target);	
	$html   = str_get_html($raws);
	foreach($html->find('div[style=margin-left:0.5em;]') as $dad){
		$rest = $dad->innertext;
		preg_match('/b>\s\s\s\s\s\s\s<\\/div>(.*?)style="padding-top/', $rest, $match);
		$re[] = $match[1];
    }
	$jmlstr = 0;
	shuffle ($re);
	foreach( $re as $rd){
		$jmlstr = $jmlstr + strlen($rd);
		$rec[] = $rd;
	}
	$ket = '<b><u>Product Review</u></b><br/>'.$rec[0];
	if ($jmlstr>1000){
	for($i=1;strlen($ket)<1000;$i++){
		$ket = $ket .'<br/><br/>'.$rec[$i];
	}
   
	$ket = str_replace('<div ','',$ket);
	$ket = preg_replace('/<a(.*?)>/','',$ket);
	$ket = preg_replace('/<\\/a>/','',$ket);
	$ket = substr($ket,0,1000);	
	}else{
	$ket = str_replace('<div ','',$ket);
	$ket = preg_replace('/<a(.*?)>/','',$ket);
	$ket = preg_replace('/<\\/a>/','',$ket);
	$ket = substr($ket,0,$jmlstr);		
	}
	}
	

//$item_edit1	= $item[0]->EditorialReviews->EditorialReview[1]->Content;  
$jumasin = 0;
foreach( $item[0]->SimilarProducts as $simasin ){
	$asm[] = $simasin->SimilarProduct->ASIN;
	$jumasin = $jumasin + 1;
}
Shuffle($asm);
foreach( $asm as $asimm ){
   $asim[] = $asimm;
}
/*
$item_sim	= $item[0]->SimilarProducts->SimilarProduct[0]->ASIN;  
$item_sim1	= $item[0]->SimilarProducts->SimilarProduct[1]->ASIN;
*/
?>

<?  $judul 	= $item_title;  
	if ($item_color='') { 
		$konten = 'This is the best product of <strong>'.$item_brand.'</strong> with low price. You can view product images, features, details and review here.<br/>Buy now, you will get a special price for <strong>'.$judul.'</strong> here'; 
		} else { 
		$konten = 'This is the best product of <strong>'.$item_brand.'</strong> with low price. You can view product images, features, details and review here.<br/>Buy now, you will get a special price for <strong>'.$judul.'</strong> here'; 
	}  
	if ($item_unit>1) {
		$konten1 = 'Estimated Price :'.$item_list.' <strong>'.$item_price.'</strong>'; } else { $konten1 = 'Estimated Price :'.$item_list.' <strong>'.$item_price.'</strong>'; 
	}  
		$gambar 	= $item_image2;  
if ($item_warranty!='') {
		$garansi = $item_brand.' give '.strtolower($item_warranty); 
		} else { 
		$garansi = 'Currently no specific warranty for this products'; 
		}  
/*		foreach($item[0]->ItemAttributes->Feature as $f){
        $feat = $item_feat.'<br>'.$item_feat1.'<br>'.$item_feat2.'<br>'.$item_feat3.'<br>'.$item_feat4;    
        } */
if ($customersreviewsurl != ''){
	$keterangan = str_replace('Amazon.com','Amazon Dot Com',$ket);
}else{	
if ($item_edit!='') {
		$keterangan = '<b><u>Product Discription</u></b><br/>'.str_replace('Amazon.com','Amazon Dot Com',substr(strip_tags($item_edit), 0, 1000)); 
	} else { 
		$keterangan = '<b><u>Product Discription</u></b><br/>Currently no descriptions for this product and will be added soon.'; 
	} 
}
	$review 	= $item_cust;  
	$beli  	= $item_url;  
	preg_match('/com\\/(.*?)\\/dp\\//', $beli, $match);
	$nmbrg = $match[1];
	
	$harga 	= $item_price.' Buy NOW!'; 
?>

<? /*  similiar products   
if ($item_sim)  {    
$url  = "http://webservices.amazon.com/onca/xml?Service=AWSECommerceService";    
$url .= "&Version=2011-08-01";    
$url .= "&Operation=ItemLookup";    
$url .= "&AWSAccessKeyId=".$amazonAWSAccessKeyId;    
$url .= "&AssociateTag=".$amazonAssociateTag;    
$url .= "&ResponseGroup=Medium";    
$url .= "&ItemId=".$item_sim.','.$item_sim1;    
$url = amazonSign($url,$amazonSecretAccessKey);    
$xml = simplexml_load_file($url);    
$item = $xml->Items->Item;  }?>  
<?    similiar detail #1  
	$sim_asin 	= $item[0]->ASIN;  
	$sim_gambar	= $item[0]->LargeImage->URL;  
	$sim_harga	= $item[0]->OfferSummary->LowestNewPrice->FormattedPrice;  
if ($sim_harga =='') {
	$sim_harga = $item[0]->ItemAttributes->ListPrice->FormattedPrice; 
 }  
	$sim_judul	= 'Only '.$sim_harga.' '.$item[0]->ItemAttributes->Title;  
	similiar detail #2  
	$sim_asin1 	= $item[1]->ASIN;  
	$sim_gambar1	= $item[1]->LargeImage->URL;  
	$sim_harga1	= $item[1]->OfferSummary->LowestNewPrice->FormattedPrice;  
 if ($sim_harga1 =='') { 
	$sim_harga1 = $item[1]->ItemAttributes->ListPrice->FormattedPrice; 
 }  
	$sim_judul1	= 'Only '.$sim_harga1.' '.$item[1]->ItemAttributes->Title; */
 ?>
 <?php
 function curPageURL() { 
 $pageURL = 'http'; 
 if ($_SERVER["HTTPS"] == "on") {
 $pageURL .= "s";
 } $pageURL .= "://"; 
 if ($_SERVER["SERVER_PORT"] != "80") {
 $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]; 
 } else { 
 $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]; 
 } return $pageURL;}
 ?>