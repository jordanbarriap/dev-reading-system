<?php
// example call:
// http://localhost:8888/paws/ebooks/reader.php?bookid=tdo&docno=tdo-2001&usr=dguerra&grp=tdosample&sid=XXXXX&page=1&course=tdo&dbase=kseatdo
include("config.php");
include("dbFunctions.php");
//include("userFunctions.php");

$bookid = $_GET['bookid'];
$docno = $_GET['docno'];
$page = $_GET['page'];
//change by jennifer
$usr = $_GET['usr'];
$grp = $_GET['grp'];
$userid = $_GET['usr'];
$dbase = $_GET['dbase'];

$sid = 'unknown';
$course = 'unknown';
$fromHierarchical = 'none';
$questionMode = 'page';
$subdocids='none';
if (isset($_GET['sid'])) $sid = $_GET['sid'];
if (isset($_GET['course'])) $course = $_GET['course'];
if (isset($_GET['fromHierarchical'])) $fromHierarchical = $_GET['fromHierarchical'];
if (isset($_GET['docidQs'])) $docidQs = $_GET['docidQs'];//added by jbarriapineda in 29-09
if (isset($_GET['filenameQs'])) $filenameQs = $_GET['filenameQs'];//added by jbarriapineda in 11-10
if (isset($_GET['questionMode'])) $questionMode = $_GET['questionMode'];//added by jbarriapineda in 11-03
if (isset($_GET['subdocids'])) $subdocids = $_GET['subdocids'];//added by jbarriapineda in 11-03

// this parameter is received only when the page is load from "previous" or "next" buttons
$frombutton = '';
if (isset($_GET['act'])) $frombutton=$_GET['act'];

// get URL for services from the config file
$corpus_path = $config_corpusURL;
$report_action_url = $config_reportURL;
$jspannotated = $config_annotatedURL;

// comment this
function track_um($book_um, $docid, $usr, $grp, $sid ) {
/*
    $act = '6'.$book_um;
    
    $queryURL = "http://adapt2.sis.pitt.edu/cbum/um?app=6&act=".$act."&sub=kseahci".$docid."&usr=".$usr."&grp=".$grp."&sid=".$sid."&res=0&svc=test3";
    
    $curl = curl_init();
    
    // set URL and other appropriate options
    curl_setopt($curl, CURLOPT_URL, $queryURL);
    curl_setopt($curl, CURLOPT_REFERER, "http://columbus.exp.sis.pitt.edu/ebooks/reader.php");
    curl_setopt($curl, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
    curl_setopt($curl, CURLOPT_PORT, 80);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
    // grab URL and pass it to the browser
    $result = curl_exec($curl);
    
    // close cURL resource, and free up system resources
    curl_close($curl);
    return $queryURL;
*/    
}

// @@@@
$db = 'hci';
if($bookid == 'silberschatz') $db = 'db';
if($bookid == 'tdo') $db = 'tdo';
if($bookid == 'ir') $db = 'ir';
// @@@@
// get document information from database (see dbFunctions.php)
$docInfo = getDocInfo($docno);
$start_page = $docInfo["spage"];
$end_page = $docInfo["epage"];
$title = $docInfo["title"];
$ksdocid = $docInfo["docid"];

$bookInfo = getBookInfo($bookid);
$extension = ".".$bookInfo["format"];
//echo $extension;
$book_folder = $bookInfo["folder"];
$docid = $ksdocid;
$js_data = "usr=".$usr."&grp=".$grp."&docid=".$docid."&dbase=".$dbase;

  
// TRACKING PAGE VIEW IN UM 
// @@@@ commented
//$urlsent = track_um($book_um_name[$bookid], $docid, $usr, $grp, $sid );
//var_dump($urlsent);
?>
<html>
<head>
<title><?php echo $bookInfo["title"]; ?> - <?php echo $bookInfo["authors"]; ?></title>
<style>
body {
	font-family: Arial;
	font-size: 11px;
	background-color: #898989;
}

.section_title {
	font-size: 20px;
	font-weight: bold;
}

.section_nav {
	font-size: 14px;
	font-weight: none;
}

.page_no {
	cursor: pointer;
	padding-left: 5px;
	padding-right: 5px;
	background: beige;
	border: 1px silver solid;
	font-size: 11px;
}

.page_nav {
	cursor: pointer;
	background: #f0f0ff;
	padding-left: 5px;
	padding-right: 5px;
	border: 1px silver solid;
	font-size: 11px;
}

#image_panel {
	border: 1px black solid;
	width: 800px;
	height: 400px;
}

#canvas {
	background-color: yellow;
	width: 100px;
	height: 10px;
	box-shadow: 20px 10px 20px #333;
}

.trackvar {
	display: none;
}

#question-btn {
	position: absolute;
	left: 9px;
	top: 33px;
	cursor: pointer;
	z-index: 70;
}

#question-img {
	max-width: 20px;
}

#question {
	width: 100%;
	height: 100%;
	position: absolute;
	left: 0px;
	top: 0px;
	cursor: pointer;
	z-index: 700;
	border-style: solid;
	border-color: black;
	border-width: 1px;
}
</style>
<script>

var corpus_path = '<?php echo $corpus_path; ?>';
var book_path = '<?php echo $bookInfo["folder"]; ?>';
var book_folder = '<?php echo $bookInfo["folder"]; ?>';
var page_disp = '<?php echo ($start_page + $page - 1); ?>' ;//(JW: match url-page) <?php echo $start_page; ?>;
var bookid = '<?php echo $bookid; ?>';
var docid = '<?php echo $docid; ?>';
var usr = '<?php echo $usr; ?>';
var grp = '<?php echo $grp; ?>';

window.parent.firstActTracking=true;//added by jbarriapineda in 10-01
window.parent.waitQsPopup=false;//added by jbarriapineda in 10-01
window.parent.bookid=bookid;

</script>
<script src='js/jquery.js' type='text/javascript'></script>

<script src='<?php echo $jspannotated;?>excanvas.js'
	type="text/javascript"></script>
<script src='<?php echo $jspannotated;?>prototype.js'
	type='text/javascript'></script>
<script
	src='<?php echo $jspannotated;?>im.js?<?php echo $js_data; ?>&v=0.9'
	type='text/javascript'></script>
	
<script>
  
<?php

echo "var extension = '".$extension."'";

$ip=$_SERVER['REMOTE_ADDR']; 

$page_disp = $start_page + $page - 1;
if($page_disp < 10) $fileName = "0000000";
if($page_disp >= 10 && $page_disp < 100) $fileName = "000000";
if($page_disp >= 100) $fileName = "00000";
$fileName .= $page_disp . $extension;

$imagePath = $corpus_path ."/". $book_folder . "/" . $fileName;
//echo $imagePath;
?>

var ip = '<?php echo $ip; ?>';

function init_reader(){
    var frombutton = '<?php echo $frombutton;?>';
    if(frombutton == ''){
        var sid = '<?php echo $sid; ?>';  
        var course = '<?php echo $course; ?>';
        var docsrc = '<?php echo $grp; ?>';
        var docno = '<?php echo $docno; ?>';  
        var docsrc = '<?php echo $bookid;?>';
        var fileName = '<?php echo $fileName; ?>';  
        var purl = '<?php echo $report_action_url;?>';
        var pars = 'usr='+usr+'&grp='+grp+'&sid='+sid+'&actionsrc=sunburst_model&actiontype=pageload&docsrc='+docsrc+'&docno='+docno+'&filename='+fileName+'&comment=c&result=r';
        //alert(purl+pars);
                
        var myAjax = new Ajax.Request(purl, {
            method: 'get', 
            parameters: pars,
            onSuccess: function(response) {
               // window.location = e.target.href;       
            }    
        });
    }
}
function open_page() {
	console.log("update question status start new page");
	console.log(page_disp);
	console.log(window.parent.docIdMaxpage[docid]);
	if(window.parent.docIdMaxpage[docid]!=page_disp){
		//console.log(parent.document.getElementById("hidden-question-status"));
		parent.document.getElementById("hidden-question-status").click();//added by jbarriapineda in 01-09
	}

	var fileName;
	if(page_disp < 10) fileName = "0000000";
	if(page_disp >= 10 && page_disp < 100) fileName = "000000";
	if(page_disp >= 100) fileName = "00000";

	fileName += page_disp + extension;
	
	//<!-- ki187cm -->
	drawImage(corpus_path + "/" + book_folder + "/" + fileName);
    
    // @@@@ call the set highlight on the iframe with the circle
    //Highlight corresponding section in the visualization at the moment a page is opened
    parent.currentDocno='<?php echo $docno; ?>';
	parent.document.getElementById("iframe-sun").contentWindow.setHighlight('<?php echo $docno; ?>');//added by jbarriapineda in 10-16

	parent.window.scrollTo(0,0);//scroll to the top of the page
	
    //parent.window.frames[0].setHighlight('<?php echo $docno; ?>');
	update_page_list();
}

function update_page_list() {

	var i = <?php echo $start_page; ?>;
	var el;
	while(el = document.getElementById("page-" + i)) {
		el.style.fontWeight = "normal";	
		el.style.background = "beige";
		i++;
	}
	if (document.getElementById("page-" + page_disp) != null)
	    document.getElementById("page-" + page_disp).style.fontWeight = "bold";
//	document.getElementById("page-" + page_disp).style.background = "gold";


	if(document.getElementById('page_next'))
		document.getElementById('page_next').style.visibility = 'visible';
	if(document.getElementById('page_prev'))
		document.getElementById('page_prev').style.visibility = 'visible';
	
	
	
/*JW : match url-page
	if(page_disp == <?php echo $end_page; ?>) {
		document.getElementById('page_next').style.visibility = 'hidden';
	}
	
	if(page_disp == <?php echo $start_page; ?>) {
		document.getElementById('page_prev').style.visibility = 'hidden';
	}
*/
}

function next_page() {
	page_disp++;
	open_page();	
}

function prev_page() {
	page_disp--;
	
//	if(page_disp == 0)
//		page_disp = <?php echo $end_page; ?> - <?php echo $start_page; ?> + 1;	
	
	open_page();
}

var bindEvent = function(elem ,evt,cb) {
    //see if the addEventListener function exists on the element
	if ( elem.addEventListener ) {
		elem.addEventListener(evt,cb,false);
    //if addEventListener is not present, see if this is an IE browser
	} else if ( elem.attachEvent ) {
        //prefix the event type with "on"
		elem.attachEvent('on' + evt, function(){
            /* use call to simulate addEventListener
             * This will make sure the callback gets the element for "this"
             * and will ensure the function's first argument is the event object
             */
             cb.call(event.srcElement,event);
		});
	}
};

Event.observe(window, 'load', function() {  

  // usr,grp,sid,course,actionsrc,actiontype,docsrc,docno,filename
  var sid = '<?php echo $sid; ?>';  
  var course = '<?php echo $course; ?>';
  var docsrc = '<?php echo $grp; ?>';
  var docno = '<?php echo $docno; ?>';  
  var docsrc = '<?php echo $bookid;?>';
  var fileName = '<?php echo $fileName; ?>';
  
  
  var prevc = document.getElementById('prevb');
  var nextc = document.getElementById('nextb');
  var prevc2 = document.getElementById('prevb2');
  var nextc2 = document.getElementById('nextb2');
  
  // actionsrc: previous_button, next_button
  // act : prev, next
  function navButton(e, actionsrc, act){
      window.parent.sbRefresh(docno);

      e.preventDefault();    
      //console.log('click on prev: '+e);
      // @@@@
      //var purl = 'http://columbus.exp.sis.pitt.edu/eRaeder_dev/reportAction.php';
      var purl = '<?php echo $report_action_url;?>';
      var pars = 'usr='+usr+'&grp='+grp+'&sid='+sid+'&actionsrc='+actionsrc+'&actiontype=pageload&docsrc='+docsrc+'&docno='+docno+'&filename='+fileName+'&comment=c&result=r';
      var myAjax = new Ajax.Request(purl, {
          method: 'get', 
          parameters: pars,
          onSuccess: function(response) {
              window.location = e.target.href+'&act='+act;       
          }    
      });
      
      return false;
  }
  
  if (typeof prevc != 'undefined') {  
    bindEvent(prevc,'click',function(e){
      navButton(e,'previous_button','prev');
      return false;
    });   
  }

  if (typeof nextc != 'undefined') {    
    bindEvent(nextc,'click',function(e){
      navButton(e,'next_button','next');
      return false;
    });
  }
  if (typeof prevc2 != 'undefined') {  
    bindEvent(prevc2,'click',function(e){
      navButton(e,'previous_button','prev');
      return false;
    });   
  }

  if (typeof nextc2 != 'undefined') {    
    bindEvent(nextc2,'click',function(e){
      navButton(e,'next_button','next');
      return false;
    });
  }
});

</script>
<!-- ki187cm -->
</head>

<body onLoad='init(); init_reader(); open_page();'>
	<div id="question-btn" style = "display:none">
		<a id="question-link" src="#"> 
		   <img id="question-img" src="questions/q-none.png"></img>
		</a>
	</div>
	<iframe id="question" style="display:none"></iframe>

	<span class='trackvar' id='upd'></span>
	<span class='trackvar' id='usr'><?php echo $usr; ?></span>
	<span class='trackvar' id='grp'><?php echo $grp; ?></span>
	<span class='trackvar' id='sid'><?php echo $sid; ?></span>
	<span class='trackvar' id='course'><?php echo $course; ?></span>
	<span class='trackvar' id='docno'><?php echo $docno; ?></span>
	<span class='trackvar' id='filename'><?php echo $fileName; ?></span>
	<span class='trackvar' id='img-data'>bookid=shnm&docno=shnm-0001&page=1&page_nav=21&usr=dap89&grp=grp1</span>
<?php

/*
**
*/

$prev_docid = $docid - 1;
$next_docid = $docid + 1;
// @@@@ all queries changed
$res = getDocInfoById($prev_docid,$bookid);
//$res = mysql_query("select docno,spage,epage from document where docid = '$prev_docid' and docno like '$bookid%'");
//$prow = mysql_fetch_object($res);
//$prow_num = mysql_num_rows($res);

if (count($res)>0) {
    $p_start = $res["spage"];
    $p_end = $res["epage"];
}
$res = getDocInfoById($next_docid,$bookid);
if (count($res)>0) {
    $n_start = $res["spage"];
    $n_end = $res["epage"];
}

$disp_page = $start_page + $page - 1 + $config_page_corr_offset[$bookid];

?>

<!-- ki187cm -->
	<canvas id="canvas" width="800" height="1200"
		style="position: absolute; top: 0; left: 0; border-width: 0border-color:blue; border-style: solid; visibility: hidden"></canvas>
	<canvas id="canvas1" width="800" height="1200"
		style="position: absolute; top: 0; left: 0; border-width: 0; border-color: blue; border-style: solid; visibility: hidden"></canvas>
	<canvas id="canvas2" width="800" height="1200"
		style="position: absolute; top: 0; left: 0; border-width: 0; border-color: blue; border-style: solid; visibility: hidden"></canvas>

	<style>
.page_nav2 {
	font-size: 10px;
	font-family: Arial;
	font-weight: bold;
	background: silver;
	color: white;
	text-align: center;
	cursor: pointer;
}
</style>

<?php
$courseKey = $config_book_key_course_key_map[$bookid];

if($courseKey == 'dbms') {
	session_start();
	$session_key = $courseKey.'/navigationPath';
	
	if($_SESSION[$session_key] == null) {
		//create navigation list from JSON provided for the indexTree
		$_SESSION[$session_key] = createNavigationPath($courseKey);
	}
	
	$navigationPath = $_SESSION[$session_key];
	$currentPageNavigationIndex = findCurrentPageIndex();
	
	$prev_url = $config_readerURL."?bookid=".$bookid."&usr=".$usr."&grp=".$grp."&sid=".$sid."&course=".$course."&dbase=".$dbase."&".getPreviousPage($bookid, $disp_page);
	$next_url = $config_readerURL."?bookid=".$bookid."&usr=".$usr."&grp=".$grp."&sid=".$sid."&course=".$course."&dbase=".$dbase."&".getNextPage($bookid, $disp_page);
} else {
	$prev_url = $config_readerURL."?bookid=".$bookid."&usr=".$usr."&grp=".$grp."&sid=".$sid."&course=".$course."&dbase=".$dbase."&".getSectionByPage($bookid, $disp_page, $disp_page - 1);
	$next_url = $config_readerURL."?bookid=".$bookid."&usr=".$usr."&grp=".$grp."&sid=".$sid."&course=".$course."&dbase=".$dbase."&".getSectionByPage($bookid, $disp_page, $disp_page + 1);
}


function findCurrentPageIndex() {
	global $docno, $disp_page, $navigationPath;

	for ($x = 0; $x <= count($navigationPath); $x++) {
		$navigation = $navigationPath[$x];
		if($navigation -> docno == $docno && $navigation -> bookPage == $disp_page) {
			return $x;
		}
	}

	return 0;
}


//create navigation list from JSON data for only once. It stores the navigation array in a static field.
function createNavigationPath($courseKey) {
	$navigation_path = array();

	$jsonFileContent = file_get_contents("data/".$courseKey.".json");
	$json = json_decode($jsonFileContent, true);

	$lectureArray = $json['children'];

	foreach ($lectureArray as $lecture) {
		foreach($lecture['children'] as $child) {
			createNewNavigation($child, $navigation_path, 1);
		}
	}

	return $navigation_path;
}

function createNewNavigation($child, &$navigation_path, $sectionPage) {
	if($child !== null) {
		foreach($child['links'] as $page) {
			$navigation = new stdClass();
			$navigation -> docno = $child['docno'];
			$navigation -> sectionPage = $sectionPage;
			$navigation -> bookPage = $page['pageNumber'];


			$sectionPage = $sectionPage + 1;
			array_push($navigation_path, $navigation);
		}

		if($child['children'] !== null) {
			foreach($child['children'] as $sub_child) {
				createNewNavigation($sub_child, $navigation_path,  1);
			}
		}
	}

	return $navigation_index;
}

function getNextPage($bookid, $disp_page) {
	global $navigationPath, $currentPageNavigationIndex;
	
	$tempIndex = $currentPageNavigationIndex;
	
	$current_page = $navigationPath[$tempIndex];
	$tempIndex = $tempIndex + 1;
	
	$next_page = $navigationPath[$tempIndex++];
	
	while($current_page -> bookPage == $next_page -> bookPage) { //Next section starts in current page, get further next page
		$next_page = $navigationPath[$tempIndex++];
	}
	
	$docno = $next_page -> docno;
	$in_section_page = $next_page -> sectionPage;
	$target_page = $next_page -> bookPage;
	
	return "&docno=".$docno."&page=".$in_section_page."&page_nav=".$target_page;
}

function getPreviousPage($bookid, $disp_page) {
	global $navigationPath, $currentPageNavigationIndex;
	
	$tempIndex = $currentPageNavigationIndex;
	$current_page = $navigationPath[$tempIndex];
	$prev_page = null;
	
	if($tempIndex == 0) { //first page, there is no prev page
		$prev_page = $current_page;
	} else {
		$tempIndex = $tempIndex -1;//previous page index
		$prev_page = $navigationPath[$tempIndex--];
		
		while($tempIndex > 0 && ($current_page -> bookPage === $prev_page -> bookPage)) { //Previous section starts in current page, get further previous page
			$prev_page = $navigationPath[$tempIndex--];
		}
	}
	
	$docno = $prev_page -> docno;
	$in_section_page = $prev_page -> sectionPage;
	$target_page = $prev_page -> bookPage;

	return "&docno=".$docno."&page=".$in_section_page."&page_nav=".$target_page;
}
?>
<!--img id='image' style="border:2px dotted green" src="<?php echo $imagePath; ?>"-->
	<style>
.page_navigation {
	/*background: lightgray;*/
	text-align: center;
	color: black;
	text-decoration: none;
}
</style>

	<table cellpadding=0>
		<tr>
<?php
$npages = $end_page - $start_page + 1;
$titleText = $title." [Page ".$page." of ".$npages."]";
if($prev_url)
	print "<td class='page_navigation' width=40><a class='page_navigation' id='prevb'  href='".$prev_url."'><< </a></td>";
else
	print "<td class='page_navigation' style='background-color:#898989;' width=40><span id='prevb'></span>&nbsp;</td>";
	
  print "<td class='page_navigation' style='background-color:#cccccc; font-size:12px;' width=620>".$titleText."</td>";

  
if($next_url)
	print "<td class='page_navigation' width=40><a class='page_navigation' id='nextb' href='".$next_url."'> >></a></td>";
else
	print "<td class='page_navigation' style='background-color:#cccccc;' width=40><span id='prevb'></span>&nbsp;</td>";

?>

</tr>
	</table>
	<input type = "hidden" id = "current-page" value = "<?php echo ($start_page + $page - 1);?>">
	<input type = "hidden" id = "reader-docid" value = "<?php echo $docid;?>">
	<img id='image' border=1>
	<div style="overflow: hidden; position: absolute; bottom: 10px;">
		<table cellpadding=0>
			<tr>
<?php

if($prev_url)
	print "<td class='page_navigation' width=40><a class='page_navigation' id='prevb2'  href='".$prev_url."'><< </a></td>";
else
	print "<td class='page_navigation' style='background-color:#898989;' width=40><span id='prevb2'></span>&nbsp;</td>";
	
  print "<td class='page_navigation' style='background-color:#cccccc; font-size:12px;' width=620>".$titleText."</td>";

  
if($next_url)
	print "<td class='page_navigation' width=40><a class='page_navigation' id='nextb2' href='".$next_url."'> >></a></td>";
else
	print "<td class='page_navigation' style='background-color:#cccccc;' width=40><span id='nextb2'></span>&nbsp;</td>";

?>
</tr>
		</table>
	</div>
	<script>
	// status enum
	var statuses = {
		RIGHT: 0,
		WRONG: 1,
		UNANSWERED: 2,
		NONE: 3
	};

	var questionStatus = statuses.NONE;

	var qFrame = document.getElementById('question');

	var toggleSpecificQuestion = function(docId) {
	    // Gets docids.

	    var allDocIds = JSON.parse(parent.document.getElementById('allDocIds').value);  
	    for(var index = 0; index < allDocIds.length; index++) {
	      var element = allDocIds[index];
	      if(("," + element).indexOf("," + docId + "@") > -1) {  
	         //qFrame.src = 'questions/question.php?docid=' + docId + '&usr=' + usr + '&grp=' + grp + '&docids=' + element;
	         var fileName = '<?php echo $bookid."_".$fileName; ?>';
	         qFrame.src = 'questions/question.php?docid=' + docId + '&usr=' + usr + '&grp=' + grp + '&docids=' + element+'&filename='+fileName;
	         $(qFrame).toggle();
	      }
	    }
	    if(!jQuery(qFrame).is(':visible')){//added by jbarriapineda in 30-09
		     	window.parent.skippedQuestions[docid]=1;
		}//end of code added by jbarriapineda
	};
	//end of code added by jbarriapineda

	var toggleSpecificQuestionFilename = function(fileName) {
	    // Gets docids.

	    //var allDocIds = JSON.parse(parent.document.getElementById('allDocIds').value);  
	    //for(var index = 0; index < allDocIds.length; index++) {
	    //  var element = allDocIds[index];
	    //  if(("," + element).indexOf("," + docId + "@") > -1) {  
	         qFrame.src = 'questions/question.php?docid=-&usr=' + usr + '&grp=' + grp + '&docids=' + '&filename='+fileName+'&questionMode=page&subdocids=';
	         //qFrame.src = 'questions/question.php?usr=' + usr + '&grp=' + grp + '&filename='+fileName;
	         $(qFrame).toggle();
	    //  }
	    //}
	    if(!jQuery(qFrame).is(':visible')){//added by jbarriapineda in 30-09
		     	window.parent.skippedQuestions[fileName]=1;
		}//end of code added by jbarriapineda
	};
	//end of code added by jbarriapineda

	var toggleQuestion = function() {
	    // Gets docids.
	    var questionMode = '<?php echo $questionMode; ?>';
	    var subdocids = '<?php echo $subdocids; ?>';
		var allDocIds = JSON.parse(parent.document.getElementById('allDocIds').value);
		var fileName = "";
		for(var index = 0; index < allDocIds.length; index++) {
		  var element = allDocIds[index];
		  if(("," + element).indexOf("," + docid + "@") > -1) {  
		     //qFrame.src = 'questions/question.php?docid=' + docid + '&usr=' + usr + '&grp=' + grp + '&docids=' + element;
		     fileName = '<?php echo $bookid."_".$fileName; ?>';
		     qFrame.src = 'questions/question.php?docid=' + docid + '&usr=' + usr + '&grp=' + grp + '&docids=' + element+'&filename='+fileName+'&questionMode='+questionMode+'&subdocids='+subdocids;
		     $(qFrame).toggle();
		  }
		}
		if(!jQuery(qFrame).is(':visible')){//added by jbarriapineda in 30-09
			if(questionMode=="page"){
		     	window.parent.skippedQuestions[fileName]=1;
		     	/*console.log("fileName: "+fileName+" fileNameQs: "+window.parent.filenameQs);
		     	if(fileName==window.parent.filenameQs){
					window.parent.pendingAnswers=-1;
				}*/

			}
			if(questionMode=="section"){
				window.parent.skippedQuestions[docid]=1;
				/*console.log("docid: "+docid+" docidQs: "+window.parent.docidQs);
				if(docid==window.parent.docidQs){
					window.parent.pendingAnswers=-1;
				}*/
			}
		}//end of code added by jbarriapineda

	};

	var questionOpen = function() {
		return $(qFrame).visible();
	};

	if(!window.parent.docidQs) window.parent.docidQs=-1;//added by jbarriapineda in 29-09
	if(!window.parent.filenameQs) window.parent.filenameQs=-1;//added by jbarriapineda in 11-03
	/*if(window.parent.docidQs!=-1 && docid!=window.parent.docidQs){//added by jbarriapineda in 29-09
	    toggleSpecificQuestion(window.parent.docidQs);
	    window.parent.docidQs=-1;
	}//end of code added by jbarriapineda*/
	var fileName = '<?php echo $bookid."_".$fileName; ?>';
	//console.log(fileName);
	if(window.parent.filenameQs!=-1 && fileName!=window.parent.filenameQs){//added by jbarriapineda in 11-03
		//console.log("toggleSpecificQuestionFilename "+window.parent.filenameQs);
	    toggleSpecificQuestionFilename(window.parent.filenameQs);
	    window.parent.filenameQs=-1;
	    window.parent.pendingAnswers=-1;
	}//end of code added by jbarriapineda
	

	var ESC = 27;
	document.body.addEventListener('keydown', function(e) {
		var keyCode = e.keyCode;
		if (keyCode === ESC && questionOpen()) {
			toggleQuestion();
			e.stopPropagation();
		}
	});

	var questionImg = document.getElementById('question-img');
	var questionLink = document.getElementById('question-link');
	questionLink.addEventListener('click', toggleQuestion);
	<?php
	if($fromHierarchical === "tree"){
	?>
	questionLink.click();	
	<?php 
	}
	?>
	
	var updateQuestionStatus = function(status) {
		questionStatus = status;
		if (questionStatus == statuses.RIGHT) {
			questionImg.src = "questions/q-right.png";
		} else if (questionStatus == statuses.WRONG) {
			questionImg.src = "questions/q-wrong.png";
		} else if (questionStatus == statuses.UNANSWERED) {
			questionImg.src = "questions/q-unanswered.png";
		} else if (questionStatus == statuses.NONE) {
			questionImg.src = "questions/q-none.png";
		}

		/**
		// update index too		
		var updateIndexToo = function(id) {
			var links = parent.document.getElementsByClassName('docid-' + id);
			for (var i = 0; i < links.length; i++) {
				var link = links[i];
				// remove any existing status
				var cl = link.classList;
				for (var j = cl.length-1; j >= 0; j--) {
					var c = cl[j];
					if (c.startsWith('status-')) {
						link.classList.remove(c);
					}
				}
				link.classList.add('status-' + questionStatus);
			}
		};
		updateIndexToo(docid);

		// and also other docids that share a question
		jQuery.ajax({
	        url: 'questions/api.php',
	        data: {'task': 'samequestions', 'docid': docid},
	        dataType: "json",
	        success: function(data) {
	     	   for (var i = 0; i < data.length; i++) {
	     		   updateIndexToo(data[i]);
	     	   }
	        }
	    });
		*/
		
		if (questionStatus === statuses.NONE) {
			qFrame.removeAttribute('src');
		} else if (!qFrame.src) {
			questionLink.addEventListener('click', toggleQuestion);
			//qFrame.src = 'questions/question.php?docid=' + docid + '&usr=' + usr + '&grp=' + grp;
			var fileName = '<?php echo $bookid."_".$fileName; ?>';
			qFrame.src = 'questions/question.php?docid=' + docid + '&usr=' + usr + '&grp=' + grp+'&filename='+fileName;
		}
		
		<?php
		if($fromHierarchical === "tree"){
		?>
		questionLink.click();
		<?php 
		}
		?>
	}

	// After prototype.js loads for annotations,
	// JSON.strigify({a:[1,2,3]}) returns '{"a":"[1,2,3]"}'
	// but it should return '{"a":[1,2,3]}'  (without quotes around the array)
	// constructing an array using parent fixes the problem
	
	var a = new parent.Array();
	a.push(Number(docid));
	
	var data = {
	        'docids': a,
	        'usr': usr,
	        'grp': grp
	    };

	/**
	jQuery.ajax({
	        url: 'questions/api.php?task=status',
	        type: 'POST',
	        data: parent.JSON.stringify(data),
		    contentType: 'application/json',
	        dataType: "json",
	        success: function(result) {
		        updateQuestionStatus(result[docid]);
	       }
	 });
	*/

function getAncestors(node) {
  var path = [];
  var current = node;
  while (current.parent) {
    path.unshift(current);
    current = current.parent;
  }
  return path;
}

</script>

</body>
</html>