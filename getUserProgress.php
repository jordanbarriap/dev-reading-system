<?php
/**
  * getUserProgress
  * parameters: 
  *     usr
  *     grp
  *     sid
  * Response: 
  *   JSON array
  *   -1 : error, the group is not defined for the eReader
  *   -2 : 
  *   -3 : 
  *   -4 : 
  *
  * @author Julio Guerra (PAWS group, iSchool, University of Pittsburgh)
  **/

$usr = $_GET["usr"];
$grp = $_GET["grp"];
$sid = $_GET["sid"];
$mode = $_GET["mode"];
if (!isset($mode) || strlen($mode) == 0) $mode = "all";
//$course = $_GET["course"];
//$domain = $_GET["domain"];

include("config.php");
include("dbFunctions.php");
include("userFunctions.php");

header('Content-Type: application/json');

$progress = computeUsrProgress($usr, $grp);
/*
foreach($progress as $key => $lec){
    echo $key." ".$lec["progress"]." CONF: ".$lec["confidence"]."<br />";
    echo "<blockquote>";
    foreach($lec["docs"] as $key2 => $doc){
        echo $key2." ".$doc["progress"]." CONF: ".$doc["confidence"]."<br />";
    }
    echo "</blockquote>";
}
*/
//var_dump($progress);

$jsonoutput = generateJSON($usr, $grp, $progress, !($mode == "lecture"));

echo $jsonoutput;

?>