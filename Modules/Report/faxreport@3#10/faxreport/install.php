<?php
//added for 2.11
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//

global $db;
global $amp_conf;

$autoincrement =  "AUTO_INCREMENT";

$sql = "
	CREATE TABLE IF NOT EXISTS `tbl_fax` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `from` char(50) NOT NULL,
	  `to` char(50) NOT NULL,
	  `type` tinyint(1) NOT NULL,
	  `sendstatus` tinyint(4) NOT NULL,
	  `read` tinyint(1) NOT NULL,
	  `fileaddress` char(250),
	  `sendtime` datetime NOT NULL,
	  `senttime` datetime DEFAULT NULL,
	  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";

$check = $db->query($sql);

if (DB::IsError($check)) {
	die_freepbx( "Can not create `sgsbox` table: " . $check->getMessage() .  "\n");
}

?>
