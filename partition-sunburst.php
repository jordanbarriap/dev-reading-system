<?php 
include("config.php");
$usr = $_GET["usr"];
$grp = $_GET["grp"];
$sid = $_GET["sid"];
$course = $_GET["course"];
$dbase = $_GET["dbase"];
$docno = "";
if(isset($_GET["docno"])) $docno = $_GET["docno"];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Partition - Sunburst</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <link type='text/css' href='css/demo.css' rel='stylesheet' media='screen' />
    <link type='text/css' href='css/basic.css' rel='stylesheet' media='screen' />
    <link type="text/css" rel="stylesheet" href=" css/button.css"/>
    <!-- IE6 "fix" for the close png image -->
    <!--[if lt IE 7]>
    <link type='text/css' href='css/basic_ie.css' rel='stylesheet' media='screen' />
    <![endif]-->
    <link type="text/css" href="css/bootstrap.css" rel="stylesheet" />
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="http://d3js.org/d3.v3.min.js"></script>

    <script type='text/javascript'>
        // write vars used by the sunburst
        //var testingvar = "works!!!";
        var reader_url = "<?php echo $config_readerURL;?>";
        var colors = ["<?php echo $config_colors[0];?>","<?php echo $config_colors[1];?>","<?php echo $config_colors[2];?>","<?php echo $config_colors[3];?>","<?php echo $config_colors[4];?>","<?php echo $config_colors[5];?>","<?php echo $config_colors[6];?>","<?php echo $config_colors[7];?>","<?php echo $config_colors[8];?>","<?php echo $config_colors[9];?>"];
        var progress_url = "<?php echo $config_progressURL;?>";
        var json_file = "data/<?php echo $course;?>.json";
        var usr = "<?php echo $usr;?>";
        var grp = "<?php echo $grp;?>";
        var sid = "<?php echo $sid;?>";
        var course = "<?php echo $course;?>";
        var dbase = "<?php echo $dbase;?>";
        var currentDocno = "<?php echo $docno;?>";
        function getBookName(docsrc){
            var res = "";
            switch(docsrc){
                case "lamming":
                    res = "Interactive System Design (Newman and Lamming)";
                    break;
                case "shnm":
                    res = "Designing User Interface (Shneiderman)";
                    break;
                case "preece":
                    res = "Interaction Design (Preece, Rogers and Sharp)";
                    break;
                case "dix": 
                    res = "Human Computer Interaction (Dix)";
                    break;
                case "lewis": 
                    res = "Task-Centered User Interface Design (Lewis and Rieman)";
                    break;
                case "tdo": 
                    res = "The Discipline of Organizing"; // @@@@
                    break;
				case "iir": 
                    res = "Introduction to Information Retrieval"; // @@@@
                    break;
				case "mir": 
                    res = "Modern Information Retrieval"; // @@@@
                    break;
            }
            return res;
        }
        
        $(document).ready(function () {
            
            //alert('Reader load! '+currentDocno);
        
        });

        function reloadChartDetail(){
            var storedValue = localStorage.getItem("chart-detail");

            if(storedValue == null){
                storedValue = 3;
            }
            
            $("#chart-detail").val(storedValue);
            updateChartDetail(storedValue);
        }

        function updateChartDetail(val){
            $("[class^=partition_depth_]").show();
            $("[class^=partition_depth_0]").hide();

            for(var i = 4; i > val; i--){
                $(".partition_depth_"+i).hide();
            }

            localStorage.setItem("chart-detail", val);
        }
        
        function unShadeAll(){
            
            unsetHighlight();
        }
    </script>
</head>
<body>
    <div id='content'>
        <!-- modal content -->
        <div id="basic-modal-content-1" class="basic-modal-content">
            <h3>Basic Modal Dialog 1</h3>
            <p>For this demo, SimpleModal is using this "hidden" data for its content. You can also populate the modal dialog with an AJAX response, standard HTML or DOM element(s).</p>
            <p>Examples:</p>
            <p><code>$('#basicModalContent').modal(); // jQuery object - this demo</code></p>
            <p><code>$.modal(document.getElementById('basicModalContent')); // DOM</code></p>
            <p><code>$.modal('&lt;p&gt;&lt;b&gt;HTML&lt;/b&gt; elements&lt;/p&gt;'); // HTML</code></p>
            <p><code>$('&lt;div&gt;&lt;/div&gt;').load('page.html').modal(); // AJAX</code></p>
            <p><a href='http://www.ericmmartin.com/projects/simplemodal/'>More details...</a></p>
        </div>
        <div id="basic-modal-content-2" class="basic-modal-content"></div>

        <!-- preload the images -->
        <div style='display:none'>
            <img src='img/basic/x.png' alt='' />
        </div>
    </div>

    <!-- here the sunburst will be loaded-->
    <div id="chartOut">
        <span id="tip" style="position: absolute; left: 0; top: 0; width:448px; border-bottom: 1px solid #cccccc; font-size: 12px; line-height:16px;"></span>
        <div id="detail">
            <label for="shade"><button style="font-size:11px; width:90px; height:15px; padding-top:2px;line-height:10px;" id="shadeInputLabel" onclick="unShadeAll();">Unshade All</button></label>
            <label for="detail" style="font-size:11px;">Levels&nbsp;
                <input type="range" value="3" max="4" min="1" name="detail" id="chart-detail"
                    onchange="updateChartDetail(this.value)">
            </label>
            
        </div>
        <div id="chart"></div>
        <!-- <div id="progress-bar" class="progress progress-striped active">
            <div class="bar" style="background-color: <?php echo $config_colors[0];?>; width: 10%">0%</div>
            <div class="bar" style="background-color: <?php echo $config_colors[1];?>; width: 10%"></div>
            <div class="bar" style="background-color: <?php echo $config_colors[2];?>; width: 10%"></div>
            <div class="bar" style="background-color: <?php echo $config_colors[3];?>; width: 10%"></div>
            <div class="bar" style="background-color: <?php echo $config_colors[4];?>; width: 10%">Reading</div>
            <div class="bar" style="background-color: <?php echo $config_colors[5];?>; width: 10%">Progress</div>
            <div class="bar" style="background-color: <?php echo $config_colors[6];?>; width: 10%"></div>
            <div class="bar" style="background-color: <?php echo $config_colors[7];?>; width: 10%"></div>
            <div class="bar" style="background-color: <?php echo $config_colors[8];?>; width: 10%"></div>
            <div class="bar" style="background-color: <?php echo $config_colors[9];?>; width: 10%">100%</div>
            
        </div> -->
    </div>


    <script id="sunid" type="text/javascript" src="partition-sunburst.js?v=101"></script>
    <script type='text/javascript' src='js/jquery.simplemodal.js'></script>
    <script type='text/javascript' src='js/basic.js'></script>
    

</body>
</html>