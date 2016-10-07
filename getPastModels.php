<?php
error_reporting(E_ERROR);
/**
  * getPastModels.php
  * parameters: 
  *     usr
  *     grp
  *     weeks (how many weeks back)
  * Response: 
  *   JSON array
  *
  * @author Julio Guerra (PAWS group, iSchool, University of Pittsburgh)
  **/

$usr = $_GET["usr"];
$grp = $_GET["grp"];
$weeks = $_GET["weeks"];


include("config.php");
include("dbFunctions.php");
include("userFunctions.php");

header('Content-Type: application/json');

$courseinfo = getCourseInfo($grp);
$courseid = $courseinfo["courseid"];
$domain = $courseinfo["domain"];
    
$_past_models = getPastModels($usr, $courseid, $weeks);

$_json_output = "{\"user\":\"".$usr."\", \"group\":\"".$grp."\", \"models\": [ ";
  
foreach($_past_models as $key => $_model){
    $_json_output .= "{\"built\":\"".$_model["computedon"]."\", \"progress\": [";
//    $_model["progress"]
    $_progress = split("\|",$_model["progress"]);
    foreach($_progress as $doc_progress){
        $_values = split(";",$doc_progress);
        $_json_output .= "{\"docno\" : \"".$_values[0]."\", \"uprogress\" : \"".$_values[1]."\", \"uconfidence\" : \"".$_values[2]."\"},";
    }
    $_json_output = substr($_json_output,0,-1);
    $_json_output .= "]},";
    //$user["model"] = "[the user model]";
}
$_json_output = substr($_json_output,0,-1);
$_json_output .= "]}";

echo $_json_output;

?>