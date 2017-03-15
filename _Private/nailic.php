<?php
	function nai_mac_address() {
		exec('netstat -ie', $result);
		if(is_array($result)) {
			$iface = array();
			foreach($result as $key => $line) {
				if($key > 0) {
					$tmp = str_replace(" ", "", substr($line, 0, 10));
					if($tmp <> "") {
						$macpos = strpos($line, "HWaddr");
						if($macpos !== false) {
							$iface[] = array('iface' => $tmp, 'mac' => strtolower(substr($line, $macpos+7, 17)));
						}
					}
				}
			}
			return $iface[0]['mac'];
		}
		else {
			return "notfound";
		}
	}
	function nai_server_id(){
		return md5( nai_mac_address() );
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
	
	if( file_put_contents(
		'nailic',
		str2hex( nai_server_id() )
	) )
		echo shell_exec("echo `tput setaf 2`Successfully licensed!`tput sgr0`");
	else
		echo shell_exec("echo `tput setaf 1`Something is wrong!`tput sgr0`");

?>