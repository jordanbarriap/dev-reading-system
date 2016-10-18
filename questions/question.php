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

var qsParent = getQueryVariables(parent.parent.window);
var usr = qsParent['usr'];
var grp = qsParent['grp'];
var sid = qsParent['sid'];
if (!sid) {
	// happens when using interface when not logged in through Knowledge Tree
	// Alternatively throw error (i.e., don't allow usage without going through portal)
	sid = null;
}

var addQuestion = function(question, answers, prefix) {
	var div = document.createElement('div');
	div.classList.add('question')
	var questionElement = document.createElement('h3');
	var questionContent = question.trim().charAt(0).toUpperCase() + question.trim().slice(1);
	questionElement.textContent = prefix + questionContent;
	div.appendChild(questionElement);
    for (var i = 0; i < answers.length; i++) {
    	var label = document.createElement('label');
    	var input = document.createElement('input');
    	input.classList.add('answer')
    	input.type = "checkbox";
    	label.appendChild(input);
		answerContent = answers[i].charAt(0).toUpperCase() + answers[i].slice(1);
    	var answerText = document.createTextNode(answerContent);
    	label.appendChild(answerText);
    	div.appendChild(label);
    	var br = document.createElement('br');
    	div.appendChild(br);
    }
	
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
        'answers': answers
    };
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
	    		   console.log($("#hidden-question-status"));
	    		   parent.document.getElementById("hidden-question-status").click();//added by jbarriapineda in 29-09
	    	   } else if (status === 1) {
	    		   questionImg.src = "q-wrong.png";
	    		   var message = "Incorrect. Try again.";
	    		   if (numQuestions() > 1) {
	    			   var incorrect = data['incorrect'];
	                   // change to 1-based indexing
	                   incorrect = incorrect.map(function(x){return x+1;});
	                   // to string
	                   incorrect = incorrect.join(', ');
	                   message = "Incorrect (" + incorrect + "). Try again.";
	    		   }
	    		   
	    		   statusMessage(message);
	    	   } else if (status === 2) {
	    		   statusMessage("At least one selection required.");
	    	   }
	    	   //parent.updateQuestionStatus(status);
	      }
	 });
};

document.getElementById('submit').addEventListener('click', submit);

// get questions (and last answer)

jQuery.ajax({
       url: 'api.php',
       data: {'task': 'subsectionquestions', 'docid': docid, 'docids': docids},
       dataType: "json",
       success: function(data) {
    	   for (var i = 0; i < data.length; i++) {
    		   var question = data[i];
    		   var prefix = '';
    		   if (data.length > 1) {
    			   prefix = (i+1) + '. ';
    		   }
    		   
    		   addQuestion(question['question'], question['answers'], prefix);
    	   }
    	   
    	   // now get last answer
    	   // could change api to fetch questions and last answer at once,
    	   // but this keeps things simpler
    	   jQuery.ajax({
    		   url: 'api.php',
    	       data: {'task': 'subsectionlastanswer', 'docid': docid, 'docids': docids, 'usr': usr, 'grp': grp},
    	       dataType: "json",
    	       success: function(data) {
    	    	   setAnswers(data);
    	       }
    	   });
      }
});
	
</script>
	
</body>
</html>
