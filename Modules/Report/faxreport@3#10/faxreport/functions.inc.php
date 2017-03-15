 <?php
	function nai_fax_report_truncate(){
		global $nai_db;
		$nai_db->query("TRUNCATE TABLE tbl_fax");
	}
	function nai_fax_report_set_fake_data(){
		global $nai_db;
		nai_fax_report_truncate();
		$faxes = nai_fax_get_fax_devices();
		$tt = time() - 900000;
		for($i=0;$i<200;$i++){
			$type = randomstring(1,'01');
			if($type=='1'){//in
				$from = randomstring(1,'123456789'). randomstring(6,'0123456789');
				$to = $faxes[(int)rand(0,count($faxes)-1)]['id'];
				$sendstatus = 0;
				$read = randomstring(1,'01');
				$fileaddress = '';
				$sendtime = '';
				$senttime = '';
			}else{
				$from = $faxes[(int)rand(0,count($faxes)-1)]['id'];
				$to = randomstring(1,'123456789'). randomstring(6,'0123456789');
				$sendstatus = (int)rand(1,4);
				$read = '0';
				$fileaddress = '';
				$sendtime = date( "Y-m-d H:i:s",$tt);
				if($sendstatus==1) $senttime = date( "Y-m-d H:i:s",$tt + 300); else $senttime = '';
			}
			$query =
			"INSERT INTO `tbl_fax`
			VALUES(NULL,'$from','$to','$type','$sendstatus','$read',NULL,'$sendtime','$senttime',NULL);";
			$nai_db->query($query);
		}
	}
	function nai_fax_report_make_query($type,$from,$to,$status,$datefrom,$dateto,$q_count=false){
/*
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `from` char(50) NOT NULL,
	  `to` char(50) NOT NULL,
	  `type` tinyint(1) NOT NULL,
	  `sendstatus` tinyint(4) NOT NULL,
	  `read` tinyint(1) NOT NULL,
	  `sendtime` int(11) NOT NULL,
	  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
*/
		$zd = new nai_zdate();
		
		if($from=='*'||$from=='')$from='%';
		if($to=='*'||$to=='')$to='%';
		
		if($q_count)
			$fields='COUNT(*)';
		else
			$fields='`id`,`from`,`to`,`type`,`sendstatus`,`read`,`sendtime`,`created_time`,`senttime`,`fileaddress`';

		$query = "SELECT $fields FROM `tbl_fax` WHERE ";
		
		
		$datefrom_ = $zd->pdate_to_mysqldate($datefrom);
		$dateto_ = $zd->pdate_to_mysqldate($dateto,'/',true);
		
		$query.= "`type` LIKE '$type' AND ";
		
		$query.= "`from` LIKE '$from' AND `to` LIKE '$to' AND ";
		$query.= "`created_time` BETWEEN '$datefrom_' AND '$dateto_' AND ";
		
		$status_ = 'IN(';
		$sp = '';
		foreach($status as $sts){
			$status_.= "$sp$sts";
			$sp = ',';
		}
		$status_.= ')';
		$query.=($type=='1')?
					("`read` $status_")
				:
					("`sendstatus` $status_ ");
		
		return $query;
	}
	function nai_fax_report_delete($ids){
		global $nai_db;
		$n = '';
		$sp = '';
		foreach($ids as $fax){
			$n .= $sp.$fax;
			$sp = ',';
		}
		
		$query="DELETE FROM `tbl_fax` WHERE `id` IN($n)";
		$nai_db->query($query);
		return true;
	}
	function nai_fax_report_make_as_read($ids){
		global $nai_db;
		$n = '';
		$sp = '';
		if(is_array($ids)){
			foreach($ids as $fax){
				$n .= $sp.$fax;
				$sp = ',';
			}
		}
		else{
			$n = $ids;
		}
		$query="UPDATE `tbl_fax` SET `read` = '1' WHERE `id` IN($n)";
		$nai_db->query($query);
		return $query;
	}
	function nai_fax_report_get_file_address($id){
		global $nai_db;
		global $dirname;
		$check = $nai_db->querytoarray("SELECT * FROM `tbl_fax` WHERE `id` = '$id' ;");
		//nai_fax_report_make_as_read($id);
		$filename = $check[0]['fileaddress'];
		$url =  $_SERVER['SERVER_NAME']; // site address
		$drn = substr($dirname, 0, strlen($dirname)-6 );
		$file = "$drn/$filename";
		return $file;
	}
	function nai_fax_report_exist($id){
		global $nai_db;
		$check = $nai_db->querytoarray("SELECT * FROM `tbl_fax` WHERE `id` = '$id' ;");
		if( !array_filter($check) ){
			return false;
		}
		return true;
	}
	function nai_faxreport_getlist_forper(){
		$ret = array();
		$i = 0;
		$faxes = nai_fax_get_fax_devices();
		if (isset($faxes)) {
			foreach ($faxes as $fax) {
				$ret[$i]['id'] = $fax['id'];
				$ret[$i]['value'] = "device=$fax[id]";
				$ret[$i++]['title'] = "دستگاه فکس : $fax[number]";;
			}
		}
		return $ret;
	}

	class FaxReportGridIn extends naiGrid{
		function formatField( $row ){
			global $zd;
			return array(
				'key'=> $row['id'],
				'cells'=> array(
					$row['from'],
					$row['to'] ,
					$row['read'] ,
					$zd->grid_format( $zd->mysqldate_to_timestamp( $row['created_time'] ) ),
					$row['id']
				)
			);

		}
	}
	$faxReportGridIn = new FaxReportGridIn;
	
	class FaxReportGridOut extends naiGrid{
		function formatField( $row ){
			global $zd;
			return array(
				'key'=> $row['id'],
				'cells'=> array(
					$row['from'],
					$row['to'] ,
					$row['sendstatus'] ,
					$zd->grid_format( $zd->mysqldate_to_timestamp( $row['created_time'] ) ),
					$zd->grid_format( $zd->mysqldate_to_timestamp( $row['sendtime'] ) ),
					$row['senttime'] == '0000-00-00 00:00:00'? '---': $zd->grid_format( $zd->mysqldate_to_timestamp( $row['senttime'] ) ),
					$row['id']
				)
			);

		}
	}
	$faxReportGridOut = new FaxReportGridOut;
	
	if( getor($_GET['grid']) == 'true' && getor($_GET['nai_module']) == 'faxreport' ){
		$faxReportGridObj = $_GET['faxtype'] == 1? $faxReportGridIn: $faxReportGridOut;
		switch( getor( $_REQUEST['oper'] ) ){
		case 'grid':
			$faxReportGridObj->getXml(
				nai_query_decode( $_REQUEST['query'] ),
				$_REQUEST['rows'],
				$_REQUEST['page'],
				$_REQUEST['sidx'],
				$_REQUEST['sord']
			);
			break;
		case 'excel':
			$faxReportGridObj->getExcel(
				nai_query_decode( $_REQUEST['query'] ),
				$_REQUEST['colNames'],
				$_REQUEST['sidx'],
				$_REQUEST['sord']
			);
			break;
		}
		
/*
		global $nai_db;
		$zd = new nai_zdate();
		$query = nai_query_decode( $_GET['query'] );
		$query_count = nai_query_decode($_GET['query_count']);
		
		$page = getor($_GET['page'],1); 
		$limit = getor($_GET['rows'],20); 
		$sidx = getor($_GET['sidx'],1); 
		$sord = getor($_GET['sord'],'DESC'); 
		//if(!$sidx) $sidx =1; 
		 
		// select the database 
		//mysql_select_db($database) or die("Error connecting to db."); 
		 
		// calculate the number of rows for the query. We need this for paging the result 
		$result = $nai_db->querytoarray( $query_count ); 
		//die(  $query_count);
		$count = $result[0]['COUNT(*)']; 
		 
		// calculate the total pages for the query 
		if( $count > 0 && $limit > 0) { 
			$total_pages = ceil($count/$limit); 
		} else { 
			$total_pages = 0; 
		} 
		 
		// if for some reasons the requested page is greater than the total 
		// set the requested page to total page 
		if ($page > $total_pages) $page=$total_pages;
		 
		// calculate the starting position of the rows 
		$start = $limit*$page - $limit;
		 
		// if for some reasons start position is negative set it to 0 
		// typical case is that the user type 0 for the requested page 
		if( $start < 0 ) $start = 0; 
		 
		// the actual query for the grid data 

		$SQL = "$query ORDER BY $sidx $sord LIMIT $start , $limit;"; 
		$result = $nai_db->query($SQL) or die("Couldn't execute query.".mysql_error().'<br>'.$SQL); 

		// we should set the appropriate header information. Do not forget this.
		OB_END_CLEAN();
		header("Content-type: text/xml;charset=utf-8");
		 
		$s = "<?xml version='1.0' encoding='utf-8'?>";
		$s .=  "<rows>";
		$s .= "<page>".$page."</page>";
		$s .= "<total>".$total_pages."</total>";
		$s .= "<records>".$count."</records>";
		 
		// be sure to put text data in CDATA

		while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			
			$s .= "<row id='". $row['id']."'>";
			if($_GET['faxtype']=='0'){ // out

				$s .= "<cell>". $row['from']."</cell>";
				$s .= "<cell>". $row['to']."</cell>";

				$s .= "<cell>". $row['sendstatus']."</cell>";
				
				$s .= "<cell>". jdate("j F y ساعت H:i",$zd->mysqldate_to_timestamp($row['created_time'])) ."</cell>";
				$s .= "<cell>". jdate("j F y ساعت H:i",$zd->mysqldate_to_timestamp($row['sendtime'])) ."</cell>";
				if($row['senttime']=='0000-00-00 00:00:00')
					$s .= "<cell>---</cell>";
				else
					$s .= "<cell>". jdate("j F y ساعت H:i",$zd->mysqldate_to_timestamp($row['senttime'])) ."</cell>";
					
				$s .= "<cell>". ($row['id'])."</cell>";
				
			}
			else{ //in

				$s .= "<cell>". $row['from']."</cell>";
				$s .= "<cell>". $row['to']."</cell>";
				

				$s .= "<cell>". $row['read']."</cell>";

				
				$s .= "<cell>". jdate("j F y ساعت H:i",$zd->mysqldate_to_timestamp($row['created_time'])) ."</cell>";
				$s .= "<cell>". ($row['id'])."</cell>";
			}

			$s .= "</row>";
		}
		$s .= "</rows>"; 
		 
		die( $s );

*/
	}
?>
