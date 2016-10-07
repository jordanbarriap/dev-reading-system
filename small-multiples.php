<?php 
@$dbase = $_GET["dbase"];
?>
<script type="text/javascript" src="js/d3.v2.js"></script>
<script type="text/javascript" src="http://d3js.org/d3.v3.min.js"></script>
<script type='text/javascript'>
        // write vars used by the sunburst
        var reader_url = "<?php echo $config_readerURL;?>";
        var colors = ["<?php echo $config_colors[0];?>","<?php echo $config_colors[1];?>","<?php echo $config_colors[2];?>","<?php echo $config_colors[3];?>","<?php echo $config_colors[4];?>","<?php echo $config_colors[5];?>","<?php echo $config_colors[6];?>","<?php echo $config_colors[7];?>","<?php echo $config_colors[8];?>","<?php echo $config_colors[9];?>"];
        var groupmodels_url = "<?php echo $config_groupMoldelsURL;?>";
        var selfmodels_url = "<?php echo $config_selfMoldelsURL;?>";
        var json_file = "data/<?php echo $course;?>.json";
        var usr = "<?php echo $usr;?>";
        var grp = "<?php echo $grp;?>";
        var sid = "<?php echo $sid;?>";
		var currentDocno = "<?php echo $docno;?>";
        var course = "<?php echo $course;?>";
        var dbase = "<?php echo $dbase;?>";
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
                    res = "Introduction to Information Retrieval (IIR)"; // @@@@
                    break;
				case "mir": 
                    res = "Modern Information Retrieval (MIR)"; // @@@@
                    break;
                case "mir2": 
                    res = "Modern Information Retrieval (MIR, 2nd edition)"; // @@@@
                    break;
                case "foa": 
                    res = "Finding Out About: A Cognitive Perspective on Search Engine Technology and the WWW (FOA)"; // @@@@
                    break;
                case "ies": 
                    res = "Information Retrieval: Implementing and Evaluating Search Engines (IES)"; // @@@@
                    break;
            }
            return " " + res;
        }
        
        function selectFunction(lecture, opacity) {
            d3.selectAll("." + lecture).style("opacity", opacity);
            return false;
        }
        
        $(document).ready(function () {
            
            //alert('Reader load!');
			
			var extend = function() {
				// make sure tab extends to bottom of viewport
	            var tc = document.getElementById('tab-content');
	            var viewportHeight = $(window).height();
	            var tcTop = tc.getBoundingClientRect().top;
	            var height = Math.max(0, viewportHeight - tcTop);
	            tc.style.height = height + 'px';
			};

			extend();

			$(window).on('resize orientationChange', function(event) {
				extend();
			});
        
        });
    </script>

<section id="tabs">
  <section class="tabbable">
    <ul class="nav nav-tabs" id="navTab">
      <li class="active"><a href="#tab1" data-toggle="tab">Index</a></li>
      <li><a href="#tab3" data-toggle="tab">Peer Comparison</a></li>
      <li><a href="#tab4" data-toggle="tab">My Progress</a></li>
    </ul>
    <section class="tab-content" id="tab-content">
      <section class="tab-pane fade active in" id="tab1">
        <section id="index">
		<a href="https://docs.google.com/presentation/d/1co5QR58Z4TwyD6MY68BC_LAEsQ49Zo4JAJ-XwlZxCOE/edit?usp=sharing" target= "_blank" style="position:absolute;top:5px;right:10px;font-weight:bold;color:#FE9A2E">Help?</a>
        <button type="button" id="toggle-question-status" style = "height:25px;display:none">Q/A Status</button>
		<button type="button" id="hidden-question-status" style = "display:none"></button>
        </section>
      </section>
      <section class="tab-pane fade " id="tab3">
        <div id="chart2">
          <p>This feature is under-development.<br />Are you interested on contributing to it?<br />Contact Julio at jdg60@pitt.edu</p>
          <!-- <div id="chart2Btn"><a href="#">0</a> <a href="#">1</a> <a href="#">2</a></div>  -->
        </div>
        <!--div id="guidelines"> <strong>Navigate lecture readings by browsing the sections in top circle.</strong>
          <ul>
            <li>By <b>clicking on a subsection</b>, you will open a dialog that show links to your lectures.</li>
            <li> By <b>mouse over</b>, you will compare your progress to your peers' progress (P1, P2 and P3) in the circles
              beneath this text
            <li> Use your <b>mouse wheel over the circle</b>, to zoom in and out. You can also <b>drag</b> the circle.
          </ul>
        </div-->
      </section>
      <section class="tab-pane fade" id="tab4">
        <section id="self-comp">
        <p>This feature is under-development.<br />Are you interested on contributing to it?<br />Contact Julio at jdg60@pitt.edu</p>
        </section>
      </section>
    </section>
  </section>
</section>
<script id="sunid" type="text/javascript" src="small-multiples.js"></script> 
<script type='text/javascript' src='js/jquery.simplemodal.js'></script> 
<script type='text/javascript' src='js/basic.js'></script> 
<script type='text/javascript' src='indexTree.js'></script> 
<script type='text/javascript' src='selfcompare.js'></script> 
<script type="text/javascript" src="chosen/chosen.jquery.js"></script>

