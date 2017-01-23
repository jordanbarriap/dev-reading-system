<?php
include("config.php");
include("dbFunctions.php");

// for debugging
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

$grp = $obj["grp"];
$usr = $obj["usr"];
$sid = $obj["sid"];

$data = $obj["data"];

foreach ($data as $positionTime) {
	$bookid = $positionTime["bookid"];
	$docno = $positionTime["docno"];
	$page = $positionTime["page"];
	$question = $positionTime["question"];
	$top = $positionTime["top"];
	$bottom = $positionTime["bottom"];
	$time = $positionTime["time"];
	$datetime = $positionTime["datetime"];
	$milliseconds = $positionTime["milliseconds"];

	$filename= $positionTime["filename"];//added by jbarriapineda in 10-16

	$pagefileid = $bookid."_".$filename;//added by jbarriapineda in 10-08
	
	$sql=insertProgress($usr, $grp, $sid, $pagefileid, $question, $top, $bottom, $time, $datetime, $milliseconds);//added by jbarriapineda in 10-08
	
	//insertProgress($usr, $grp, $sid, $bookid, $docno, $page, $question, $top, $bottom, $time);
	// TODO: report success/failure
}
?>