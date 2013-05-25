<?php
function tbs_action($args)
{
    $tbs_login = $args['tbs_login'];
    $tbs_password = $args['tbs_password'];
    $tbs_quality = $args['tbs_quality'];
    $tbs_maxsin = 4;
    $title = $args['title'];
    $content = trim($args['content']);
    $oimg = array();
    $pimg = array();
    preg_match_all('/<img (.*?)>/', $content, $matches);
    if (count($matches[0]) > 0) {

        for ($i = 0; $i < count($matches[0]); $i++) {
            array_push($oimg, $matches[0][$i]);
            array_push($pimg, base64_encode($matches[0][$i]));
        }
        $content = str_replace($oimg, $pimg, $content);

    }

    $content = $title . " [b^r^e^a^k^l^i^n^e] " . $content;
  
    $tbs_protected = $args['protected'];
    $result = array();
    if (!empty($tbs_login) && !empty($tbs_password)) {
        $url = 'http://thebestspinner.com/api.php';

        #$testmethod = 'identifySynonyms';
        $testmethod = 'replaceEveryonesFavorites';


        # Build the data array for authenticating.

        $data = array();
        $data['action'] = 'authenticate';
        $data['format'] = 'php'; # You can also specify 'xml' as the format.

        # The user credentials should change for each UAW user with a TBS account.

        $data['username'] = $tbs_login;
        $data['password'] = $tbs_password;
        $output = unserialize(tbspinner_curl_post($url, $data, $info));
        if ($output['success'] == 'true') {
            # Success.
            $session = $output['session'];

            # Build the data array for the example.
            $data = array();
            $data['session'] = $session;
            $data['format'] = 'php'; # You can also specify 'xml' as the format.
            $data['text'] = $content;
            $data['action'] = "replaceEveryonesFavorites";
            $data['maxsyns'] = $tbs_maxsin; # The number of synonyms per term.
            $data['quality'] = $tbs_quality;
            $data['protectedterms'] = $tbs_protected;

            # Post to API and get back results.
            $output = tbspinner_curl_post($url, $data, $info);
            $output = unserialize($output);
            if ($output['success'] == 'true') {
                //$options = (array )get_option('wp_tmoc_settings');
                //$tmoc_rewrite_title = $options['tmoc_rewrite_title'];
                $spintaxmentah = stripslashes(str_replace("\r", "<br>", $output['output']));
                $output = tbs_master_spinner($spintaxmentah);
                $newcontent = stripslashes(str_replace("\r", "<br>", $output));
                $multispace = array("     ", "    ", "   ", "  ");
                $newcontent = str_replace($multispace, " ", $newcontent);
                $newcontent = stripslashes($newcontent);

                $spuncontent = explode("[b^r^e^a^k^l^i^n^e]", $newcontent);
                $result['title'] = trim($spuncontent[0]);
                $spunpost = trim($spuncontent[1]);

                $spunpost = str_replace(array(" ,", " ."), array(",", "."), $spunpost);
                if (count($matches[0]) > 0) {

                    $result['content'] = str_replace($pimg, $oimg, $spunpost);

                } else {
                    $result['content'] = $spunpost;
                }
                
                /*
                $data = array();
                $data['session'] = $session;
                $data['format'] = 'php'; # You can also specify 'xml' as the format.
                $data['text'] = $spintaxmentah;
                $data['action'] = "randomSpin";
                $output = tbspinner_curl_post($url, $data, $info);
                $output = unserialize($output);
                if ($output['success'] == 'true') {

                $newcontent = stripslashes(str_replace("\r", "<br>", $output['output']));
                $spuncontent = explode("[b^r^e^a^k^l^i^n^e]", $newcontent);
                $result['title'] = trim($spuncontent[0]);
                $spunpost = trim($spuncontent[1]);


                $multispace = array("     ", "    ", "   ", "  ");
                $spunpost = str_replace($multispace, " ", $spunpost);
                $spunpost = stripslashes($spunpost);
                $newcontent = str_replace(array(" ,", " ."), array(",", "."), $spunpost);
                if (count($matches[0]) > 0) {
                $result['content'] = str_replace($pimg, $oimg, $newcontent);
                }
                else
                {
                $result['content'] = $newcontent;
                }
                }
                */
            }
        }
    }

    return $result;


}

function tbs_master_spinner($content)
{
    $words = explode("{", $content);
    $newtext = "";
    foreach ($words as $word) {
        $words = explode("}", $word);
        foreach ($words as $word) {
            $words = explode("|", $word);
            $hn = count($words) - 1;
            $hr = rand(1, $hn);
            $nr = array_rand($words, 1);
            //if($nr == 0){ $nr = $hr; }
            $word = $words[$nr];
            $newtext .= $word . " ";
        }

    }
    return $newtext;
}
function tbspinner_curl_post($url, $data, &$info)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, tbspinner_curl_postData($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    if (ini_get('open_basedir') != '' || ini_get('safe_mode') != 0) {
        $html = tbspinner_curl_redir_exec($ch);
    }
    else {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $html = trim(curl_exec($ch));
        
    }
    curl_close($ch);

    return $html;
}
function tbspinner_curl_redir_exec($ch){
    static $curl_loops = 0;
    static $curl_max_loops = 20;
    if ($curl_loops++ >= $curl_max_loops){
        $curl_loops = 0;
        return false;
    }
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    list($header, $data) = explode("\n\n", $data, 2);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302){
        $matches = array();
        preg_match('/Location:(.*?)\n/', $header, $matches);
        $url = @parse_url(trim(array_pop($matches)));
        if(!$url){
            $curl_loops = 0;
            return $data;
        }
        $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
        if (!$url['scheme']) { $url['scheme'] = $last_url['scheme']; }
        if (!$url['host']){ $url['host'] = $last_url['host']; }
        if (!$url['path']) { $url['path'] = $last_url['path']; }
        $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
        curl_setopt($ch, CURLOPT_URL, $new_url);
        return curl_redir_exec($ch);
    }
    else {
        $curl_loops = 0;
        return $data;
    }

}
function tbspinner_curl_postData($data)
{

    $fdata = "";
    foreach ($data as $key => $val) {
        $fdata .= "$key=" . urlencode($val) . "&";
    }

    return $fdata;

}
?>