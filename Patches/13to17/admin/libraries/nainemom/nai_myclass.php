<?php // myclass.php
include_once('jdf.php');

function XMLtoArray($XML)
{
    $xml_parser = xml_parser_create();
    xml_parse_into_struct($xml_parser, $XML, $vals);
    xml_parser_free($xml_parser);
    // wyznaczamy tablice z powtarzajacymi sie tagami na tym samym poziomie
    $_tmp='';
    foreach ($vals as $xml_elem) {
        $x_tag=$xml_elem['tag'];
        $x_level=$xml_elem['level'];
        $x_type=$xml_elem['type'];
        if ($x_level!=1 && $x_type == 'close') {
            if (isset($multi_key[$x_tag][$x_level]))
                $multi_key[$x_tag][$x_level]=1;
            else
                $multi_key[$x_tag][$x_level]=0;
        }
        if ($x_level!=1 && $x_type == 'complete') {
            if ($_tmp==$x_tag)
                $multi_key[$x_tag][$x_level]=1;
            $_tmp=$x_tag;
        }
    }
    // jedziemy po tablicy
    foreach ($vals as $xml_elem) {
        $x_tag=$xml_elem['tag'];
        $x_level=$xml_elem['level'];
        $x_type=$xml_elem['type'];
        if ($x_type == 'open')
            $level[$x_level] = $x_tag;
        $start_level = 1;
        $php_stmt = '$xml_array';
        if ($x_type=='close' && $x_level!=1)
            $multi_key[$x_tag][$x_level]++;
        while ($start_level < $x_level) {
            $php_stmt .= '[$level['.$start_level.']]';
            if (isset($multi_key[$level[$start_level]][$start_level]) && $multi_key[$level[$start_level]][$start_level])
                $php_stmt .= '['.($multi_key[$level[$start_level]][$start_level]-1).']';
            $start_level++;
        }
        $add='';
        if (isset($multi_key[$x_tag][$x_level]) && $multi_key[$x_tag][$x_level] && ($x_type=='open' || $x_type=='complete')) {
            if (!isset($multi_key2[$x_tag][$x_level]))
                $multi_key2[$x_tag][$x_level]=0;
            else
                $multi_key2[$x_tag][$x_level]++;
            $add='['.$multi_key2[$x_tag][$x_level].']';
        }
        if (isset($xml_elem['value']) && trim($xml_elem['value'])!='' && !array_key_exists('attributes', $xml_elem)) {
            if ($x_type == 'open')
                $php_stmt_main=$php_stmt.'[$x_type]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
            else
                $php_stmt_main=$php_stmt.'[$x_tag]'.$add.' = $xml_elem[\'value\'];';
            eval($php_stmt_main);
        }
        if (array_key_exists('attributes', $xml_elem)) {
            if (isset($xml_elem['value'])) {
                $php_stmt_main=$php_stmt.'[$x_tag]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
                eval($php_stmt_main);
            }
            foreach ($xml_elem['attributes'] as $key=>$value) {
                $php_stmt_att=$php_stmt.'[$x_tag]'.$add.'[$key] = $value;';
                eval($php_stmt_att);
            }
        }
    }
    return $xml_array;
}

function str2hex($string){
    $hex='';
	$strSplit = str_split($string);
	foreach($strSplit as $char)
		$hex .= dechex(ord($char));
    return $hex;
}
function hex2str($hex){
    $string='';
	$strSplit = str_split($hex);
    foreach($strSplit as $key=>$char)
		if( ($key+1) % 2 == 0 )
			$string .= chr(hexdec($hex[$key-1].$hex[$key]));
    return $string;
}
function nai_encode($string,$key='1,2,3,4'){
	$ret = null;
	$j = 0;
	$str_len = strlen($string);
	$keys = explode(',',$key);
	if($str_len<1 || !@$keys[0] ) return null;
    for ($i=0; $i < $str_len; $i++){
        $ret .= chr( plus_ascii( ord($string[$i]), (int)$keys[$j++] ) );
		if(!@$keys[$j]) $j = 0;
    }
	return $ret;
}
function nai_decode($string,$key='1,2,3,4'){
	$ret = null;
	$j = 0;
	$str_len = strlen($string);
	$keys = explode(',',$key);
	if($str_len<1 || !@$keys[0] ) return null;
    for ($i=0; $i < $str_len; $i++){
        $ret .= chr( minus_ascii( ord($string[$i]), $keys[$j++] ) );
		if(!@$keys[$j]) $j = 0;
    }
	return $ret;
}
function nai_query_encode($query){
	return str2hex( nai_encode( $query, '11,32,34,65,90' ) );
}
function nai_query_decode($encoded_query){
	return nai_decode( hex2str( $encoded_query ), '11,32,34,65,90' );
}
function plus_ascii($ascii,$val){
	return ($ascii+$val)%256;
}
function minus_ascii($ascii,$val){
	$ret = $ascii-$val;
	if($ret<0)
		while($ret<0)
			$ret+=256;
	return $ret;
}
function f_uck_string($psw,$id=null){
    $x=false;
    $psw = str2hex($psw.$id);
    for($i=0;$i<30018;$i++){
        $x=!$x;
        $psw = md5($psw,$x);
    }    
    return $psw;
}
function fu_ck_string($psw,$id=null){
    $x=true;
    $psw = str2hex(str2hex($psw.$id));
    for($i=0;$i<30018;$i++){
        $x=!$x;
        $psw = md5($psw,$x);
    }    
    return $psw;
}

function minlength($str,$min=2,$before=true,$char='0'){	
	$ret = $str;
	if($before){
		while(strlen($ret)<$min)
			$ret = $char.$ret;
	}
	else{
		while(strlen($ret)<$min)
			$ret+=$char;
	}
	return $ret;
}
function justlength($str,$length,$after=true,$char='0'){
	$strlength = strlen($str);
	if( $strlength > $length )
		return substr($str,0,$length);
	else
		return minlength($str,$length,!$after,$char);
	
}


function validate_password($password,$min=4,$max=24){
    $re = '/^[\w\d]{'.$min.','.$max.'}$/';
    return (bool) preg_match($re, $password);
}
function validate_name($name,$min=2,$max=24){
    $cot = "'";
    //$re = '/^[A-Za-z0-9 '.$cot.']{'.$min.','.$max.'}$/';
	$re = "/^[پچجحخهعغفقثصضشسیبلاتنمکگوئدذرزطظژؤآإأءًٌٍَُِّ A-z 0-9 -._+=()*&^%$#@! \s]+$/";
    return (bool) preg_match($re, $name);
}

function validate_number($name,$min=2,$max=24){
    $cot = "'";
    $re = '/^[0-9]{'.$min.','.$max.'}$/';
    return (bool) preg_match($re, $name);
}
function validate_email($email){
	$re = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
	return (bool) preg_match($re, $email);
}
function fileor(&$x,$or = null){
    return isset($x)&&$x['size']>0?$x:$or;
}
function getor(&$x,$or=null){   
    return isset($x)?$x:$or;
}
function moreor(&$x,$val,$or=null){
    return $x>$val?$x:$or;
}
function lessor(&$x,$val,$or=null){
    return $x<$val?$x:$or;
}
function betweenor(&$x,$more,$less,$or=null){
    return $x>$more&&$x<$less?$x:$or;
}
function minor($x,$val){
    return $x>$val?$x:$val;
}
function maxor($x,$val){
    return $x<$val?$x:$val;
}


function randomstring($length,$_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'){
    $_ret = null;
	$i = 0;
	$charsLen = strlen($_chars);
    while($i++<$length)
        $_ret.= $_chars[rand(0,$charsLen-1)];
    return $_ret;
}
function filecontent(&$file){
    $tmpName = $file["tmp_name"];
    $fop = fopen($tmpName, 'r');
    $content = fread($fop, filesize($tmpName));
    fclose($fop);
    return $content;
}
function pic_content($pic){
    ob_start();
    imagejpeg($pic);
    $contents =  ob_get_contents();
    ob_end_clean();
    return $contents;
}

function nai_join($arr,$sp=',',$cot=''){
	$ret='';
	$spt='';
	for($i=0;@$arr[$i];$i++){
		$ret.=$spt.$cot.$arr[$i].$cot;
		$spt=$sp;
	}
	return $ret;
}
function nai_split($str,$sp=',',$base64=false){
	$ret=explode($sp,$str);
	if(!@$ret[0]){
		return array();
	}
	else if($base64){
		for($i=0;@$ret[$i];$i++){
			$ret[$i] = base64_decode($base64);
		}
	}
	return $ret;
}
	

//  **********************************************************************
//  * This is MySql class
//  * Status: Complate?!
//  **********************************************************************
class mysql{
    private $_databaselink=null,$_error=0,$_errortext='';

    public function connect($databaseserver='localhost',$databaseuser='root',$databasepassword=null){
		$this->_databaselink = mysql_pconnect($databaseserver,$databaseuser,$databasepassword);
        return (bool)$this->_databaselink;
    }
	public function selectdatabase($databasename){
	return (bool)mysql_select_db($databasename);
	}
    public function close(){
        return (bool)mysql_close();
    }
    public function querytoarray($query,$numeric = false){
		
        $ret = array('');
        $result = mysql_query($query);
        $this->_error = mysql_errno($this->_databaselink);
		$this->_errortext - mysql_error($this->_databaselink);
        if(!$this->_error){
            $ret = array();
            if($numeric) $type = MYSQL_NUM; else $type = MYSQL_ASSOC;
            while( $ret[] = mysql_fetch_array($result, $type) ){}

			
			unset($ret[ count($ret)-1 ]);
			
            return $ret;
        }
        else{
            return false;
        } 
    }
    public function query($query){
        $result = mysql_query($query);
        $this->_error = mysql_errno($this->_databaselink);
		$this->_errortext - mysql_error($this->_databaselink);
        if(!$this->_error){
            return $result;
        }
        else{
            return false;
        } 
    }
    public function error(){
        return $this->_error;
    }
	public function errortext(){
		return $this->_errortext;
	}
    public function link(){
        return $_databaselink;
    }
}





function tag($tagname,$attr='',$text=''){
	if( !ncl() ) return false;
	return("<$tagname $attr>$text</$tagname>");
}
function otag($tagname,$attr=''){
	return("<$tagname $attr>");
}
function ctag($tagname){
	if( !ncl() ) return false;
	return("</$tagname>");
}
function stag($tagname,$attr=''){
	return("<$tagname $attr/>");
}

function include_css($userAddress,$autoecho=true){
	$ret = tag('link',"rel=\"stylesheet\" type=\"text/css\" href=\"$userAddress\"");
	if($autoecho) echo $ret; else return $ret;
}
function include_js($userAddress,$autoecho=true){
	$ret = tag('script',"type=\"text/javascript\" src=\"$userAddress\"");
	if($autoecho) echo $ret; else return $ret;
}
function msort($array, $key, $sort_flags = SORT_REGULAR) {
    if (is_array($array) && count($array) > 0) {
        if (!empty($key)) {
            $mapping = array();
            foreach ($array as $k => $v) {
                $sort_key = '';
                if (!is_array($key)) {
                    $sort_key = $v[$key];
                } else {
                    // @TODO This should be fixed, now it will be sorted as string
                    foreach ($key as $key_key) {
                        $sort_key .= $v[$key_key];
                    }
                    $sort_flags = SORT_STRING;
                }
                $mapping[$k] = $sort_key;
            }
            asort($mapping, $sort_flags);
            $sorted = array();
            foreach ($mapping as $k => $v) {
                $sorted[] = $array[$k];
            }
            return $sorted;
        }
    }
    return $array;
}
function show_query($query){
	$bir = array(';','SELECT','FROM','WHERE','GROUP BY','ORDER BY','LIMIT','JOIN','INNER','OUTER','AND','OR','LIKE','IN','BETWEEN');
	foreach($bir as $val){
		$query = str_replace($val, '<span style="color:#4B91A2;display: inline-block;direction:ltr;margin:0 1px;font-family: Tahoma;">'.$val.'</span>', $query);
	}
	$query = str_replace("\n", '<br/>', $query);
	$query = str_replace("\t", '<span style="width:40px;display: inline-block;"></span>', $query);
	echo "<div class=\"show_query\" style='direction:ltr;text-align:left;font-family: Tahoma;'>$query</div>";
}


function nai_get_browser() 
{ 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
    
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    } 
    
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
    
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
    
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
} 





	function nai_all_days_as_option($now = false){
		$ret = '';
		for($i=1;$i<=31;$i++){
			if($now && (int)jdate('d') == $i) $check = 'selected="true"'; else $check = '';
			$ret.="<option value=\"$i\" $check>$i</option>";
		}
		return $ret;
	}
	function nai_all_months_as_option($now = false){
		$ret = '';
		$months = array('01' => 'فروردین', '02' => 'اردیبهشت', '03' => 'خرداد', '04' => 'تیر', '05' => 'مرداد', '06' => 'شهریور', '07' => 'مهر', '08' => 'آبان', '09' => 'آذر', '10' => 'دی', '11' => 'بهمن', '12' => 'اسفند');
		foreach($months as $monthnum=>$monthname){
			if($now && (int)jdate('m') == $monthnum) $check = 'selected="true"'; else $check = '';
			$ret.="<option value=\"$monthnum\" $check>$monthname</option>";
		}
		return $ret;
	}
	function nai_all_years_as_option($now = false){
		$ret = '';
		for ($i = 1380; $i <= jdate('Y'); $i++) {
			if($now && (int)jdate('Y') == $i) $check = 'selected="true"'; else $check = '';
			$ret.="<option value=\"$i\" $check>$i</option>";
		}
		return $ret;
	}
	function nai_all_hours_as_option(){
		$ret = '';
		for ($i = 0; $i < 24; $i++) {
			$ret.="<option value=\"$i\">$i</option>";
		}
		return $ret;
	}
	function nai_all_years_later_as_option($now = false){
		$ret = '';
		for ($i = jdate('Y'); $i <= (jdate('Y')+10); $i++) {
			if($now && (int)jdate('Y') == $i) $check = 'selected="true"'; else $check = '';
			$ret.="<option value=\"$i\" $check>$i</option>";
		}
		return $ret;
	}
	function nai_jsmsg($msg){
		return "<script> alert(\"$msg\"); </script>";
	}
	function nai_file_download($file_url, $dlname='', $justob=false){
		if($justob)
			ob_clean();
		else
			OB_END_CLEAN();
		if(file_exists($file_url)){
			$file_name = basename($file_url);
			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: Binary"); 
			header("Content-disposition: attachment; filename=\"".($dlname!=''?$dlname:$file_name)."\"");
			if($justob)
				ob_clean();
			else
				OB_END_CLEAN();
			readfile($file_url);
			exit();
		}else{
			header("HTTP/1.0 404 Not Found");
			if($justob)
				ob_clean();
			else
				OB_END_CLEAN();
			exit();
		}
	}
	function nai_content_download($content, $dlname=''){
		OB_END_CLEAN();
		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: Binary"); 
		header("Content-disposition: attachment; filename=\"".$dlname."\"");
		OB_END_CLEAN();
		echo($content);
		exit();

	}
	function nai_file($file_url){
		$filename =  basename($file_url);
		$pos = strrpos($file, '.');
		$extension = ($pos !== false) ? substr($file, $pos) : '';
		$mime_type = "";
		switch ($extension) {
			case '.mp3':
				$mime_type = "audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3";
				break;
			case '.ulaw':
				$mime_type = "audio/basic";
				break;
			case '.gsm':
				$mime_type = "audio/x-gsm";
				break;
			case '.wav':
				$mime_type = "audio/x-wav, audio/wav";
				break;
		}


		OB_END_CLEAN();
		if(file_exists($file_url)){
			$file_name = basename($file_url);
			header('Content-Type: audio/x-wav, '.$mime_type);
			header("filename=\"".'document.'.$extension."\"");
			OB_END_CLEAN();
			readfile($file_url);
			exit();
		}else{
			header("HTTP/1.0 404 Not Found");
			OB_END_CLEAN();
			exit();
		}
	}
	
	
	function set_header($header){
		switch($header){
			case 'excel':
				// Redirect output to a client’s web browser (Excel5)
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="document.xls"');
				header('Cache-Control: max-age=0');
				// If you're serving to IE 9, then the following may be needed
				header('Cache-Control: max-age=1');

				// If you're serving to IE over SSL, then the following may be needed
				header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
				header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
				header ('Pragma: public'); // HTTP/1.0
				break;
		}

	}

	function nai_excel_header( $array ){
		$ret = '<table><tr>';
		foreach($array as $val){
			$ret.='<th>'. htmlspecialchars($val) . '</th>';
		}
		$ret.='</tr>';
		return $ret;
	}
	
	function nai_excel_row( $array ){
		$ret = '<tr>';
		foreach($array as $val){
			$ret.='<td>'. htmlspecialchars($val) . '</td>';
		}
		$ret.='</tr>';
		return $ret;
	}
	
	function nai_excel_footer( $array ){
		return '</table>';
	}
?>
