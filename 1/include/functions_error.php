<?php

function auto_link_text( $text )
{
    $pattern = "@http://(?:www\\.)?(\\S+/)\\S*(?:\\s|\$)@i";
    $callback = create_function( "\$matches", "\r\n       \$url       = array_shift(\$matches);\r\n       \$url_parts = parse_url(\$url);\r\n\r\n       \$text = parse_url(\$url, PHP_URL_HOST) . parse_url(\$url, PHP_URL_PATH);\r\n       \$text = preg_replace(\"/^www./\", \"\", \$text);\r\n\r\n       \$last = -(strlen(strrchr(\$text, \"/\"))) + 1;\r\n       if (\$last < 0) {\r\n           \$text = substr(\$text, 0, \$last) . \"&hellip;\";\r\n       }\r\n\r\n       return sprintf('<a rel=\"nofollow\" href=\"%s\">%s</a>', \$url, \$text);\r\n   " );
    preg_match_all( "#<(a|img|iframe)(.*?)http:\\/\\/(.*?)>#i", $text, $out );
    $new = array( );
    $old = array( );
    $i = 0;
    while ( $i < count( $out[0] ) )
    {
        $newout = str_replace( "http://", "hxxp://", $out[0][$i] );
        array_push( $new, $newout );
        array_push( $old, $out[0][$i] );
        ++$i;
    }
    $string = str_replace( $old, $new, $text );
    return str_replace( "hxxp://", "http://", preg_replace_callback( $pattern, $callback, $string ) );
}

function ratna_strip_selected_tags( $text, $tags = array( ) )
{
    $args = func_get_args( );
    $text = array_shift( $args );
    $tags = 2 < func_num_args( ) ? array_diff( $args, array(
        $text
    ) ) : ( array )$tags;
    foreach ( $tags as $tag )
    {
        while ( preg_match( "/<".$tag."(|\\W[^>]*)>(.*)<\\/".$tag.">/iusU", $text, $found ) )
        {
            $text = str_replace( $found[0], $found[2], $text );
        }
    }
    return preg_replace( "/(<(".join( "|", $tags ).")(|\\W.*)\\/>)/iusU", "", $text );
}

function cleanchars( $rawhtml, $save = "" )
{
    $rawhtml = mb_convert_encoding( $rawhtml, "UTF-8", "HTML-ENTITIES" );
    $rawhtml = preg_replace( "/[^a-zA-Z0-9\\s%\"'-_\\+=\\.,><\\?\\\$@!\\(\\)\\*&\\^:;\\/\\/]/", "", $rawhtml );
    if ( $save != "script" )
    {
        $rawhtml = preg_replace( "/<script\\b[^>]*>(.*?)<\\/script>/is", "", $rawhtml );
    }
    $final = preg_replace( "/[^(\\x20-\\x7F)]*/", "", $rawhtml );
    return $final;
}

function crawl( $url, $ref = "", $data = "" )
{
    $ua = getRandom_ua( );
    $path = $_SERVER['DOCUMENT_ROOT'].str_replace( basename( $_SERVER['PHP_SELF'] ), "", $_SERVER['PHP_SELF'] );
    if ( $ref == "" )
    {
        $ref = $url;
    }
    $ch = curl_init( );
    $https = strpos( $url, "https://" );
    if ( $https !== false )
    {
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
    }
    if ( $data != "" )
    {
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
    }
    if ( $ref != "NO" )
    {
        curl_setopt( $ch, CURLOPT_REFERER, $ref );
    }
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_USERAGENT, $ua );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_COOKIEJAR, $path."/curlcookie.txt" );
    curl_setopt( $ch, CURLOPT_COOKIEFILE, $path."/curlcookie.txt" );
    if ( ini_get( "open_basedir" ) != "" || ini_get( "safe_mode" ) != 0 )
    {
        $result = curl_redir_exec( $ch );
    }
    else
    {
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        $result = curl_exec( $ch );
    }
    curl_close( $ch );
    return $result;
}

function curl_redir_exec( $ch )
{
    static $curl_loops = 0;
    static $curl_max_loops = 20;
    if ( $curl_max_loops <= $curl_loops++ )
    {
        $curl_loops = 0;
        return false;
    }
    curl_setopt( $ch, CURLOPT_HEADER, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $data = curl_exec( $ch );
    list( $header, $data ) = header    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    if ( $http_code == 301 || $http_code == 302 )
    {
        $matches = array( );
        preg_match( "/Location:(.*?)\\n/", $header, $matches );
        $url = @parse_url( @trim( @array_pop( $matches ) ) );
        if ( !$url )
        {
            $curl_loops = 0;
            return $data;
        }
        $last_url = parse_url( curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL ) );
        if ( !$url['scheme'] )
        {
            $url['scheme'] = $last_url['scheme'];
        }
        if ( !$url['host'] )
        {
            $url['host'] = $last_url['host'];
        }
        if ( !$url['path'] )
        {
            $url['path'] = $last_url['path'];
        }
        $new_url = $url['scheme']."://".$url['host'].$url['path'].( $url['query'] ? "?".$url['query'] : "" );
        curl_setopt( $ch, CURLOPT_URL, $new_url );
        return curl_redir_exec( $ch );
    }
    $curl_loops = 0;
    return $data;
}

function logWriter( $title )
{
    $fp = fopen( "title_posted.log", "a" );
    fputs( $fp, $title."\n" );
    fclose( $fp );
}

function contentpost( $title, $body, $urlsrc = "" )
{
    $orititle = $title;
    $spunbody = "";
    $spunttitle = "";
    include( "settings.php" );
    $username = trim( $username );
    if ( $striplink == 1 )
    {
        $body = ratna_strip_selected_tags( $body, array( "a", "url" ) );
    }
    if ( $contentspin == 1 && $tbs_username != "" && $tbs_password != "" )
    {
        include( "tbsspin.php" );
        $args = array(
            "tbs_login" => $tbs_username,
            "tbs_password" => $tbs_password,
            "tbs_quality" => $quality,
            "protected" => $protect,
            "title" => $title,
            "content" => $body
        );
        $result = tbs_action( $args );
        $spunbody = $result['content'];
        $spunttitle = $result['title'];
    }
    if ( $srclink == 1 )
    {
        $src = "Via: <a href=\"".$urlsrc."\" target=\"_blank\" rel=\"nofollow\">".$title."</a>";
        if ( $spunttitle != "" )
        {
            $title = $spunttitle;
        }
        if ( $spunbody != "" )
        {
            $body = $spunbody."<br />\n<small>".$src."</small>\n";
        }
        else
        {
            $body .= "<br />\n<small>".$src."</small>\n";
        }
    }
    else
    {
        if ( $spunttitle != "" )
        {
            $title = $spunttitle;
        }
        if ( $spunbody != "" )
        {
            $body = $spunbody;
        }
    }
    if ( $keywords != "" )
    {
        $linkold = array( );
        $linknew = array( );
        $imold = array( );
        $imnew = array( );
        preg_match_all( "/<img(.*)?>/", $body, $image );
        $y = 0;
        while ( $y < count( $image[0] ) )
        {
            array_push( $imold, $image[0][$y] );
            array_push( $imnew, base64_encode( $image[0][$y] ) );
            ++$y;
        }
        $body = str_replace( $imold, $imnew, $body );
        $kw = explode( "\n", $keywords );
        $i = 0;
        while ( $i < count( $kw ) )
        {
            preg_match_all( "/<a(.*)?<\\/a>/", $body, $link );
            $n = 0;
            while ( $n < count( $link[0] ) )
            {
                array_push( $linkold, $link[0][$n] );
                array_push( $linknew, base64_encode( $link[0][$n] ) );
                ++$n;
            }
            $body = str_replace( $linkold, $linknew, $body );
            $el = explode( "|", $kw[$i] );
            $body = preg_replace( "/".$el[0]."/i", "<a href=\"".$el[1]."\" target=\"_blank\" rel=\"nofollow\">\$0</a>", $body );
            ++$i;
        }
        $body = str_replace( $linknew, $linkold, $body );
        $body = str_replace( $imnew, $imold, $body );
    }
    if ( !empty( $username ) && !empty( $password ) && !empty( $blogid ) )
    {
        $service = "blogger";
        set_include_path( get_include_path( ).PATH_SEPARATOR."../" );
        require_once( "../Zend/Loader.php" );
        Zend_Loader::loadclass( "Zend_Gdata" );
        Zend_Loader::loadclass( "Zend_Gdata_Query" );
        Zend_Loader::loadclass( "Zend_Gdata_ClientLogin" );
        $client = Zend_Gdata_ClientLogin::gethttpclient( $username, $password, $service, null, Zend_Gdata_ClientLogin::DEFAULT_SOURCE, null, null, Zend_Gdata_ClientLogin::CLIENTLOGIN_URI, "GOOGLE" );
        ( $client );
        $gdClient = new Zend_Gdata( );
        $labels = fca_tag_yahoo( $title." ".$body, 6 );
        $id = explode( "[#]", $blogid );
        if ( createPublishedPost( $title, $body, $labels, trim( $id[0] ), $gdClient ) )
        {
            logwriter( $orititle );
            echo "Posted: ".$title."<br />".$body;
            $asinlist = file_get_contents( "asinlist.txt" );
            $asin = explode( "\n", $asinlist );
            $asinlist = trim( str_replace( $asin[0], "", $asinlist ) );
            $fp = fopen( "asinlist.txt", "w" );
            fputs( $fp, $asinlist );
            fclose( $fp );
        }
        else
        {
            echo "Failed, attempting to post ".$title."<br />".$body;
        }
    }
    else
    {
        $my_post = array(
            "post_title" => $title,
            "post_content" => $body,
            "post_status" => $post_status,
            "post_author" => 1,
            "post_category" => $wpcategory
        );
        if ( wp_insert_post( $my_post ) )
        {
            logwriter( $orititle );
            echo "Posted: ".$title."<br />".$body;
        }
        else
        {
            echo "Failed, attempting to post ".$title."<br />".$body;
        }
    }
}

function createPublishedPost( $title, $content, $labelnya = "", $blogID, $gdClient )
{
    $labelnya = trim( $labelnya );
    $uri = "http://www.blogger.com/feeds/".$blogID."/posts/default";
    $entry = $gdClient->newEntry( );
    $entry->title = $gdClient->newTitle( $title );
    $entry->content = $gdClient->newContent( $content );
    if ( 10 <= strlen( $labelnya ) )
    {
        $labels = $entry->getCategory( );
        $newLabel = $gdClient->newCategory( $labelnya, "http://www.blogger.com/atom/ns#" );
        $labels[] = $newLabel;
        $entry->setCategory( $labels );
    }
    $entry->content->setType( "text" );
    $createdPost = $gdClient->insertEntry( $entry, $uri );
    $idText = explode( "-", $createdPost->id->text );
    $newPostID = $idText[2];
    return $newPostID;
}

function getRandom_ua( )
{
    $ua = array( "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1b3) Gecko/20090305 Firefox/3.1b3 GTB5", "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; ko; rv:1.9.1b2) Gecko/20081201 Firefox/3.1b2", "Mozilla/5.0 (X11; U; SunOS sun4u; en-US; rv:1.9b5) Gecko/2008032620 Firefox/3.0b5", "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.8.1.12) Gecko/20080214 Firefox/2.0.0.12", "Mozilla/5.0 (Windows; U; Windows NT 5.1; cs; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8", "Mozilla/5.0 (X11; U; OpenBSD i386; en-US; rv:1.8.0.5) Gecko/20060819 Firefox/1.5.0.5", "Mozilla/5.0 (Windows; U; Windows NT 5.0; es-ES; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3", "Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.7.9) Gecko/20050711 Firefox/1.0.5", "Mozilla/4.0 (Windows; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)", "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; GTB5; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506; InfoPath.2; OfficeLiveConnector.1.3; OfficeLivePatch.0.0)", "Mozilla/4.0 (Mozilla/4.0; MSIE 7.0; Windows NT 5.1; FDM; SV1; .NET CLR 3.0.04506.30)", "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0; .NET CLR 2.0.50727)", "Mozilla/4.0 (compatible; MSIE 5.0b1; Mac_PowerPC)", "Mozilla/4.0 (compatible; MSIE 5.23; Mac_PowerPC)", "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; GTB6; Ant.com Toolbar 1.6; MSIECrawler)", "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 1.1.4322; InfoPath.2; .NET CLR 3.5.21022; .NET CLR 3.5.30729; MS-RTC LM 8; OfficeLiveConnector.1.4; OfficeLivePatch.1.3; .NET CLR 3.0.30729)", "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/4.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 1.1.4322; InfoPath.2; .NET CLR 3.5.21022; .NET CLR 3.5.30729; MS-RTC LM 8; OfficeLiveConnector.1.4; OfficeLivePatch.1.3; .NET CLR 3.0.30729)", "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; InfoPath.2)", "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; fi-fi) AppleWebKit/420+ (KHTML, like Gecko) Safari/419.3", "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; de-de) AppleWebKit/125.2 (KHTML, like Gecko) Safari/125.7", "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-us) AppleWebKit/312.8 (KHTML, like Gecko) Safari/312.6", "Mozilla/5.0 (Windows; U; Windows NT 5.1; cs-CZ) AppleWebKit/523.15 (KHTML, like Gecko) Version/3.0 Safari/523.15", "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16", "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_6; it-it) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16", "Mozilla/5.0 (Windows; U; Windows NT 5.0; it-IT; rv:1.7.12) Gecko/20050915", "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.0.1) Gecko/20020919", "Mozilla/4.0 (compatible; MSIE 5.0; Windows 98) Opera 5.12 [en]", "Opera/6.0 (Windows 2000; U) [fr]", "Mozilla/4.0 (compatible; MSIE 5.0; Windows NT 4.0) Opera 6.01 [en]", "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1) Opera 7.10 [en]", "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 9.24", "Opera/9.51 (Macintosh; Intel Mac OS X; U; en)", "Opera/9.70 (Linux i686 ; U; en) Presto/2.2.1", "Opera/9.80 (Windows NT 5.1; U; cs) Presto/2.2.15 Version/10.00" );
    $x = array_rand( $ua, 1 );
    $agent = $ua[$x];
    return $agent;
}

function fca_tag_yahoo( $content, $num, $remove_tags = "" )
{
    $senddata = array(
        "q" => "select%20*%20from%20search.termextract%20where%20context%3D%22".urlencode( utf8_decode( addslashes( strip_tags( $content ) ) ) )."%22",
        "format" => "json",
        "diagnostics" => "false"
    );
    $data = fca_post_request( "http://query.yahooapis.com/v1/public/yql", "http://".$_SERVER['HTTP_HOST'], $senddata );
    $ret = "";
    if ( $json = fca_yql_ttn_json_decode( $data ) )
    {
        $i = 0;
        if ( is_array( $json['query']['results']['Result'] ) && !empty( $json['query']['results']['Result'] ) )
        {
            foreach ( $json['query']['results']['Result'] as $kw )
            {
                if ( $num <= $i )
                {
                    break;
                }
                $ret .= $kw.", ";
                ++$i;
            }
        }
    }
    return substr( $ret, 0, 0 - 2 );
}

function fca_post_request( $url, $referer, $_data )
{
    $data = array( );
    while ( list( $n, $v ) = n )
    {
        $data[] = "{$n}={$v}";
    }
    $data = implode( "&", $data );
    $url = parse_url( $url );
    if ( $url['scheme'] != "http" )
    {
        exit( "Only HTTP request are supported !" );
    }
    $host = $url['host'];
    $path = $url['path'];
    if ( $fp = @fsockopen( $host, 80, $errno, $errstr, 10 ) )
    {
        fputs( $fp, "POST {$path} HTTP/1.1\r\n" );
        fputs( $fp, "Host: {$host}\r\n" );
        fputs( $fp, "Referer: {$referer}\r\n" );
        fputs( $fp, "Content-type: application/x-www-form-urlencoded\r\n" );
        fputs( $fp, "Content-length: ".strlen( $data )."\r\n" );
        fputs( $fp, "Connection: close\r\n\r\n" );
        fputs( $fp, $data );
        $result = "";
        while ( !feof( $fp ) )
        {
            $result .= fgets( $fp, 128 );
        }
        fclose( $fp );
    }
    $result = explode( "\r\n\r\n", $result, 2 );
    $content = isset( $result[1] ) ? $result[1] : "";
    return $content;
}

function fca_yql_ttn_json_decode( $json )
{
    $comment = false;
    $out = "\$x=";
    $i = 0;
    while ( $i < strlen( $json ) )
    {
        if ( !$comment )
        {
            if ( $json[$i] == "{" || $json[$i] == "[" )
            {
                $out .= " array(";
            }
            else if ( $json[$i] == "}" || $json[$i] == "]" )
            {
                $out .= ")";
            }
            else if ( $json[$i] == ":" )
            {
                $out .= "=>";
            }
            else
            {
                $out .= $json[$i];
            }
        }
        else
        {
            $out .= $json[$i];
        }
        if ( $json[$i] == "\"" )
        {
            $comment = !$comment;
        }
        ++$i;
    }
    if ( 1 < strlen( $json ) )
    {
        eval( $out.";" );
        return $x;
    }
    return "";
}

function fca_forbidden_tag( $forbidden, $tag )
{
    if ( is_array( $forbidden ) && !empty( $forbidden ) )
    {
        foreach ( $forbidden as $forbid )
        {
            if ( !( $forbid != "" ) && !( strpos( strtolower( $tag ), strtolower( $forbid ) ) !== false ) )
            {
                continue;
            }
            return true;
        }
    }
    return false;
}

function string_limit_words( $string, $word_limit )
{
    $words = explode( " ", $string, $word_limit + 1 );
    if ( $word_limit < count( $words ) )
    {
        array_pop( $words );
    }
    return implode( " ", $words );
}

function ParseSpinText( $s )
{
    preg_match( "#{(.+?)}#is", $s, $m );
    if ( empty( $m ) )
    {
        return $s;
    }
    $t = $m[1];
    if ( strpos( $t, "{" ) !== false )
    {
        $t = substr( $t, strrpos( $t, "{" ) + 1 );
    }
    $parts = explode( "|", $t );
    $s = preg_replace( "+{".preg_quote( $t )."}+is", $parts[array_rand( $parts )], $s, 1 );
    return ParseSpinText( $s );
}

function fca_agc_snipet( $title )
{
    $title = urlencode( $title );
    $xml = simplexml_load_file( "http://www.bing.com/search?q=".$title."&format=rss" );
    return $xml;
}

function closetags( $html )
{
    preg_match_all( "#<(?!meta|img|br|hr|input\\b)\\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU", $html, $result );
    $openedtags = $result[1];
    preg_match_all( "#</([a-z]+)>#iU", $html, $result );
    $closedtags = $result[1];
    $len_opened = count( $openedtags );
    if ( count( $closedtags ) == $len_opened )
    {
        return $html;
    }
    $openedtags = array_reverse( $openedtags );
    $i = 0;
    while ( $i < $len_opened )
    {
        if ( !in_array( $openedtags[$i], $closedtags ) )
        {
            $html .= "</".$openedtags[$i].">";
        }
        else
        {
            unset( $closedtags[array_search( $openedtags[$i], $closedtags )] );
        }
        ++$i;
    }
    return $html;
}

if ( file_exists( "wp-config.php" ) )
{
    require_once( "wp-config.php" );
}
else if ( file_exists( "../wp-config.php" ) )
{
    require_once( "../wp-config.php" );
}
else if ( file_exists( "../../wp-config.php" ) )
{
    require_once( "../../wp-config.php" );
}
else if ( file_exists( "../../../wp-config.php" ) )
{
    require_once( "../../../wp-config.php" );
}
if ( !class_exists( "simple_html_dom" ) )
{
    require_once( "include/htmldom.php" );
}
?>
