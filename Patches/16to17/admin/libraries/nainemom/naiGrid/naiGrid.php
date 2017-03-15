<?php

//$system_xml['SYSTEM']['NAIGRID']['SUBROWS']


	class naiGrid {
		private function getCount( $query ){
			global $nai_db;
			$queryCount = "SELECT COUNT(*) AS count FROM ( $query ) AS countQuery;";
			$_temp = $nai_db->querytoarray( $queryCount );
			return $_temp[0]['count'];
		}
		private function nai_excel_header( $array ){
			$ret = '<table><tr>';
			$style = "background-color: #43494B; border: solid 1px #363A3C; color: #fff; text-align: center; font-family: Tahoma;height: 40px;";
			foreach($array as $val){
				$ret.='<th style="'.$style.'">&nbsp;&nbsp;&nbsp;&nbsp;'. htmlspecialchars($val) . '&nbsp;&nbsp;&nbsp;&nbsp;</th>';
			}
			$ret.='</tr>';
			return $ret;
		}
		
		private function nai_excel_row( $array ){
			$ret = '<tr>';
			$style = "border: solid 1px #363A3C; text-align: center; font-family: Tahoma; height: 25px;";
			foreach($array as $val){
				$ret.='<td style="'.$style.'">'. htmlspecialchars($val) . '</td>';
			}
			$ret.='</tr>';
			return $ret;
		}
		
		private function nai_excel_footer( ){
			return '</table>';
		}
		private function setXmlHeader(){
			OB_CLEAN();
			header("Content-type: text/xml;charset=utf-8");

		}
		private function settings($key='rows'){
			$system_xml_file_address = file_get_contents( 'system.xml');
			$system_xml = XMLtoArray($system_xml_file_address);
			return $system_xml['SYSTEM']['NAIGRID'][ strtoupper($key) ];
		}
		private function getXmlData( $query, $limit, $page, $orderBy, $orderType){
			global $nai_db;
			$page = minor( $page, 1 );
			$limit = minor( $limit, 1 );
			$limitStart = $limit * $page - $limit;
			
			/* check if query has not ; at end */
			$count = $this->getCount($query);
			
			$pages = minor( ceil( $count / $limit ), 1 );
			
			
			$dataQueryPlus = null;
			if( $orderBy ) $dataQueryPlus .= ' ORDER BY `' . $orderBy . '` ' . $orderType;
			$dataQueryPlus .= ' LIMIT ' . $limitStart . ' , ' . $limit;
			
			$rows = $nai_db->querytoarray($query.$dataQueryPlus.';');
			
			$data = array(
				'rows'=>$rows,
				'count'=>$count,
				'pages'=>$pages,
				'page'=>$page
			);
			return $data;
		}
		

		public function getXml( $query, $limit=0, $page=null, $orderBy=null, $orderType=null ){
			if( $limit < 1 ){
				$limit = (int)$this->settings('rows');
			}
			$this->setXmlHeader();
			$data = $this->getXmlData($query, $limit, $page, $orderBy, $orderType);
			echo '<xml>';
			echo  "<rows>";
			if( $data['rows'] ){
				foreach( $data['rows'] as $row ){
					$formatField_ = $this->formatField( $row );
					echo '<row id="'. $formatField_['key']. '">';
					foreach( $formatField_['cells'] as $cell )
						echo '<cell>'. htmlspecialchars( $cell ). '</cell>';
					echo '</row>';
				}
			}
			echo '<page>'. $data['page'] .'</page>';
			echo '<total>'. $data['pages'] .'</total>';
			echo '<records>'. $data['count'] .'</records>';
			echo '<userdata name="querythis">'. htmlspecialchars($query.$dataQueryPlus.';') .'</userdata>';
			echo '<userdata name="query">'. htmlspecialchars($query.';') .'</userdata>';
			echo '</rows>';
			
			echo '</xml>';
			exit;
		}
		public function getSubXml( $query, $limit=0, $page=null, $orderBy=null, $orderType=null ){
			if( $limit < 1 ){
				$limit = (int)$this->settings('subrows');
			}
			$this->setXmlHeader();
			$data = $this->getXmlData($query, $limit, $page, $orderBy, $orderType);
			echo  "<rows>";

			if( $data['rows'] ){
				foreach( $data['rows'] as $row ){
					$formatField_ = $this->formatField( $row );
					echo '<row>';
					foreach( $formatField_['cells'] as $cell )
						echo '<cell>'. htmlspecialchars( $cell ). '</cell>';
					echo '</row>';
				}
			}
			echo '</rows>';
			exit;
		}

		public function getExcel( $query, $colNames, $orderBy=null, $orderType=null ){
			
			global $nai_db;
			$limit = 65536; // excel 2007+
			$count = $this->getCount($query);
			
			if( $count > $limit ){ // overflow
				echo 'overflow';
				exit;
			}	

			$dataQueryPlus = null;
			if( $orderBy ) $dataQueryPlus .= ' ORDER BY `' . $orderBy . '` ' . $orderType;
			
			
			$result = $nai_db->query($query.$dataQueryPlus.';');
			

			OB_CLEAN();
			//header("Content-type: text/xml;charset=utf-8");
			
			echo $this->nai_excel_header( $colNames );
			while( $row = mysql_fetch_array($result,MYSQL_ASSOC) ) {
				$formatField_ = $this->formatField( $row );
				echo $this->nai_excel_row( $formatField_['cells'] );
			}
			echo $this->nai_excel_footer();
			exit;
			
		}
		
		function formatField( $row ){
			$ret = array();
			foreach( $row as $cell ){
				if( ! isset( $ret['key'] ) )
					$ret['key'] = $cell;
				else
					$ret['cells'][] = $cell;
			}
			return $ret;
		}
		
	}

?>