<?php
include("config.php");
include("dbFunctions.php");
include("userFunctions.php");

$usr = $_GET["usr"];
$grp = $_GET["grp"];
$sid = $_GET["sid"];

$reader_url = $config_readerURL;


$courseInfo = getCourseInfo($grp);

$course_key = $courseInfo["coursekey"];
$course_domain = $courseInfo["domain"];
$course_kseadb = $courseInfo["legacy_kseadb"];
$course_title = $courseInfo["title"];
$course = $course_key; // used for getting the file name of the json structure

// The book, docno and page can either be received as parameters or
// they are taken from the last action of the user, or the default given the
// group (grp)
$bookid = "";
$docno =  ""; 
$page = -1;

// AS PARAMETERS: The book, docno and page to load can be received as parameters
//if (isset($_GET['course']) ) { $course = $_GET['course'];}
if (isset($_GET['bookid']) ) { $bookid = $_GET['bookid'];}
if (isset($_GET['docno']) ) { $docno = $_GET['docno'];}
if (isset($_GET['page']) ) { $page = $_GET['page'];}


// if there were no parameters got from URL
if(strlen($bookid) == 0){
    $user_last_doc = getUserLastDocViewed($usr,$grp,$course_domain);
	
    if(!is_null($user_last_doc)){
        $bookid = $user_last_doc["docsrc"];
        $docno =  $user_last_doc["docno"]; 
        $page = 1;
    }else{
        if($course === 'isd'){
            $bookid = "lamming";
            $docno =  "lamming-0001"; 
            $page = 1; 
        }
        if($course === 'tdo'){
            $bookid = "tdo";
            $docno =  "tdo-2000";
            $page = 1;
        }		
		if($course === 'ir'){
            $bookid = "iir";
            $docno =  "iir-2104";
            $page = 1;
        }
    }
}

if (isset($_GET['docno']) ) { $docno = $_GET['docno'];}

?>
<!DOCTYPE html>
<html>
<head>
<title>ReadingCircle ~ PAWS Lab at Pitt</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<link href="css/bootstrap.css" type="text/css" rel="stylesheet"/>
<link href="css/button.css" type="text/css" rel="stylesheet"/>
<link href="css/eRaeder.css" type="text/css" rel="stylesheet"/>
<!--<link type='text/css' href='css/demo.css' rel='stylesheet' media='screen'/>
<link type='text/css' href='css/basic.css' rel='stylesheet' media='screen'/>
<link type='text/css' href='css/basic_ie.css' rel='stylesheet' media='screen'/>
<link type="text/css" rel="stylesheet" href="chosen/chosen.css"/>
<link type="text/css" rel="stylesheet" href="css/button.css"/>-->
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.peity.min.js"></script>
<script type="text/javascript" src="js/jquery.scrollTo.min.js"></script>
<script type="text/javascript" src="js/bootstrap.js"></script>
<script type="text/javascript" src="d3/d3.js"></script>
<!--script type="text/javascript" src="partition-sunburst.js"></script-->
<script>
    $(document).ready(function () {
        var flag = 0;
        $("#slide-btn").click(function () {
            if (flag == 0) {
                $("#sidebar").animate({"width":"0"}, 500)
                $("#content").animate({"padding":"0 0 0 200px"}, 500)
                $("#slide-btn").animate({"left":"0"}, 500)
                $("#sidebar").hide(500)
                $("#toggle-question-status").hide();
                flag = 1
                return true;
            }
            if (flag == 1) {
                $("#sidebar").animate({"width":"450px"}, 500).show()
                $("#slide-btn").animate({"left":"450px"}, 500)
                $("#content").animate({"padding":"0 0 0 500px"}, 500)
                $("#toggle-question-status").show(500);
                flag = 0
                return true;
            }
        });
    });

    function sbRefresh(docno){
         
         $("#iframe-sun")
           //.load(function(){window.frames[0].setHighlight(docno);})
           .attr("src","partition-sunburst.php?usr=<?php echo $usr;?>&grp=<?php echo $grp;?>&sid=<?php echo $sid;?>&course=<?php echo $course;?>&dbase=<?php echo $course_kseadb;?>&docno="+docno);
           //alert("partition-sunburst.php?usr=<?php echo $usr;?>&grp=<?php echo $grp;?>&sid=<?php echo $sid;?>&course=<?php echo $course;?>&dbase=<?php echo $course_kseadb;?>&docno="+docno);
    }
    
</script>
</head>
<body>
<div id="container">
  <div id="sidebar">
    <iframe name="iframe-sb" id="iframe-sun" width="450px" height="400px" scrolling="no" style="overflow:hidden;" 
        src="partition-sunburst.php?v=100&usr=<?php echo $usr;?>&grp=<?php echo $grp;?>&sid=<?php echo $sid;?>&course=<?php echo $course;?>&dbase=<?php echo $course_kseadb;?>&docno=<?php echo $docno;?>" ></iframe>
    <div style="width:450px; overflow:hidden">
      <?php require_once("small-multiples.php") ?>
    </div>
  </div>
  <div id="slide-btn">
    <button id="slide-toggle" class="btn btn-inverse btn-mini"><i class="icon-resize-horizontal icon-white"></i> </button>
  </div>
  <div id="content">
    <iframe id="readings" src="<?php echo $reader_url;?>?bookid=<?php echo $bookid;?>&docno=<?php echo $docno;?>&page=<?php echo $page;?>&usr=<?php echo $usr;?>&grp=<?php echo $grp;?>&sid=<?php echo $sid;?>&course=<?php echo $course;?>&dbase=<?php echo $course_kseadb;?>" 
        name="iframe-content" scrolling="no" style="overflow-x:hidden; overflow:hidden; padding-top: -100px; height: 1130px; width: 100%;"></iframe>
  </div>
</div>

<!-- Ruikun's work part1 -->
<div id="peerCompareShowBox">
  <div id="peerCompareShowWrapper">
    <div class="chartCompare">
      <iframe name="iframe-sb" id="iframe-sun" width="450px" height="435px" src="partition-sunburst.php?v=100&usr=<?php echo $usr;?>&grp=<?php echo $grp;?>&sid=<?php echo $sid;?>&course=<?php echo $course;?>&dbase=<?php echo $course_kseadb;?>"></iframe>
      <h5 id="firstChartCompare"></h5>
    </div>
  </div>
  <span id="closeBtn">Close</span> </div>
<!-- part1 end -->
	<div id="overlay"></div>
  
</body>

<!-- Ruikun's work part2 -->
<script type="text/javascript">

var usr = "<?php echo $usr; ?>";
var grp = "<?php echo $grp; ?>";
var sid = "<?php echo $sid; ?>";
var course = "<?php echo $course; ?>";
var course_kseadb = "<?php echo $course_kseadb; ?>";
var groupmodels_url = "<?php echo $config_groupMoldelsURL; ?>";
groupmodels_url = groupmodels_url+"?grp="+grp+"&mode=model";
var userName=[], userLogin=[];
//alert(thisUrl);

$.ajax({
    url: groupmodels_url,
    async:false,
    success: function(result){

		 for(var i=0;i<result.users.length;i++){
			userName[i]=result.users[i].name;
		 	userLogin[i]=result.users[i].login; 
		 }
		 //alert(userName);
		 //alert(userLogin);
	 }
})



/*	var peerCompare=document.getElementById("peerCompareShowBtn");
	var peerCompareShowWrapper=document.getElementById("peerCompareShowWrapper");
	peerCompare.onclick=peerCompareShow;	
	var peerCompareShowBox=document.getElementById("peerCompareShowBox");*/


/*	var svgTemp=document.getElementById("chart2").getElementsByTagName("svg");
	for (var k=0; k<svgTemp.length;k++){
		svgTemp[k].onclick=getUserName;
	}*/
	
var svgNameSub=0;
	
	$(function(){
		$("#chart2 #chart2Btn a").click(function(){
			
			switch($(this).html()){
				case "0":
					svgNameSub=0;
					break;
				case "1":
					svgNameSub=1;
					break;
				case "2":
					svgNameSub=2;
					break;		
			}
			
			//alert(svgNameSub);
			peerCompareShow();

			return false;
		});
				
	});
	
	// @@@@ REVIEW
	var userNameSub=0;
	for(var j=0;j<userLogin.length;j++){
		if(userLogin[j]==usr){
			userNameSub=j;
			//alert(userName[j])
		}
	}

	
	//var compareUserName=[];
	//var subNum=0;
	
/*	for(var k=0;k<userLogin.length;k++){		
		//if(userLogin[k]!=usr){
			compareUserName[subNum]=userName[k];
			subNum++;
		//}	
	}*/
	var closeBtn=document.getElementById("closeBtn");
	closeBtn.onclick=PeerCompareClose;
	var peerCompareShowWrapper=document.getElementById("peerCompareShowWrapper");
	
	function peerCompareShow(){
		//$("#peerCompareShowBox").css("display","block");
		$("#peerCompareShowBox").fadeIn("fast");
		peerCompareShowWrapper.innerHTML="<div class='chartCompare'><iframe src='partition-sunburst.php?v=100&usr="+usr+"&grp="+grp+"&sid="+sid+"&course="+course+"&dbase="+course_kseadb+"' name='iframe-sb' id='iframe-sun' width='450px' height='435px'></iframe>"+
		    "<h5>"+userName[userNameSub]+"</h5></div>"+
		    "<div class='chartCompare'><iframe src='partition-sunburst.php?v=100&usr="+userLogin[svgNameSub]+"&grp="+grp+"&sid="+sid+"&course="+course+"&dbase="+course_kseadb+"' name='iframe-sb' id='iframe-sun' width='450px' height='435px'></iframe>"+
		    "<h5>"+userName[svgNameSub]+"</h5></div>";
	}
	
	function PeerCompareClose(){
		$("#peerCompareShowBox").fadeOut("fast");
		//$("#peerCompareShowBox").css("display","none");	
	}
	
/*	for(var j=0;j<userLogin.length;j++){
		if(userLogin[j]==usr){
			document.getElementById("firstChartCompare").innerHTML=userName[j];
			//alert(userName[j])
		}
	}*/

	
/*	for(var j=0;j<userLogin.length;j++){
		if(userLogin[j]!=usr){
			peerCompareShowWrapper.innerHTML+="<div class='chartCompare'><iframe src='partition-sunburst.php?v=100&usr="+userLogin[j]+"&grp="+grp+"&sid="+sid+"&course="+course+"&dbase="+course_kseadb+"' name='iframe-sb' id='iframe-sun' width='450px' height='435px'></iframe><h5>"+userName[j]+"</h5></div>";
		}
		else{
			document.getElementById("firstChartCompare").innerHTML=userName[j];
		}
	}*/
	
	
	//peerCompareShowWrapper.innerHTML+="<div style='clear:both'></div>";
	

</script>
<!-- part2 end -->
<script type='text/javascript'>
	var send_progress_url = "<?php echo $config_sendProgressURL;?>";
</script>
<script type="text/javascript" src="scripts/progress.js"></script>
</html>