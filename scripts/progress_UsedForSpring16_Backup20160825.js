// track reading progress

// show an overlay and don't track progress when user is inactive


var IDLE_TIMEOUT = 60000; // time in milliseconds of inactivity until considered idle
var counter = 0;

var idle = false; // is user idle?

var overlay = document.getElementById('overlay');

//is the tab/window active?
var active = true;

// disable the following until reliable
//$(window).focus(function() {
//	active = true;
//});
//
//$(window).blur(function() {
//	active = false;
//});

//need this to make blur event work as exptected on chrome 
window.focus();

var reset = function() {
	// require active to reset? If not, a mouse movement over an idle window
	// will beome not idle
	if (active) {
		counter = 0;
		if (idle)
			$(overlay).hide();
		idle = false;	
	}
}

$(document).ready(function() {
	var applyReset = function(elt) {
		// mouse events
		// I use the click() function to automatically update the index tree.
		// So, here re move elt.onclick() event for black out. mouse event
		// should be eough.
		//elt.onclick = reset;
		elt.oncontextmenu = reset;
		elt.ondblclick = reset;
		elt.onmousedown = reset;
		elt.onmouseenter = reset;
		elt.onmouseleave = reset;
		elt.onmousemove = reset;
		elt.onmouseover = reset;
		elt.onmouseout = reset;
		elt.onmouseup = reset;
		
		// keyboard events
		elt.onkeydown = reset;
		elt.onkeypress = reset;
		elt.onkeyup = reset;
		
		// wheel events
		elt.onwheel = reset;
		
		// touch events
		elt.ontouchcancel = reset;
		elt.ontouchend = reset;
		elt.ontouchmove = reset;
		elt.ontouchstart = reset;
	};
	
	// use an interval to make sure setting apply to iframes (doesn't work on first try for some reason)
	// could eventually stop the repeated applications
	setInterval(function() {
		var windows = [window];
		for (var i = 0; i < window.length; i++) {
			windows.push(window[i]);
		}
		for (var i = 0; i < windows.length; i++) {
			w = windows[i];
			applyReset(w.document);
		}
	}, 2000);
	applyReset(overlay);
});

var checkIdleTime = function() {
	counter += checkDelay;
    if (counter >= IDLE_TIMEOUT) {
    	idle = true;
    }
    
    if (!active) {
    	idle = true;
    }
    
    if (idle && !$(overlay).is(":visible")) {
    	$(overlay).fadeIn(200);
    }
};

var checkDelay = 1000; // check every second if use has become idle (based on counter)
window.setInterval(checkIdleTime, checkDelay);

//parse a query string
var parseQs = function(qs) {
	var vars = qs.split('&');
    var map = Object(null);
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        var key = decodeURIComponent(pair[0]);
        var val = decodeURIComponent(pair[1]);
        map[key] = val;
    }
    return map;
}

// it's possible for the same bookid, docno, page, position, to have multiple
// entries
var positionTimes = [];
/*
 * e.g,
 * [
 *   {
 *     bookid: lamming,
 *     docno: lamming-0244,
 *     page: 1,
 *     top: 17,
 *     bottom: 46,
 *     time: 5
 *   },
 *   {
 *     bookid: lamming,
 *     ...
 *   },
 *   ... 
 * ]
 * 
 * 
 */

var updateDelay = 1; // seconds
setInterval(function() {
	if (!idle) {
		var readings = document.getElementById('readings');
		var readingsWindow = readings.contentWindow;
		var readingsDoc = readings.contentDocument || readingsWindow.document;
		if (readingsDoc.readyState === 'complete') {
			var qs = readings.contentWindow.location.search.substring(1);
		    var map = parseQs(qs);
		    
		    var bookid = map['bookid'];
		    var docno = map['docno'];
		    var page = map['page'];
		    var question = document.getElementById('readings').contentWindow.questionOpen();
		    
		    var canvas = readingsDoc.getElementById('canvas');
		    var height = canvas.height;
		    
		    var top = document.body.scrollTop - canvas.offsetTop;
		    var bottom = top + window.innerHeight;
		    
		    top = Math.min(height, Math.max(0, top));
		    bottom = Math.min(height, Math.max(0, bottom));
		    
		    // top and bottom are between 0 and 100
		    top = (100 * top / height).toFixed(4);
		    bottom = (100 * bottom / height).toFixed(4);
		    
		    var len = positionTimes.length;
		    var last = len > 0 ? positionTimes[len-1] : null;
		    var sameAsLast = last
		      && last['bookid'] === bookid
		      && last['docno'] === docno
		      && last['page'] === page
		      && last['question'] === question
		      && last['top'] === top
		      && last['bottom'] === bottom;
		    
		    if (sameAsLast) {
		    	last['time'] += updateDelay;
		    } else {
			    var posTime = {
			      'bookid': bookid,
			      'docno': docno,
			      'page': page,
			      'question': question,
			      'top': top,
			      'bottom': bottom,
			      'time': updateDelay
			    };
			    
			    positionTimes.push(posTime);
		    }
		}
	}
}, updateDelay*1000);

var sendDelay = 10; // seconds
setInterval(function() {
	if (positionTimes.length > 0) {
		var qs = window.location.search.substring(1);
	    var map = parseQs(qs);
	    var grp = map['grp'];
	    var usr = map['usr'];
	    var sid = map['sid'];
		if (!sid) {
			// happens when using interface when not logged in through Knowledge Tree
			// Alternatively throw error (i.e., don't allow usage without going through portal)
			sid = null;
		}
	    
		var toSend = {};
		toSend['data'] = positionTimes;
		toSend['grp'] = grp;
		toSend['usr'] = usr;
		toSend['sid'] = sid;
		var json = JSON.stringify(toSend);
		$.ajax({
			url: send_progress_url,
			type: 'post',
			contentType: 'application/json',
			data: json,
			success: function(data) {
				//TODO: error checking... (error callback)
				document.getElementById("hidden-question-status").click();
			}
		});
		
		positionTimes = [];
	}
}, sendDelay*1000);


