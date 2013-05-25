<?php
	// Login function
	function is_logged(){
		session_start();
		if(!isset($_SESSION["logged"])){
			redirect('login.php');
		}
	}
	
	// SQL function
	function get_options($option){
		$query = mysql_query("SELECT * FROM options where name='$option'");
		$query = mysql_fetch_object($query);
		return $query->value;
	}
	
	function sql_totalkeyword(){
		$query = mysql_query("SELECT * FROM keyword");
		$query = mysql_num_rows($query);
		return $query;
	}
	function sql_totalblogID(){
		$query = mysql_query("SELECT * FROM blog_id");
		$query = mysql_num_rows($query);
		return $query;
	}
	function sql_totapost(){
		$query = mysql_query("SELECT SUM(count) as total FROM blog_id");
		while($row = mysql_fetch_object($query)){
			$data = $row->total;
		}
		return $data;
	}
	function get_keyword_sql(){
		$query_keyword = mysql_query("SELECT * FROM keyword order by rand() LIMIT 1");
		if(mysql_num_rows($query_keyword) < 1){
			exit('Response 404 : No Keyword In Database');
		}
		while($row = mysql_fetch_object($query_keyword)){
				$get_keyword = $row->keyword;
				$id = $row->id;
		}
		mysql_query("UPDATE keyword SET count=count+1 where id=$id");
		return $get_keyword;
	}
	
	// zend publish function	
	function createPublishedPost2($title, $content,$labelnya){
	  	global $gdClient,$blogID;
	  	$uri = 'http://www.blogger.com/feeds/' . $blogID . '/posts/default';
	  	$entry = $gdClient->newEntry();
	  	$entry->title = $gdClient->newTitle($title);
	 	$entry->content = $gdClient->newContent($content);
	 	$labels = $entry->getCategory();
		$newLabel = $gdClient->newCategory($labelnya, 'http://www.blogger.com/atom/ns#'); 
		$labels[] = $newLabel; // Append the new label to the list of labels. 

		$entry->setCategory($labels); 
	  	$entry->content->setType('text');
		
		// add check if empty by momod
		$checking = $entry->content->setType('text');
		if(empty($checking)){
			return;
		}
	  	$createdPost = $gdClient->insertEntry($entry, $uri);
	}
	
	// Get Related search
	function related_keyword($keyword){
		$explode = explode(' ',$keyword);
		$total =  count($explode);	
		
		for ($i=1; $i<=$total; $i++){
			$data[] = implode(' ',$explode);
			array_pop($explode);
		}
	
		foreach($data as $r){
			$url = 'http://www.bing.com/images/search?q='.urlencode($r).'&qft=+filterui:imagesize-medium&count=10&first='.rand(0,30).'&format=xml';

	        simplexml_load_file($url);
	        $decode = simplexml_load_file($url)->xpath('/searchresult/section/documentset/document');
			
			if(!empty($decode)){
				$tag_array = $decode;
				break;
			}
		} 
		
		foreach($tag_array as $r){
			$tag[] = ',' . trim(preg_replace('/[^a-zA-Z0-9\s\s+]/', '', $r->title));
		}
		
		shuffle($tag);
		$tag = $tag[0].$tag[1].$tag[2];
		return 	$tag;
	}
	
	// Pull Keyword From Google trends into txt
	function google_trends(){
		$feed = simplexml_load_file('http://www.google.com/trends/hottrends/atom/hourly');
		$children =  $feed->children('http://www.w3.org/2005/Atom');
		$parts = $children->entry;
		foreach ($parts as $entry) {
	   		$details = $entry->children('http://www.w3.org/2005/Atom');
	   		$dom = new domDocument();
	   		@$dom->loadHTML($details->content);
	   		$anchors = $dom->getElementsByTagName('a');
	      	
			foreach ($anchors as $anchor) {
				$url = $anchor->getAttribute('href');
	        	$trend = $anchor->nodeValue;
				$kw = rtrim($trend);	
				$kw = mysql_escape_string($kw);
				
				$duplicate = mysql_query("SELECT * FROM keyword where keyword='$kw'");
				$duplicate = mysql_num_rows($duplicate);
				if($duplicate < 1){
					mysql_query("INSERT INTO keyword (id,keyword,count) VALUES ('','$kw','0')");
				}
			}

		}
	}
	
	// get twitter trends
	function twitter_trends(){
		$file = "http://api.twitter.com/1/trends/1.json";
		$file = file_get_contents($file);
		$twittertrends = json_decode($file,TRUE);
		$twittertrends = $twittertrends[0]['trends'];
		foreach($twittertrends as $r){
			$kw = clean_text($r['name']);
			$kw = strtolower($kw);
			$kw = rtrim($kw);
			$duplicate = mysql_query("SELECT * FROM keyword where keyword='$kw'");
			$duplicate = mysql_num_rows($duplicate);
			if($duplicate < 1){
					mysql_query("INSERT INTO keyword (id,keyword,count) VALUES ('','$kw','0')");
			}
		}
	}
	
	
	// HTML Function Start here
	function br($num=1){
		return str_repeat("<br />", $num);
	}
	
	function nbs($num = 1){
		return str_repeat("&nbsp;", $num);
	}
	
	function anchor($link,$text){
		return ("<a href='$link' >$text</a>");
	}
	
	function redirect($uri){
		header("Refresh:0;url=".$uri);
		exit;
	}
	
	function remove_common_words($input){
   		$commonWords = array('...','..','<','>','http','a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','c','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','\'ll','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','you\'ve','yours','yourself','yourselves','you\'ve','z','zero','ada','adalah','agak','agar','akan','aku','amat','anda','apa','apabila','atau','bahwa','bagai','baru','beberapa','begitu','begini','bila','belum','betapa','banyak','boleh','cara','cuma','dan','dalam','dari','dapat','demikian','dengan','di','dia','hanya','harus','ialah','ini','ingin','itu','hanya','jika','juga','hendak','kali','kalau','kami','kan','karena','ke','kelak','kemudian','kenapa','kepada','kini','ku','lah','lain-lain','lagi','lalu','lama','lantas','maka','mana','masa','masih','mau','me','mereka','merupakan','meng','mengapa','mesti','mu','namun','nan','nun','nya','orang','pada','paling','pasti','para','pen','pengen','pernah','saat','saja','sana','sang','sangat','saya','sebagainya','sedang','sehingga','selain','selalu','seluruh','sekali','sekarang','sementara','semua','senantiasa','seorang','seseorang','seperti','serba','sering','serta','sesuatu','si','sini','situ','suatu','sudah','supaya','tahun','tanpa','telah','terus','untuk','yakni','yaitu','yang');
	    return preg_replace('/\b('.implode('|',$commonWords).')\b/','',$input);
	}

	function check($data){
		echo '<pre>', print_r($data, true), '</pre>';
	}
	
	function blog_title($text){
		$text = trim(preg_replace('/[^a-z0-9]/i',' ', $text));
		$text = strtolower($text);
		$text = remove_common_words($text);
		$text = ucwords($text);
		return $text;
	}
	
	function clean_text($text){
		$text = trim(preg_replace('/[^a-z0-9]/i', ' ', $text));
		$text = strtolower($text);
		$text = ucwords($text);
		return $text;
	}
	function clean_keyword($text){
		$text = trim(preg_replace('/[^a-z0-9]/i', ' ', $text));
		$text = strtolower($text);
		return $text;
	}

?>