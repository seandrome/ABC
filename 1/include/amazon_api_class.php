<?php
require_once 'aws_signed_request.php';

class AmazonProductAPI {

    private $associate_tag;
    private $public_key;
    private $private_key;
    private $region;

    public function setAssociateTag($associate_tag) {
        $this->associate_tag = $associate_tag;
    }

    public function setPublicKey($public_key) {
        $this->public_key = $public_key;
    }

    public function setPrivateKey($private_key) {
        $this->private_key = $private_key;
    }
    
    public function setRegion($region) {
        $this->Region = $region;
    }

    private function verifyXmlResponse($response) {
        if ($response === False) {
            throw new Exception("Could not connect to Amazon");
        }
        else {
            if (isset($response->Items->Item->ItemAttributes->Title)) {
                return ($response);
            }
            else {
                throw new Exception("Invalid xml response.");
            }
        }
    }

    private function queryAmazon($parameters) {
        return aws_signed_request($this->Region, $parameters, $this->public_key, $this->private_key);
    }

    public function getItemByAsin($asin_code) {
        $parameters = array("Operation"     => "ItemLookup",
                "ItemId"        => $asin_code,
                "AssociateTag" => $this->associate_tag ,
                "ReviewSort"=>"-HelpfulVotes",
                "ResponseGroup"=>"Large,OfferFull,Offers,OfferSummary,Reviews,Similarities,Variations");

        $xml_response = $this->queryAmazon($parameters);

        return $this->verifyXmlResponse($xml_response);
    }

    public function getItemByKeyword($search , $pages = 1) {

        $parameters = array(
                "Operation"=>"ItemSearch",
                "AssociateTag"=>$this->associate_tag,
                "Keywords"=>$search,
                "SearchIndex"=>"All",
                "ItemPage"=>$pages,
                'sort' => '+pmrank',
                "ReviewSort"=>"-HelpfulVotes",
                "ResponseGroup"=>"Large,OfferFull,Offers,OfferSummary,Reviews,Similarities,Variations");

        $xml_response = $this->queryAmazon($parameters);

        return $this->verifyXmlResponse($xml_response);

    }

    public function getRandomByKeyword($search) {

        $file_name = 'count.txt';
        if(file_exists($file_name)) {
            $obj_fopen = fopen($file_name, 'r');
            if ($obj_fopen) {
                while (!feof($obj_fopen)) {
                    $file = fgets($obj_fopen, 4096);
                    //echo $file."<br>";
                    $key = explode('|', $file);
                    if($key[0] == $search) {
                        $is_key = $key[0];
                        $is_total = $key[1];
                        break;
                    }
                }
                fclose($obj_fopen);
            }
        }

        if(strlen($is_key)== 0) {
            $xml = AmazonProductAPI::getItemByKeyword($search);
            $total_pages = $xml->Items->TotalPages;

            if(file_exists($file_name)) {
                $obj_fopen = fopen($file_name, 'a');
                fwrite($obj_fopen, $search.'|'.$total_pages."\r\n");
                fclose($obj_fopen);
            }

        } else {
            $rand = rand(1, 5);
            $xml = AmazonProductAPI::getItemByKeyword($search ,$rand);

        }

        $rand_item = rand(0, 9);
        $item = $xml->Items->Item[$rand_item];
        $ASIN = $xml->Items->Item[$rand_item]->ASIN;

        return AmazonProductAPI::getItemByAsin($ASIN);
    }

    public function searchProducts($search, $category, $searchType = "UPC" , $pages = 1 ) {
        $allowedTypes = array("UPC", "TITLE", "ARTIST", "KEYWORD");
        $allowedCategories = array("Music", "DVD", "VideoGames");

        if($pages == 0)
            $pages = 1;

        switch($searchType) {
            case "UPC" :    $parameters = array("Operation"     => "ItemLookup",
                        "ItemId"        => $search,
                        "SearchIndex"   => $category,
                        "IdType"        => "UPC",
                        "ResponseGroup" => "Medium");
                break;

            case "TITLE" :  $parameters = array("Operation"     => "ItemSearch",
                        "Keywords"         => $search,
                        "SearchIndex"   => $category,
                        "AssociateTag" => $this->associate_tag ,
                        "ItemPage" => $pages ,
                        'sort' => '+pmrank',
                        "ResponseGroup" => "Medium");
                break;

        }

        $xml_response = $this->queryAmazon($parameters);

        return $this->verifyXmlResponse($xml_response);

    }

}

?>
