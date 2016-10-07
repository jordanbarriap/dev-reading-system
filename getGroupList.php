<?php
error_reporting(E_ERROR);
/**
  * getGroupList
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

//$_filterusers = array("unknown","peterb","dguerra","jennifer","shoha99","dap89");
$_filterusers = array("unknown");

$grp = $_GET["grp"];
$mode = $_GET["mode"];


include("config.php");
include("dbFunctions.php");
include("userFunctions.php");

header('Content-Type: application/json');

$jsonoutput = getGroupListFromUM($grp);
// mode == model means it will retrieve the users with their last computed models
if ($mode == "model"){
    $courseinfo = getCourseInfo($grp);
    $courseid = $courseinfo["courseid"];
    $domain = $courseinfo["domain"];
    $_group_list = json_decode($jsonoutput,true);
    $_users = &$_group_list["users"];
    
    $_last_models = getLastModels($grp, $courseid, $domain);
    //var_dump($_last_models);
    foreach($_users as $key => &$user){
        $login = $user["login"];
            if (isset($_last_models[$login])){
                $strprogress = $_last_models[$login]["progress"];
                $_progress = split("\|",$strprogress);
                //var_dump($_progress);
                //echo "<br />";
                $_P = array();
                $_n_docs = 0;
                $sum_progress = 0.0;
                foreach($_progress as $doc_progress){
                    $_values = split(";",$doc_progress);
                    $_P[] = array("docno" => $_values[0], "uprogress" => $_values[1], "uconfidence" => $_values[2]);
                    $sum_progress += floatval($_values[1]);
                    $_n_docs++;
                    
                }            
                $user["avg_progress"] = round($sum_progress/$_n_docs,3);
                $user["progress"] = $_P;
            }else{
                $user["avg_progress"] = 0.0;
                $user["progress"] = array();
            }
        
        //$user["model"] = "[the user model]";
    }
    removeNonSudents($_group_list["users"], $_filterusers);
    sortListByProgress($_group_list["users"]);
    
    //var_dump($_group_list["users"]);
    echo json_encode($_group_list);

}else{
    echo $jsonoutput;
}




function sortListByProgress(&$_users){
    $n = count($_users);
    //echo $n;
    for($i=0;$i<$n;$i++){
        //var_dump($_users[$i]);
        //echo $_users[$i]["avg_progress"]."\n";
        for($j=0;$j<$n-1;$j++){
            if ($_users[$j]["avg_progress"] < $_users[$j+1]["avg_progress"]){
                $_tmp = $_users[$j];
                $_users[$j] = $_users[$j+1];
                $_users[$j+1] = $_tmp;
            }
        }
    }
    
    /*for($i=0;$i<$n;$i++){
        echo $_users[$i]["login"]." : ".$_users[$i]["avg_progress"]."\n";
    }*/
    
}

function removeNonSudents(&$_users, $_filterusers){
    
    $n = count($_users);
    //echo $n;
    for($i=0;$i<$n;$i++){
        //var_dump($_users[$i]);
        
        if (array_search($_users[$i]["login"], $_filterusers)) {
            //echo $_users[$i]["login"]."<br />";
            unset($_users[$i]);
        }
    }
}


?>