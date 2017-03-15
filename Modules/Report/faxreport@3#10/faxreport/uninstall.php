<?php
//This file is part of FreePBX.

$sql = "DROP TABLE tbl_fax";
$result = $db->query($sql);
if(DB::IsError($result)) {
        echo "ERROR DELETING TABLE: ".$result->getDebugInfo();
}

?>