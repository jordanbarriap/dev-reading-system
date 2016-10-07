<?php
/**
  * userFunctions
  *
  *
  * @author Julio Guerra (PAWS group, iSchool, University of Pittsburgh)
  **/

/*
MAIN FUNCTION
*/
function computeUsrProgress($usr, $grp){
    // --------------------------------------------------------------------------------------------------------
    // 1) GET COURSE INFORMATION
    // --------------------------------------------------------------------------------------------------------
    $_courseinfo = getCourseInfo($grp);
    
    if(count($_courseinfo)==0){
        die("-1"); 
    }
    //var_dump($_courseinfo);
    //echo "<br /><br />";
    $course = $_courseinfo["coursekey"];
    $domain = $_courseinfo["domain"];
    
    // --------------------------------------------------------------------------------------------------------
    // 2) GET ALL USER ACTIVITY BY DOCNO
    // --------------------------------------------------------------------------------------------------------
    // 2.1) ACTIVITY FROM UM
    $_ACT_FROM_UM = getActivityFromUM($usr,$grp,$domain);
    //var_dump($_ACT_FROM_UM);
    //echo "<br />";
    //echo "<br />";
    
    // 2.2) ACTIVITY FROM THE READER APP
    $_ACT_FROM_READER = getUserActivityFromReader($usr,$grp,$course); // should give docno, npages, different pages visited, total visited
    //var_dump($_ACT_FROM_READER);
    //echo "<br />";
    //echo "<br />";
    
    // 2.2) ACTIVITY FROM THE OLD READER APP
    $_ACT_FROM_OLDREADER = array();
    //$_ACT_FROM_OLDREADER = getUserActivityFromOldReader($usr,$grp,$domain); // should give docno, npages, different pages visited, total visited
    //var_dump($_ACT_FROM_READER);
    //echo "<br />";
    //echo "<br />";
    
    // --------------------------------------------------------------------------------------------------------
    // 3) GET THE HIERARCHICAL STRUCTURE OF THE COURSE FROM THE JSON FILE AND FILL PROGRESS
    // --------------------------------------------------------------------------------------------------------
    $filestr = file_get_contents("data/".$course.".json");
    //echo "data/".$course.".json";
    $json_course = json_decode($filestr,true);
    //var_dump($json_course);
    $_lectures = $json_course["children"];
    //var_dump($json_course);
    
    $ucourse = array();
    foreach($_lectures as $lec){
        //$_docs = array();
        $_docs = getChildren($lec, $_ACT_FROM_UM, $_ACT_FROM_READER, $_ACT_FROM_OLDREADER);
        $ucourse[$lec["docno"]] = array("progress"=>0.0, "confidence"=>0.0, "docsrc"=>"", "files"=>"", "docs"=>$_docs);
        
    }
    //var_dump($ucourse); // shows the array
    
    // --------------------------------------------------------------------------------------------------------
    // 4) AGGREGATE USER PROGRESS AMONG THE NODES
    // --------------------------------------------------------------------------------------------------------
    foreach($ucourse as $key => &$lec){
        //$_docs = array();
        //echo $key."<br />";
        //var_dump($lec);
        //echo "<br />";
        //print_r($lec);
        $lecnchildren = count($lec["docs"]);
        $factor = ($lecnchildren + 1)/$lecnchildren;
        $lecprogress = aggregateProgress($lec);
        $lec["progress"] = $lecprogress[0]*$factor;
        //$lec["confidence"] = $lecprogress[1]*$factor;
        //echo " TOT : ".$lec["progress"]."<br /><br />";
       // $ucourse[$lec["docno"]] = array("progress"=>0.0, "confidence"=>0.0, "docsrc"=>"", "files"=>"", "docs"=>$_docs);
        
    }
    unset($lec);

    
    // --------------------------------------------------------------------------------------------------------
    // 5) GENERATE JSON RESPONSE
    // --------------------------------------------------------------------------------------------------------
    

    return $ucourse;

}


/*
recursive function that fills a tree with the structure containing only docno and uprogress
*/
function getChildren($_doc, $_ACT_FROM_UM, $_ACT_FROM_READER, $_ACT_FROM_OLDREADER){
    $_output = array();
    foreach($_doc["children"] as $child){
        $_docs = array();
        $_docfiles = array();
        if (is_array($child["links"]) && count($child["links"]) > 0){
            foreach($child["links"] as $strlink){
                array_push($_docfiles, substr($strlink["url"],-12));
            }
        }
        if (is_array($child["children"]) && count($child["children"]) > 0){
            $_docs = getChildren($child, $_ACT_FROM_UM, $_ACT_FROM_READER, $_ACT_FROM_OLDREADER);
        }
        $docno = $child["docno"];
        $progress = computeProgress($docno, $_ACT_FROM_UM, $_ACT_FROM_READER, $_ACT_FROM_OLDREADER);
        
        $_output[$docno] = array("progress"=>$progress[0], "confidence"=>$progress[1], "docsrc"=>$child["bookid"], "files"=>$_docfiles, "docs"=>$_docs);
    }
    
    return $_output;
}

/*
recursive function that aggregates progress and confidence levels from children to parents
*/
function aggregateProgress(&$_doc){
    $aggprogress = 1.0*$_doc["progress"];
    $aggconfidence = 1.0*$_doc["confidence"];
    $aggconfidencedeno = $aggprogress;
    $children = &$_doc["docs"]; // JA JA, the fucking &amp solve the recursion problem!!
    if (is_array($children) && count($children) > 0){
        $nchildren = count($children);
        //echo " ".$nchildren;
        $confcontributors = 0;
        foreach($children as $key => &$child){
            //echo $key." : ".$child["progress"]."  CHILDREN: ".count($child["docs"])."<br />";
            $childprog = aggregateProgress($child);
            $aggprogress += $childprog[0];
            if ($childprog[0]>0){
                $aggconfidence += $childprog[0]*$childprog[1];
                $aggconfidencedeno += $childprog[0];
                $confcontributors++;
            }
            
            //$child["progress"] = $aggprogress;
            //echo $key." : ".$child["progress"]."  CHILDREN: ".count($child["docs"])."<br />";
        }
        unset($child);
        $aggprogress = 1.0*$aggprogress / ($nchildren + 1);
        if ($aggconfidencedeno == 0) $aggconfidencedeno = 1.0;
        $aggconfidence = 1.0*$aggconfidence / ($aggconfidencedeno);
        
    }else{
        
    }
    $_doc["progress"] = $aggprogress;
    $_doc["confidence"] = $aggconfidence;
    return array($aggprogress,$aggconfidence);
}

/*
COMPUTING PROGRESS FOR A DOCNO
*/
function computeProgress($docno, $_ACT_FROM_UM, $_ACT_FROM_READER, $_ACT_FROM_OLDREADER){
    
    $confidence = 0.0;
    $totalhits = 0;
    $coverage = 0.0;
    $clicks = 0;
    $annotations = 0;
    $distinct = 0;
    $npages = 1;
    
    if (isset($_ACT_FROM_UM[$docno])) {
        $totalhits += intval($_ACT_FROM_UM[$docno]["hits"]);
        $npages = intval($_ACT_FROM_UM[$docno]["npages"]);
        $distinct = 1;
    }
    if (isset($_ACT_FROM_READER[$docno]))  {
        $totalhits += $_ACT_FROM_READER[$docno]["pageloads"];
        $distinct = $_ACT_FROM_READER[$docno]["distinctpages"];
        $npages = $_ACT_FROM_READER[$docno]["npages"];
        $clicks = $_ACT_FROM_READER[$docno]["clicks"];
        $annotations = $_ACT_FROM_READER[$docno]["annotations"];
        
    }
    
    if (isset($_ACT_FROM_OLDREADER[$docno]))  {
        $npages = $_ACT_FROM_OLDREADER[$docno]["npages"];
        $clicks += $_ACT_FROM_OLDREADER[$docno]["clicks"];
        $annotations += $_ACT_FROM_OLDREADER[$docno]["annotations"];    
    } 
    
    $coverage = 1.0 * $distinct / $npages;
    
    $loadrate = $totalhits / $npages;
    $actionrate = ($clicks + $annotations) / $npages;
    
    $loadconf = 0.0;
    $actionconf = 0.0;
    
    if ($loadrate > 0) $loadconf = 0.1;
    if ($loadrate > 0.5) $loadconf = 0.25;
    if ($loadrate > 1) $loadconf = 0.5;
    if ($loadrate > 2) $loadconf = 1;

    if ($actionrate > 0) $actionconf = 0.1;
    if ($actionrate > 0.5) $actionconf = 0.25;
    if ($actionrate > 1) $actionconf = 0.5;
    if ($actionrate > 2) $actionconf = 1;
    
    $confidence = ($loadconf + $actionconf) / 2;
    if ($coverage>1.0) $coverage = 1.0;
    
    return array($coverage,$confidence);

}

/*

*/
function getUserActivityFromReader($usr,$grp,$domain){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $result = array();
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    
    $sql = "SELECT max(T.actiondate), T.docno, T.docsrc, ";
	$sql .= "    sum(if(T.actiontype='pageload',1,0)) as totalpageloads, ";
	$sql .= "    sum(if(T.actiontype='click',1,0)) as totalclicks, ";
	$sql .= "    sum(if(T.actiontype='annotation',1,0)) as totalannotations, ";
	$sql .= "    count(distinct(T.filename)) distinctpages, (D.epage-D.spage+1) as doclength, D.filenames FROM tracking T, document D ";
    $sql .= " WHERE T.uid='".$usr."' and T.domain='".$domain."' and D.docno=T.docno ";
    $sql .= "    and T.actiontype in ('pageload', 'click','annotation') group by T.docno;";
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row=mysqli_fetch_array($res)){
                
                    $result[$row["docno"]] = array(
                        "pageloads"     => intval($row["totalpageloads"]),
                        "clicks"        => intval($row["totalclicks"]),
                        "annotations"   => intval($row["totalannotations"]),
                        "distinctpages" => intval($row["distinctpages"]),
                        "npages"        => intval($row["doclength"])

                    );
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
    return $result;
}


function getUserLastDocViewed($usr,$grp,$domain){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $result = null;
    //echo "hello - func";
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    
    $sql = "SELECT T.docno, T.actiondate, T.docsrc, T.filename FROM tracking T ";
    $sql .= " WHERE T.uid='".$usr."' and T.grp='".$grp."' and T.domain='".$domain."' ";
    $sql .= "    ORDER BY actiondate DESC limit 1;";
//    echo $sql;
    
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row=mysqli_fetch_array($res)){
                
                    $result = array(
                        "docno"      => $row["docno"],
                        "actiondate" => $row["actiondate"],
                        "docsrc"     => $row["docsrc"],
                        "filename"   => $row["filename"]
                    );
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
    return $result;
}


/*

*/
function getUserActivityFromOldReader($usr,$grp,$domain){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort, $config_kseadbs;
    $dbname = $config_kseadbs[$domain];
    
    $sql = "SELECT L.docid, ";
    $sql .= "	sum(if(L.action='annotation',1,0)) AS totalannotations, ";
    $sql .= "	sum(if(L.action='click',1,0)) AS totalclicks ";
    $sql .= "FROM logAT L ";
    $sql .= "WHERE L.userid='".$usr."' GROUP BY L.docid;";

    $docidset = "";
    $byid = array();
    $byno = array(); // THIS IS THE IMPORTANT
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $dbname, $config_dbPort);
    $first = true;
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row=mysqli_fetch_array($res)){
                    if(!$first){
                        $docidset .= ",";
                    }else $first = false;
                    $docidset .= $row["docid"];
                    
                    $byid[$row["docid"]] = array(
                        "clicks"        => intval($row["totalclicks"]),
                        "annotations"   => intval($row["totalannotations"])
                    );
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }else{
        
    }
    
    //var_dump($byid);
    //echo "<br /><br />";
    
    //  GET THE DOCNOs
    $sql2 = "SELECT docid,docsrc,docno, (epage-spage+1) as npages FROM document WHERE docid in (".$docidset.");";
    $connection2 = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    
    
    if ($connection2){
        if($res2 = mysqli_query($connection2, $sql2)){
            if(mysqli_num_rows($res2) > 0){
                $i = 0;
                while($row2=mysqli_fetch_array($res2)){
                    $rec = $byid[$row2["docid"]];
                    $annotations = $rec["annotations"];
                    $clicks = $rec["clicks"];
                    $npages = intval($row2["npages"]);
                    $byno[$row2["docno"]] = array("annotations" => intval($annotations), "clicks" => intval($clicks), "npages" => $npages);
                    $i++;
                }
            }
            mysqli_free_result($res2);
        }

        dbDisconnectMySQL($connection2);
    }
    //var_dump($byno);
    //echo "<br /><br />";
    
    return $byno;
}


/*

*/
function getCourseInfo($grp){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $result = array();
    $sql = "SELECT C.courseid, C.coursekey, C.domain, C.title, C.legacy_kseadb FROM course C, groups G where G.grp='".$grp."' and G.courseid=C.courseid;";
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row=mysqli_fetch_array($res)){
                    $result["courseid"] = $row["courseid"];
                    $result["coursekey"] = $row["coursekey"];
                    $result["domain"] = $row["domain"];
                    $result["title"] = $row["title"];
                    $result["legacy_kseadb"] = $row["legacy_kseadb"];
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
    return $result;
}


/*

*/
function getActivityFromUM($usr,$grp,$domain){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    
    // GET THE JSON FROM UM
    $filestr = file_get_contents("http://adapt2.sis.pitt.edu/eRaederUMInterface/GetUserActivity?usr=".$usr."&grp=".$grp."&domain=".$domain);
    $json_umactivity = json_decode($filestr,true);
    //var_dump($json_umactivity);
    $_umactivity = $json_umactivity["activity"];
    //var_dump($_umactivity);
    $docidset = "";
    $docsetbyids = array(); // This is just for storing the progress got from um in a relational array indexed by docid (easy to get a specific docid)
    $docsetbyno = array(); // THIS IS THE IMPORTANT ONE
    
    $first = true;
    foreach($_umactivity as $_docact){
        if(!$first){
            $docidset .= ",";
            
        }else $first = false;
        $docidset .= $_docact["docid"];
        $docsetbyids[$_docact["docid"]] = $_docact["count"];
        
    }
    //echo "<br />".$docidset;
    //echo "<br />";
    //var_dump($docsetbyids);
    
    //  GET THE DOCNOs
    $sql = "SELECT docid,docsrc,docno, (epage-spage+1) as npages FROM document WHERE docid in (".$docidset.");";
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                $i = 0;
                while($row=mysqli_fetch_array($res)){
                    $umhits = $docsetbyids[$row["docid"]];
                    $npages = intval($row["npages"]);
                    $docsetbyno[$row["docno"]] = array("hits" => intval($umhits), "npages" => $npages);
                    $i++;
                }
            }
            mysqli_free_result($res);
        }

        dbDisconnectMySQL($connection);
    }
    
    return $docsetbyno;
}


function generateJSON($usr, $grp, $_progress, $alldocs){
    $output = "{\"user\":\"".$usr."\", \"group\":\"".$grp."\", \"progress\": [ ";
    
    foreach($_progress as $key => $_lec){
        if ($alldocs){
            fillJSON($output, $_lec, $key);
        }else{
            $output .= "{\"docno\":\"".$key."\", \"uprogress\":\"".$_lec["progress"]."\" , \"uconfidence\":\"".$_lec["confidence"]."\"},";
        }
    }
    
    $output = substr($output,0,-1);
    $output .= " ]}";
    return $output;
}

function fillJSON(&$output, $_doc, $docno){
    
    $output .= "{\"docno\":\"".$docno."\", \"uprogress\":\"".$_doc["progress"]."\" , \"uconfidence\":\"".$_doc["confidence"]."\"},";
    $children = $_doc["docs"];
    if (is_array($children) && count($children) > 0){
        foreach($children as $key => $child){
            fillJSON($output, $child, $key);
        }
    }
}

/*

*/
function getLastModels($grp, $courseid, $domain){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $result = array();
    
    $sql = "SELECT PM.modelid, PM.uid, PM.computedon, PM.progress, ";
    $sql .= " if((select max(actiondate) from tracking where uid=PM.uid and domain='".$domain."')>PM.computedon,1,0) as needsupdate  ";
	$sql .= "    FROM precomputed_models PM ";
	$sql .= "    WHERE PM.modelid=(SELECT max(PM2.modelid) FROM precomputed_models PM2 WHERE PM2.uid = PM.uid AND PM2.courseid=".$courseid.");";
    
    
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row=mysqli_fetch_array($res)){
                
                    $result[$row["uid"]] = array(
                        "modelid"       => intval($row["modelid"]),
                        "progress"      => $row["progress"],
                        "needsupdate"   => intval($row["needsupdate"]),
                        "computedon"   => $row["computedon"]
                    );
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
      
    return $result;
}

/*
(SELECT PM.modelid, PM.uid, PM.computedon, PM.progress FROM precomputed_models PM
WHERE PM.uid='dguerra' AND  PM.courseid=1 
	AND (PM.computedon > date_sub(now(),interval 7 day))
ORDER BY PM.computedon DESC LIMIT 0,1)
UNION
(SELECT PM.modelid, PM.uid, PM.computedon, PM.progress FROM precomputed_models PM
WHERE PM.uid='dguerra' AND  PM.courseid=1 
	AND (PM.computedon < date_sub(now(),interval 7 day) AND PM.computedon > date_sub(now(),interval 14 day))
ORDER BY PM.computedon DESC LIMIT 0,1)
*/
function getPastModels($usr, $courseid, $weeks){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $result = array();
    
    $sql = "(SELECT PM.modelid, PM.uid, PM.computedon, PM.progress FROM precomputed_models PM WHERE PM.uid='".$usr."' AND  PM.courseid=".$courseid." ";
    $sql .= " AND (PM.computedon > date_sub(now(),interval 7 day)) ";
	$sql .= " ORDER BY PM.computedon ASC LIMIT 0,1) ";
    
    for($i=1;$i<$weeks;$i++){
        $b1 = $i*7;
        $b2 = $b1+7;
        $sql .= " UNION ";
        $sql .= "(SELECT PM.modelid, PM.uid, PM.computedon, PM.progress FROM precomputed_models PM WHERE PM.uid='".$usr."' AND  PM.courseid=".$courseid." ";
        $sql .= " AND (PM.computedon < date_sub(now(),interval ".$b1." day) AND PM.computedon > date_sub(now(),interval ".$b2." day)) ";
	    $sql .= " ORDER BY PM.computedon ASC LIMIT 0,1) ";
    }
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    
    //echo $weeks."<br />".$sql."<br />";
    
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row=mysqli_fetch_array($res)){
                
                    $result[] = array(
                        "modelid"       => intval($row["modelid"]),
                        "progress"      => $row["progress"],
                        "computedon"   => $row["computedon"]
                    );
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
      
    return $result;
}



/*

*/
function getGroupListFromUM($grp){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    
    // GET THE JSON FROM UM
    $response = file_get_contents("http://adapt2.sis.pitt.edu/eRaederUMInterface/GetGroupList?grp=".$grp);
    
    return $response;
}


/*

*/
function computeModel($usr, $grp){
    $output = "";
    $_model = computeUsrProgress($usr, $grp);
    foreach($_model as $key => $_lec){
        $output .= $key.";".round($_lec["progress"],2).";".round($_lec["confidence"],2)."|";
    }
    unset($_model);
    return $output;
}

function storeModel($usr, $courseid, $progress){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $sql =  "INSERT INTO precomputed_models (uid,sid,courseid,computedon,progress) ";
    $sql .= " values ('".$usr."','UNKNOWN',".$courseid.",now(),'".$progress."');";
    //echo $sql."<br />";
    $id = 0;
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    if ($connection){
        mysqli_query($connection, $sql);
        $id = mysqli_insert_id($connection);
        dbDisconnectMySQL($connection);
    }
    
    return $id;
}
?>