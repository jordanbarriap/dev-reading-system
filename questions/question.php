<html>
<head>
<link rel="stylesheet" href="style.css?v=<?php echo time();?>">
<script src='../js/jquery.js' type='text/javascript'></script>
</head>
<body>
    <span id="close-btn">X</span>
    <img id="question-img" src="q-right.png">
	<h3 style = "color:red">Now, please answer the following questions before you read the next section.</h3>
    <div id="questions"></div>
    <br>
    <button type="button" id="submit">Submit</button>
    <span id="status"></span>
<script>

document.getElementById('close-btn').addEventListener('click', parent.toggleQuestion);

var ESC = 27;
document.body.addEventListener('keydown', function(e) {
	var keyCode = e.keyCode;
	if (keyCode === ESC && parent.questionOpen()) {
		parent.toggleQuestion();
		e.stopPropagation();
	}
});

var questionImg = document.getElementById("question-img");
// not a good approach :-(
questionImg.src = parent.document.getElementById("question-img").src;

var getQueryVariables = function(w) {
    var query = w.location.search.substring(1);
    var vars = query.split('&');
    var qs = {};
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        qs[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
    }
    return qs;
}

var qs = getQueryVariables(window);
var docid = qs['docid'];
var docids = qs['docids'];

var filename=qs['filename'];//added by jbarriapineda in 10-23
var questionmode=qs['questionMode'];//added by jbarriapineda in 11-03
var subdocids=qs['subdocids'];//added by jbarriapineda in 11-03

var qsParent = getQueryVariables(parent.parent.window);
var usr = qsParent['usr'];
var grp = qsParent['grp'];
var sid = qsParent['sid'];

if (!sid) {
	// happens when using interface when not logged in through Knowledge Tree
	// Alternatively throw error (i.e., don't allow usage without going through portal)
	sid = null;
}

var addQuestion = function(question, answers, prefix,correct,n_attempts) {
	var div = document.createElement('div');
	div.classList.add('question')
	if(correct==0){
		var correctImg = document.createElement("img");
		correctImg.classList.add("status-correct");
		correctImg.classList.add("status");
		//correctImg.src="../img/correct.png";
		div.appendChild(correctImg);
	}
	if(correct==1){
		var correctImg = document.createElement("img");
		correctImg.classList.add("status-incorrect");
		correctImg.classList.add("status");
		//correctImg.src="../img/incorrect.png";
		div.appendChild(correctImg);
	}
	if(correct==2){
		var correctImg = document.createElement("img");
		correctImg.classList.add("status-non-answered");
		correctImg.classList.add("status");
		//correctImg.src="../img/incorrect.png";
		div.appendChild(correctImg);
	}
	var questionElement = document.createElement('h3');
	var attempts_info = document.createElement('div');
	attempts_info.setAttribute( 'class', 'attempts-div');
	//if(n_attempts>0){
	var questionContent = question.trim().charAt(0).toUpperCase() + question.trim().slice(1);
	attempts_info.innerHTML=generateAttemptsMsg(correct,n_attempts);
	/*}else{
		var questionContent = question.trim().charAt(0).toUpperCase() + question.trim().slice(1);
		attempts_info.innerHTML=" "+n_attempts+"/2 attempts";
	}*/
	
	/*if (correct==0){
		questionElement.textContent = prefix + '<img src="../img/correct.png" alt="Correct answer" class="status-correct">' + questionContent;
	}else{*/
	questionElement.textContent = prefix +  questionContent ;
	//}
	
	div.appendChild(questionElement);
	/*if(n_attempts>0){*/
		div.appendChild(attempts_info);
	/*}*/

	var br1 = document.createElement('br');
    div.appendChild(br1);

    for (var i = 0; i < answers.length; i++) {
    	var label = document.createElement('label');
    	var input = document.createElement('input');
    	input.classList.add('answer')
    	input.type = "checkbox";
    	if(correct==0){
    		input.disabled=true;
    	}
    	label.appendChild(input);
		answerContent = answers[i].charAt(0).toUpperCase() + answers[i].slice(1);
    	var answerText = document.createTextNode(answerContent);
    	label.appendChild(answerText);
    	div.appendChild(label);
    	var br2 = document.createElement('br');
    	div.appendChild(br2);
    }
    var br3 = document.createElement('br');
    div.appendChild(br3);
    document.getElementById('questions').appendChild(div);
};

var curTimer = null;
var statusMessage = function(message, time) {
    time = (typeof time === 'undefined') ? 1500 : time;
    var element = document.getElementById("status");
    if (curTimer)
    	clearTimeout(curTimer);
    element.textContent = message;
    var timer = setTimeout(function() {
    	element.textContent = "";
    	curTimer = null;
    }, time);
    curTimer = timer;
};

var numQuestions = function() {
	return document.getElementsByClassName('question').length;
};

var getAnswers = function() {
	var arr = [];
	var questions = document.getElementsByClassName('question');
	for (var i = 0; i < questions.length; i++) {
		var a = [];
		var q = questions[i];
		var answers = q.getElementsByClassName('answer');
		for (var j = 0; j < answers.length; j++) {
			var answer = answers[j];
			if (answer.checked) {
				a.push(j);
			}
		}
		arr.push(a);
	}
	return arr;
};

var setAnswers = function(arr) {
	var questions = document.getElementsByClassName('question');
	for (var i = 0; i < arr.length; i++) {
		var q = questions[i];
		var a = arr[i];
		var answers = q.getElementsByClassName('answer');
		for (var j = 0; j < a.length; j++) {
			answers[a[j]].checked = true;
		}
	}
}

var submit = function() {
	var answers = getAnswers();
	var data = {
        'docid': docid,
		'docids': docids,
        'usr': usr,
        'grp': grp,
        'sid': sid,
        'answers': answers,
        'filename':filename,
        'questionmode':questionmode,
        'subdocids':subdocids
    };
    console.log(data);//added by jbarriapineda in 10-22
    console.log(parent.parent.pendingAnswers);//added by jbarriapineda 
    parent.parent.pendingAnswers=-1;
	jQuery.ajax({
	       url: 'api.php?task=subsectionsubmit',
	       type: 'POST',
	       data: JSON.stringify(data),
	       contentType: 'application/json',
	       dataType: "json",
	       success: function(data) {
	    	   var status = data['status'];
	    	   if (status === 0) {
	    		   questionImg.src = "q-right.png";
	    		   statusMessage("Correct!");
	    		   //console.log($("#hidden-question-status"));
	    		   $(".status").removeClass().addClass("status status-correct");//change all questions to show a correct icon
	    		   $(".question").find("input").attr("disabled", true);
	    		   $("#submit").attr('disabled','disabled');//disable submit button
	    		   var n_attempts = data["n_attempts"];
	    		   for(var i=1;i<=$("#questions").children().length;i++){//disable checkbox for correct questions
	    		   		var ith_attempts=n_attempts[i-1];
	    		   		$($("#questions").children()[i-1]).find(".attempts-div").html(generateAttemptsMsg(0,ith_attempts));//updates attempts message
	    		   }
	    	   } else if (status === 1) {
	    		   questionImg.src = "q-wrong.png";
	    		   var message = "Incorrect. Try again.";
	    		   if (numQuestions() >= 1) {
	    			   var incorrect = data['incorrect'];
	                   // change to 1-based indexing
	                   incorrect = incorrect.map(function(x){return x+1;});
	                   var n_attempts = data["n_attempts"];
	                   for(var i=1;i<=$("#questions").children().length;i++){//disable checkbox for correct questions
	                   		var ith_attempts=n_attempts[i-1];
	                   		//console.log(ith_attempts+" actualizar globo");
	                   	    //console.log($($("#questions").children()[i-1]).find(".attempts-div"));
	                   		$($("#questions").children()[i-1]).find(".attempts-div").html("");
	                   	    if(incorrect.indexOf(i)<0){
	                   	    	$($("#questions").children()[i-1]).find("input").attr("disabled", true);//css("pointer-events","none");
	                   	    	$($("#questions").children()[i-1]).find(".status").removeClass().addClass("status status-correct");
	                   	    	$($("#questions").children()[i-1]).find(".attempts-div").html(generateAttemptsMsg(0,ith_attempts));//updates attempts message
	                   	    }else{
	                   	    	$($("#questions").children()[i-1]).find(".status").removeClass().addClass("status status-incorrect");
	                   	    	$($("#questions").children()[i-1]).find(".attempts-div").html(generateAttemptsMsg(1,ith_attempts));//updates attempts message
	                   	    }
	                   }

	                   // to string
	                   incorrect = incorrect.join(', ');
	                   message = "Incorrect (" + incorrect + "). Try again.";
	                   //Added by jbarriapineda in 01-06-2017
	    		   }
	    		   
	    		   statusMessage(message);
	    	   } else if (status === 2) {
	    		   statusMessage("At least one selection required.");
	    	   }
	    	   //console.log("Actualizar question status submit");//added by jbarriapineda in 11-08
	    	   //console.log($("#hidden-question-status").length);

	    	   //Refresh index
	    	   parent.parent.document.getElementById("hidden-question-status").click();//added by jbarriapineda in 29-09
	    	   console.log(parent.parent.document.getElementById("iframe-sun").src);
	    	   //Refresh progress visualization
	    	   parent.parent.document.getElementById("iframe-sun").src=parent.parent.document.getElementById("iframe-sun").src;//added by jbarriapineda in 01-08
	    	   //parent.updateQuestionStatus(status);
	      }
	 });
};

document.getElementById('submit').addEventListener('click', submit);

// get questions (and last answer)

jQuery.ajax({
       url: 'api.php',
       data: {'task': 'subsectionquestions', 'usr': usr, 'grp': grp, 'docid': docid, 'docids': docids, 'filename':filename, 'questionmode':questionmode,'subdocids':subdocids},
       dataType: "json",
       success: function(data) {
       	   console.log("QUESTIONS");
       	   var ncorrects=0;//added by jbarriapineda in 01-06
    	   for (var i = 0; i < data.length; i++) {
    		   var question = data[i];
    		   var prefix = '';
    		   if (data.length > 1) {
    			   prefix = (i+1) + '. ';
    		   }
    		   
    		   addQuestion(question['question'], question['answers'], prefix, question['correct'],question['n_attempts']);//modified by jbarriapineda in 01-22

    		   //added by jbarriapineda in 01-06
    		   if(question['correct']==0){
    		   	ncorrects++;
    		   }
    	   }
    	   //added by jbarriapineda in 01-06
    	   //if all answers are correct, disable submit button
    	   if(ncorrects==data.length){
    	   		document.getElementById('submit').disabled=true;
    	   }else{
    	   		document.getElementById('submit').disabled=false;
    	   }
    	   // now get last answer
    	   // could change api to fetch questions and last answer at once,
    	   // but this keeps things simpler
    	   jQuery.ajax({
    		   url: 'api.php',
    	       data: {'task': 'subsectionlastanswer', 'docid': docid, 'docids': docids, 'filename':filename, 'questionmode':questionmode, 'subdocids':subdocids, 'usr': usr, 'grp': grp},
    	       dataType: "json",
    	       success: function(data) {
    	       	   console.log("LAST ANSWERS");
    	       	   console.log(data);
    	    	   setAnswers(data);
    	       }
    	   });
      }
});

//added by jbarriapineda in 01-23
function generateAttemptsMsg(correct,n_attempts){
	var attempts_info="";
	if(n_attempts==0){
		attempts_info=" "+n_attempts+"/2 attempts";
		return attempts_info;
	}
	if(correct==0){
		if(n_attempts==1){
			//questionContent=questionContent+" ("+n_attempts+"/2 attempts) => Full credit!";
			attempts_info=" "+n_attempts+"/2 attempts &rarr; Full credit!";
		}
		else{
			if(n_attempts==2){
				//questionContent=questionContent+" ("+n_attempts+"/2 attempts) => Half credit!";
				attempts_info=" "+n_attempts+"/2 attempts &rarr; Half credit!";
			}else{
				//questionContent=questionContent+"<span class='attempt_info'> ("+n_attempts+"/2 attempts) => No credit!</span>";
				attempts_info=" "+n_attempts+"/2 attempts &rarr; No credit!";
			}
		}
	}
	if(correct==1){
		if(n_attempts<2){
			attempts_info=" "+n_attempts+"/2 attempts";
		}else{
			if(n_attempts>2){
				attempts_info=" "+n_attempts+"/2 attempts &rarr; No credit!";
			}
		}	
	}
	return attempts_info;
}
	
</script>
	
</body>
</html>
