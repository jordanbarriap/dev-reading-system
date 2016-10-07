<?php
/**
  * updateModels
  *     update model of all students from grp checking if there are recent actions
  * parameters: 
  *     grp
  * Response: 
  *   JSON array
  *   -1 : error, the group is not defined for the eReader
  *   -2 : 
  *   -3 : 
  *   -4 : 
  *
  * @author Julio Guerra (PAWS group, iSchool, University of Pittsburgh)
  **/

$grp = $_GET["grp"];


include("config.php");
include("dbFunctions.php");
include("userFunctions.php");


$jsongroup = getGroupListFromUM($grp);
// mode == model means it will retrieve the users with their last computed models

$courseinfo = getCourseInfo($grp);
$courseid = $courseinfo["courseid"];
$domain = $courseinfo["domain"];

// GET LAST MODELS FORM PRECOMPUTED MODELS TABLE
$_last_models = getLastModels($grp, $courseid, $domain);

// PROCESS THE JSON FROM UM (CONTANING THE WHOLE CLASS LIST)    
$_group_list = json_decode($jsongroup,true);
$_users = $_group_list["users"];
$output_report1 = "Updated models: ";
$output_report2 = "(";
$count_inserts = 0;
foreach($_users as $key => $user){
    $userlogin = $user["login"];
    // look for the user last model
    if (!isset($_last_models[$userlogin]) || intval($_last_models[$userlogin]["needsupdate"]) == 1){
        // THERE IS NO PRECOMPUTED MODEL FOR THE USER
        $user_model = computeModel($userlogin, $grp);
        $user_model = substr($user_model,0,-1);
        $insertid = storeModel($userlogin, $courseid, $user_model);
        $output_report2 .= $userlogin.",";
        //echo $insertid."<br />";
        $count_inserts++;
        //$user_model = " :( ";
    }else{
        $user_model = $_last_models[$userlogin]["progress"];
        //$needsupdate = intval($_last_models[$userlogin]["needsupdate"]);
        //if ($needsupdate == 1) $user_model = computeModel($usr, $grp);
    }
    //echo $user["login"]." ".$user_model."<br />";
    //$user["model"] = "[the user model]";
}
$output_report1 .= $count_inserts;

echo $output_report1."<br />";
if ($count_inserts>0) echo substr($output_report2,0,-1).")";
   // var_dump($_users);

?>