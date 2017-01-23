<?php
/** reportAction
 *  
 *      Doc: https://docs.google.com/spreadsheet/ccc?key=0AuwVS4EheR-EdDRvVVJvdGhyTWlWWGlUWXY5VXBDMXc&usp=sharing
 *  examples: 
 *      User click on lecture 1 in the sunburst: reportAction.php?usr=dguerra&grp=20132isd&sid=AAAA&actionsrc=sunburst_model&actiontype=display_content&docsrc=lecture&docno=lecture-1&filename=&result=&comment=
 *      User click on one reading in the contents of lecture 1: reportAction.php?usr=dguerra&grp=20132isd&sid=AAAA&actionsrc=sunburst_model&actiontype=pageload&docsrc=lamming&docno=lamming-0001&filename=00000001.jpg&result=&comment=
 *      User displays the table of content: reportAction.php?usr=dguerra&grp=20132isd&sid=AAAA&actionsrc=tab_menu&actiontype=change_to_toc&docsrc=&docno=&filename=&result=&comment=
 *      User click on a document from the TOC: reportAction.php?usr=dguerra&grp=20132isd&sid=AAAA&actionsrc=toc&actiontype=pageload&docsrc=lamming&docno=lamming-0002&filename=00000002.jpg&result=&comment=
 **/

$usr = $_GET["usr"];
$grp = $_GET["grp"];
$sid = $_GET["sid"];
//$course = $_GET["course"];
$actionsrc = $_GET["actionsrc"];
$actiontype = $_GET["actiontype"];
$docsrc = $_GET["docsrc"];
$docno = $_GET["docno"];
$filename = $_GET["filename"];
$result = $_GET["result"];
$comment = $_GET["comment"];

include("config.php");
include("dbFunctions.php");

$id = insertTracking($usr, $grp, $sid, $actionsrc, $actiontype, $docsrc, $docno, $filename, $result, $comment);
echo $id;
?>