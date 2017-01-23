<?php

include("../config.php");
include("../dbFunctions.php");

$task = $_GET["task"];


// for debugging

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getSortedUniqueArray($elements){
  $unique_array = array_unique($elements);
  sort($unique_array);
  return $unique_array;
}

if ($task == "status") {
	// this now requires POST requests so you can use the same interface for multiple
	// status requests (including those that that have to send lots of docids, and thus
	// require a POST
	
	// return 0 if last answer was correct,
	//        1 if last answer was wrong,
	//        2 if unanswered,
	//        3 if there is no question
	
	$json = file_get_contents('php://input');
	$obj = json_decode($json, true);
	
	$grp = $obj["grp"];
	$usr = $obj["usr"];
	$docids = $obj["docids"];
	$filename = $obj["filename"];
	
	$arr = array();
	
	//foreach ($docids as $docid) {
		$status = 2;
		$questionIds = filenameToQuestionIds($filename);//docidToQuestionIds($docid);
		
		if (count($questionIds) == 0) {
			$status = 3;
		} else {
			$lastStatuses = array();
			foreach ($questionIds as $id) {
				array_push($lastStatuses, getLastAnswerStatus($usr, $grp, $id));
			}
			if (in_array(1, $lastStatuses)) {
				$status = 1;
			} else {
				// if we got here, all questions are either correct or unanswered
					
				if (!in_array(2, $lastStatuses)) {
					// if all correct return correct
					$status = 0;
				} else if (in_array(0, $lastStatuses)) {
					// if we have both correct and unanswered, return incorrect
					$status = 1;
				} else {
					// if all unanswered, return unanswered
					$status = 2;
				}
			}
		}
		$arr[$docid] = $status;
	//}//commented by jbarriapienda in 10-23
	
	echo(json_encode($arr));
} else if ($task == "questions") {
	$docid = $_GET["docid"];
	$filename = $_GET["filename"];//added by jbarriapineda in 10-23
	$usr = $_GET["usr"];//added by jbarriapineda in 01-22
	$grp = $_GET["grp"];//added by jbarriapineda in 01-22

	$questions = array();
	
	$questionIds = filenameToQuestionIds($filename);//docidToQuestionIds($docid);//commented by jbarriapineda in 10-23
	
	foreach ($questionIds as $id) {
		$questionText = getQuestion($id);
		$answers = getAnswers($id);
		$n_attempts = getNumberOfAttempts($usr,$grp,$id);//added by jbarriapineda in 01-22
		$question = array("question" => $questionText, "answers" => $answers, "n_attempts"=>$n_attempts);//added by jbarriapineda in 01-22
		array_push($questions, $question);
	}
	
	echo(json_encode($questions));
} else if ($task == "lastanswer") {
	$docid = $_GET["docid"];
	$filename = $_GET["filename"];//added by jbarriapineda in 10-23
	$usr = $_GET["usr"];
	$grp = $_GET["grp"];
	// returns the last answer of a user for a question
	$lasts = array();

	$questionIds = filenameToQuestionIds($filename);//docidToQuestionIds($docid);//commented by jbarriapineda in 10-23
	foreach ($questionIds as $id) {
		$last = getLastAnswer($usr, $grp, $id);
		
		array_push($lasts, $last);
	}	
	
	echo(json_encode($lasts));
	
} else if ($task == "submit") {
	// checks an answer, and increments the counter
	
	// unlike the other endpoints, this comes as a POST request.
	// data is in json
	$json = file_get_contents('php://input');
	$obj = json_decode($json, true);
	
	$docid = $obj["docid"];
	$filename = $obj["filename"];
	$grp = $obj["grp"];
	$usr = $obj["usr"];
	$sid = $obj["sid"];
	$answers = $obj["answers"];
	
	$questionIds = filenameToQuestionIds($filename);//docidToQuestionIds($docid);//commented by jbarriapineda in 10-23
	
	$correctAnswers = array();
	foreach ($questionIds as $id) {
		array_push($correctAnswers, getCorrectAnswerIndices($id));
	}
	
	$status = 2;
	
	$count = 0;
	foreach ($answers as $array) {
		$count += count($array);
	}
	
	$incorrectIndices = array();
	
	// only consider when answer submitted? (if so, change >= to >)
	if ($count > 0) {
		for ($i = 0; $i < count($correctAnswers); $i++) {
			$correct = true;
			if ($answers[$i] != $correctAnswers[$i]) {
				$correct = false;
				array_push($incorrectIndices, $i);
			}
			$id = $questionIds[$i];
			insertAnswer($usr, $grp, $sid, $id, json_encode($answers[$i]), $correct);
		}
		
	}/*else{
		$status = 2;
	}*/
	if (count($incorrectIndices) == 0) {
		$status = 0;
	} else {
		$status = 1;
	}
	
	// TODO: return status the same way it is for the status API (0 is correct, etc.)
	$arr = array('status' => $status, 'incorrect' => $incorrectIndices);
	echo(json_encode($arr));
} else if ($task == "samequestions") {
	// given some docid, return the other docids that share the same question
	
	$docid = $_GET["docid"];
	$docids = docidToDocids($docid);
	
	echo(json_encode($docids));
} else if ($task == "subsectionstatus") {
	// this now requires POST requests so you can use the same interface for multiple
	// status requests (including those that that have to send lots of docids, and thus
	// require a POST
	
	// return 0 if last answer was correct,
	//        1 if last answer was wrong,
	//        2 if unanswered,
	//        3 if there is no question
	//        4 if a user does not read more than 80% of the required documents.
	
	$json = file_get_contents('php://input');
	$obj = json_decode($json, true);
	
	$grp = $obj["grp"];
	$usr = $obj["usr"];
	$docids = $obj["docids"];
	
	$arr = array();
	$success_rate_arr=getSuccessRate($usr,$grp);//added by jbarriapineda in 11-13
	$group_success_rate_arr=getGroupSuccessRate($usr,$grp);//added by jbarriapineda in 01-07

	// Concatenates all document ids that need to be queries from database.
	$all_docnos = "(";
	$all_docids = "(";
	$docno_to_ids = array();
	for ($i = 0; $i < count($docids); ++$i) {
       foreach(explode(",", $docids[$i]) as $docid_each) {
	    list($docid, $docno) = explode('@', $docid_each);
		$docno_to_ids[$docno] = $docid;
	    $all_docnos = $all_docnos."'".$docno."',";
		$all_docids = $all_docids."'".$docid."',";
		$docid_nquestions=0;
		$docid_questions_array = docidToQuestionIds2($docid);
		if($docid_questions_array){
			$docid_nquestions=sizeof($docid_questions_array);
		}
		if(!array_key_exists ( $docid, $success_rate_arr )){
			if($docid_nquestions>0){
				$success_rate_arr[$docid]=0;
			}else{
				$success_rate_arr[$docid]=-1;
			}
			
		}/*else{
			$success_rate_arr[$docid]=$success_rate_arr[$docid];
		}*/
		if(!array_key_exists ( $docid, $group_success_rate_arr )){
			if($docid_nquestions>0){
				$group_success_rate_arr[$docid]=0;
			}else{
				$group_success_rate_arr[$docid]=-1;
			}
		}/*else{
			$group_success_rate_arr[$docid]=$group_success_rate_arr[$docid];
		}*/
	  } 
    }
	$all_docnos = $all_docnos."'-1')";
	$all_docids = $all_docids."'-1')";
	
	// Queries database.
	$docid_total_pages = getTotalPageForDocs($all_docnos);
	$docid_read_pages = getTotalPageReadForDocs2($usr, $grp, $all_docnos);//modified by jbarriapineda in 10-23
	//print_r($docid_read_pages);//added by jbarriapineda in 10-23
	$docid_questionids = getTotalQuestionIdsForDocs($all_docids);
	//print_r($docid_questionids);//added by jbarriapineda in 10-23

	foreach ($docids as $docids_each) {
		$status = 2;
		
		$docid_key = "";
		$docid = explode(",", $docids_each);
		$questionIds = array();
		$percentage_per_page = 0.0;
		$total_docs = 0;
		foreach($docid as $docid_each_raw) {
		  list($docid_each, $docno_each) = explode('@', $docid_each_raw);
		  $docid_key = $docid_key.$docid_each.",";
		  $page_for_read = 0;
		  $page_read = 0;
		  if(array_key_exists($docid_each, $docid_questionids)) {
		    $questionIds = array_merge($questionIds, explode(',', $docid_questionids[$docid_each]));
		  }
		  if(array_key_exists($docid_each, $docid_total_pages)) {
		    $page_for_read = $docid_total_pages[$docid_each];
		  }
		  if(array_key_exists($docno_each, $docid_read_pages)) {
		    $page_read = $docid_read_pages[$docno_each];
		  }		  
		  $percentage_per_page = $page_for_read > 0 ? $percentage_per_page + $page_read / ($page_for_read + 0.0) : 0.0;
		  $total_docs = $total_docs + 1;
		  //echo $docid_each."-".$page_for_read."-".$page_read."-".count($questionIds)."\n";
		}
		//echo($docids_each." ");
		//echo($percentage_per_page / $total_docs);
		//echo("\n");
		if($total_docs > 0 and ($percentage_per_page / $total_docs < 0.8) ) {
		    $status = 4;
		} else if (count($questionIds) == 0) {
			$status = 3;
		} else {
			$lastStatuses = array();
			foreach ($questionIds as $id) {
				array_push($lastStatuses, getLastAnswerStatus($usr, $grp, $id));
			}
			if (in_array(1, $lastStatuses)) {
				$status = 1;
			} else {
				// if we got here, all questions are either correct or unanswered
					
				if (!in_array(2, $lastStatuses)) {
					// if all correct return correct
					$status = 0;
				} else if (in_array(0, $lastStatuses)) {
					// if we have both correct and unanswered, return incorrect
					$status = 1;
				} else {
					// if all unanswered, return unanswered
					$status = 2;
				}
			}
		}
		$docid_key = ($docid_key == "") ? $docid_key : str_replace(",-1", "", $docid_key."-1");
		$arr[$docid_key] = $status;
	}
	$final_arr=array('status' => $arr, 'success_rate' => $success_rate_arr, 'group_success_rate'=> $group_success_rate_arr);
	echo(json_encode($final_arr));
} else if ($task == "subsectionquestions") {
	$usr=$_GET["usr"];//added by jbarriapineda in 01-06
	$grp=$_GET["grp"];//added by jbarriapineda in 01-06
	$docid = $_GET["docid"];//added by jbarriapineda in 11-03
	$subdocids = $_GET["subdocids"];//added by jbarriapineda in 11-03
	$docid_array = explode(",", $_GET["docids"]);
	$questionIds = array();
	$questions = array();
	$questionmode = $_GET["questionmode"];//added by jbarriapineda in 11-03
	$filename = $_GET["filename"];//added by jbarriapineda in 10-23
	
	//foreach($docid_array as $docid){
	if($questionmode=="page"){//if the user reaches the end of a end-of-section page we show all the questions that can be finished by reading that page till the end
		$questionIds = array_merge($questionIds, filenameToQuestionIds($filename));//docidToQuestionIds($docid)); //modified by jbarriapineda in 10-23
	}
	if($questionmode=="section"){//if a user click a question mark icon from the index, we just have to show the questions related to that section
		$subdocids_array=explode(",",$subdocids);
		//if(count($subdocids_array)>0){
		for($i=0;$i<count($subdocids_array);$i++){
			$subdocid=$subdocids_array[$i];
			$questionIds = array_merge($questionIds, docidToQuestionIds2($subdocid)); //added by jbarriapineda in 10-23
		}
		//}
		
	}
	//}
	$questionIds = getSortedUniqueArray($questionIds);
	
	foreach ($questionIds as $id) {
		$questionText = getQuestion($id);
		$answers = getAnswers($id);
		$correct = getLastAnswerStatus($usr,$grp,$id);
		$n_attempts = getNumberOfAttempts($usr,$grp,$id);//added by jbarriapineda in 01-22
		//$correctAnswers = rand(0, 5);//added by jbarriapineda in 11-13
		//$totalAnswers = rand(5, 10);//added by jbarriapineda in 11-13
		//$question = array("question" => $questionText, "answers" => $answers, "corrects"=>$correctAnswers, "total"=>$totalAnswers);//added by jbarriapineda in 11-13
		$question = array("question" => $questionText, "answers" => $answers, "correct"=>$correct, "n_attempts"=>$n_attempts);//added by jbarriapineda in 11-13, modified in 01-22
		array_push($questions, $question);
	}
	
	echo(json_encode($questions));
} else if ($task == "subsectionsubmit") {
	// checks an answer, and increments the counter
	
	// unlike the other endpoints, this comes as a POST request.
	// data is in json
	$json = file_get_contents('php://input');
	$obj = json_decode($json, true);
	
	$docid = $obj["docid"];
	$docids = $obj["docids"];
	$grp = $obj["grp"];
	$usr = $obj["usr"];
	$sid = $obj["sid"];
	$answers = $obj["answers"];
	$questionmode = $obj["questionmode"];//added by jbarriapineda in 11-03
	$filename = $obj["filename"];//added by jbarriapineda in 10-23
	$subdocids = $obj["subdocids"];//added by jbarriapineda in 10-23
	
	$docid_array = explode(",", $docids);
	$questionIds = array();
	//foreach($docid_array as $docid) {
	//  $questionIds = array_merge($questionIds, docidToQuestionIds($docid));
	//}

	//foreach($docid_array as $docid){
	if($questionmode=="page"){//if the user reaches the end of a end-of-section page we show all the questions that can be finished by reading that page till the end
		$questionIds = array_merge($questionIds, filenameToQuestionIds($filename));//docidToQuestionIds($docid)); //modified by jbarriapineda in 10-23
	}
	if($questionmode=="section"){//if a user click a question mark icon from the index, we just have to show the questions related to that section
		$subdocids_array=explode(",",$subdocids);
		//if(count($subdocids_array)>0){
		for($i=0;$i<count($subdocids_array);$i++){
			$subdocid=$subdocids_array[$i];
			$questionIds = array_merge($questionIds, docidToQuestionIds2($subdocid)); //added by jbarriapineda in 10-23
		}
		//}
		
	}
	
	$questionIds = getSortedUniqueArray($questionIds);
	//echo "docid ".$docid;
	//print_r($questionIds);
	$correctAnswers = array();
	$n_attempts = array();//added by jbarriapineda in 22-01
	foreach ($questionIds as $id) {
		array_push($correctAnswers, getCorrectAnswerIndices($id));
		array_push($n_attempts, getNumberOfAttempts($usr,$grp,$id));//added by jbarriapineda in 22-01
	}
	
	$status = 2;
	
	$count = 0;
	foreach ($answers as $array) {
		$count += count($array);
	}
	//echo "count ".$count;
	$incorrectIndices = array();
	
	// only consider when answer submitted? (if so, change >= to >)
	if ($count > 0) {
		for ($i = 0; $i < count($correctAnswers); $i++) {
			$correct = true;
			if ($answers[$i] != $correctAnswers[$i]) {
				$correct = false;
				array_push($incorrectIndices, $i);
			}
			$id = $questionIds[$i];
			insertAnswer($usr, $grp, $sid, $id, json_encode($answers[$i]), $correct);
		}
	}
	if ($count == 0){//added by jbarriapineda in 01-06
		$status=2;
	}else{
		if (count($incorrectIndices) == 0) {
			$status = 0;
		} else {
			$status = 1;
		}
	}
	
	
	// TODO: return status the same way it is for the status API (0 is correct, etc.)
	$arr = array('status' => $status, 'incorrect' => $incorrectIndices,"n_attempts"=> $n_attempts);//modified by jbarriapineda in 22-01
	echo(json_encode($arr));
} else if ($task == "subsectionlastanswer") {
    $docid_array = explode(",", $_GET["docids"]);
    $filename = $_GET["filename"];
	$usr = $_GET["usr"];
	$grp = $_GET["grp"];
	$questionmode = $_GET["questionmode"];//added by jbarriapineda in 11-03
	$subdocids = $_GET["subdocids"];//added by jbarriapineda in 01-06
	$questionIds = array();
	$lasts = array();
	
	//foreach($docid_array as $docid) {	  
	  //$questionIds = array_merge($questionIds, docidToQuestionIds($docid));
	//$questionIds = filenameToQuestionIds($filename);//docidToQuestionIds($docid);//commented by jbarriapineda in 10-23
	//}

	$filename = $_GET["filename"];//added by jbarriapineda in 10-23
	
	//foreach($docid_array as $docid){
	if($questionmode=="page"){//if the user reaches the end of a end-of-section page we show all the questions that can be finished by reading that page till the end
		$questionIds = array_merge($questionIds, filenameToQuestionIds($filename));//docidToQuestionIds($docid)); //modified by jbarriapineda in 10-23
	}
	if($questionmode=="section"){//if a user click a question mark icon from the index, we just have to show the questions related to that section
		$subdocids_array=explode(",",$subdocids);
		//if(count($subdocids_array)>0){
		for($i=0;$i<count($subdocids_array);$i++){
			$subdocid=$subdocids_array[$i];
			$questionIds = array_merge($questionIds, docidToQuestionIds2($subdocid)); //added by jbarriapineda in 10-23
		}
		//}
		
	}
	
	$questionIds = getSortedUniqueArray($questionIds);
	
	foreach ($questionIds as $id) {
		$last = getLastAnswer($usr, $grp, $id);
		array_push($lasts, $last);
	}
	
	echo(json_encode($lasts));
}
?>
