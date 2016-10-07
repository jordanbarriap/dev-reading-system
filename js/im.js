//document.body.onload = document.body.onload + ";" + init;
var isIE = document.all;
var mouseX = 0;
var mouseY = 0;
var oC0, oC1;
var oC2;
var ctx0;
var ctx;
var ctx2;
var img;    // Create new Image object
var onMenuInput =  -99;
var preAt = -1; //highlighted box
var Drawing = -1; //trigger for drawing 0 is to disable 1 is to enable, currently unused
var url;
var oBln, oLay, oPre, oTd1, oTd2;
var JdbDown;
var pcloc = false;
var showIM = "off";
var curFileid;
var fileURL;
var myID = u_getURLvar("usr");
var loc = new String(parent.document.location);
var where = ( loc.search(/readURL.cgi/) < 0 ) ? true : false;
var myIp = true;
var nonote = "<span style='color:#777777'><i>This selection is created without a note</i></span>";
var jspeRaeder_url = "http://columbus.exp.sis.pitt.edu/jspeRaeder"; 
var annotated_url = "http://columbus.exp.sis.pitt.edu/jspeRaeder/annotated2/im/"; 
var book_path = "http://columbus.exp.sis.pitt.edu/ebooks/books";
var bookid = window.bookid; //u_getURLvar("bookid");
url = annotated_url;
var dbase = 'kseahci';
if (bookid=='tdo'){
    dbase = 'kseatdo';
}

console.log("The dbase is:"+dbase+" cause bookid = "+bookid +" and url ="+url);

myIp = "127.0.1.1";
function AtClass() {
	var docid;
	var userid;
	var groupid;
	var x1;
	var x2;
	var y1;
	var y2;
	var annotation;
	var type;
	var kind;
	var author;
	var atid;
	var fileid ="";
	var cdate;
	var atkey;
	var fileurl;
};

AtClass.prototype = {

	makeHTTP : function() {
		var sendStr = "";
		if (this.userid && this.groupid && this.docid)
		{
			sendStr = "userid=" + encodeURIComponent(this.userid) + "&groupid=" + encodeURIComponent(this.groupid) + "&docid=" + encodeURIComponent(this.docid);
			sendStr += "&x1=" + encodeURIComponent(this.x1) + "&y1=" + encodeURIComponent(this.y1) + "&x2=" + encodeURIComponent(this.x2) + "&y2=" + encodeURIComponent(this.y2);
			sendStr += "&type=" + encodeURIComponent(this.type) + "&kind=" + encodeURIComponent(this.kind) + "&author=" + encodeURIComponent(this.author) ;
			sendStr += "&annotation=" + encodeURIComponent(this.annotation) + "&atid=" + encodeURIComponent(this.atid) + "&fileid=" + encodeURIComponent(this.fileid);
			sendStr += "&cdate=" + encodeURIComponent(this.cdate)+ "&atkey=" + encodeURIComponent(this.atkey)+ "&fileurl=" + encodeURIComponent(this.fileurl);
			sendStr += "&bookid=" + encodeURIComponent(bookid) + "&dbase=" + encodeURIComponent(dbase) +  "&at_type=" + encodeURIComponent("0");
			return sendStr; 
		}
		else
		{
			console.log("failed to create a HTTP string");
			return "test1";
		} 	
	}, //function
	makeInitHTTP: function() {
		var sendStr = "";
		if (this.userid && this.groupid && this.docid)
		{
			sendStr = "userid=" + encodeURIComponent(this.userid) + "&groupid=" + encodeURIComponent(this.groupid) + "&docid=" + encodeURIComponent(this.docid);
			sendStr += "&fileid=" + encodeURIComponent(this.fileid) +	"&bookid=" + encodeURIComponent(bookid) + "&dbase=" + encodeURIComponent(dbase);
			return sendStr; 
		}
		else
		{
			console.log("failed to create a HTTP string");
			return "test2";
		} 	
	},
	setMember : function(docid, userid, groupid, x1, x2, y1, y2, annotation, type, kind, author, atid, fileid, fileurl) {
		this.docid = docid;
		this.userid = userid;
		this.groupid = groupid;
		this.x1 = x1;
		this.x2 = x2;
		this.y1 = y1;
		this.y2 = y2;
		this.annotation = annotation;
		this.type = type;
		this.kind = kind;
		this.author = author;
		this.atid = atid;
		this.fileid = fileid;
		this.fileurl = fileurl;
	}
};

function Sketch()
{
	this.isMouseDown = false;
	this.prevPt = {x: 0, y: 0};
	this.downPt = {x: 0, y: 0};
	this.upPt = {x: 0, y: 0};
	this.bbox = {left: 0, top: 0, right: 0, bottom: 0, cx: 0, cy: 0};
	this.dotCount = 0;	// # of dots
}

var sketch = new Sketch();
var oAt = new AtClass();



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajax 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function ajax_save(sendStr) {
	sendStr += "&cmd=insert";
	console.log(sendStr);
	var Ajax1 = new Ajax.Request(
		url + "dbDown187.jsp" ,
		{
			method : 'get',
			parameters : sendStr,
			onComplete : ajax_process_save,
			onException : function() {console.log("Ajax error");}
		});
}

function ajax_click(sendStr) {
	//console.log(sendStr);
	sendStr += "&cmd=inc";
	var Ajax1 = new Ajax.Request(
		url + "dbDown187.jsp" ,
		{
			method : 'get',
			parameters : sendStr,
			onComplete : ajax_process_save,
			onException : function() {console.log("Ajax error");}
		});
}


function ajax_delete(sendStr) {
	//console.log(sendStr);
	sendStr += "&cmd=delete";
	var Ajax2 = new Ajax.Request(
		url + "dbDown187.jsp" ,
		{
			method : 'get',
			parameters : sendStr,
			onComplete : ajax_process_delete,
			onException : function() {console.log("Ajax error");}
		});
}

function ajax_load(sendStr) {
	console.log(sendStr);
	var Ajax3 = new Ajax.Request(
		url + "dbDown187.jsp" ,
		{
			method : 'get',
			parameters : sendStr,
			onComplete : ajax_process_load
		});
}
function ajax_list(sendStr) {
	console.log(sendStr);
	var Ajax4 = new Ajax.Request(
		url + "dbSide187.jsp" ,
		{
			method : 'get',
			parameters : sendStr,
			onComplete : ajax_process_load
		});
}

function ajax_process_delete(originalRequest) {
	try {
		//console.log("From ServerDel:" + originalRequest.responseText);
		eval("JdbDown=" + originalRequest.responseText);
		cvs_reDraw();
		//refresh_side_list();
	} catch(e){console.log("ajax_process_del" + e);} 
}

function ajax_process_save(originalRequest) {
	try {
		console.log("From ServerSave:" + originalRequest.responseText);
		eval("JdbDown=" + originalRequest.responseText);
		cvs_reDraw();
		//refresh_side_list();
	} catch(e){console.log("ajax_process_save" + e);} 
}
function ajax_process_load(originalRequest) {
	try {
		console.log("From ServerDown:" + originalRequest.responseText);
		eval("JdbDown=" + originalRequest.responseText); 
		cvs_reDraw();
		//refresh_side_list();
	} catch(e){console.log("ajax_process_load" + e);} 
}
function refresh_side_list() {
	  hardReload = new Date()
		hardReload = url + "dbSide187.jsp?" + initHTTP() + "&" + hardReload.getTime()
		parent.frames[2].location.href = hardReload
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// End of Ajax
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function init(){ 
  console.log("Executing function Init from  im.js");
 	var loc = new String(parent.document.location);
// 	if ( myID == "ki187cm") {
		if (loc.search(/localhost:8080/) > 0) pcloc=true;
	 	if (pcloc) {
			var oDiv = document.createElement("div");
		 	oDiv.setAttribute("id", "debug");
		 	oDiv.style.cssText =  "position:absolute;left:820px;top:75px;width:680px;height:350px;" +
		 			"font-size:12px;background-color:#EEEEEE;border-width:2;border-color:black;border-style: solid;" +
		 			"visibility:hidden;";
			document.body.appendChild(oDiv); 
			console.log("Debug :");
			
			//change by Jennfier
			url = annotated_url;
			//url = "http://localhost:8080/im/";
		}
		else {
			var vis = "hidden";
			if (myID=="ki187cm") vis = "visible";
			var oDiv = document.createElement("div");
		 	oDiv.setAttribute("id", "debug");
		 	oDiv.style.cssText =  "position:absolute;left:820px;top:75px;width:680px;height:350px;" +
		 			"font-size:12px;background-color:#EEEEEE;border-width:2;border-color:black;border-style: solid;" +
		 			"visibility:hidden";// + vis;
		 			//"visibility:hidden;";
			document.body.appendChild(oDiv); 
			console.log("Debug :");
			url = annotated_url;
		}
		console.log("The url ="+url+" and the annotated_url="+annotated_url);
		
		addEvent( document, "mousedown", onMouseDown );
		addEvent( document, "mouseup", onMouseUp );
		addEvent( document, "mousemove", onMouseMove );
	
		//ck_power_img();
		
		menu_show(-1,-1000,-1000);
		menu_init_balloon();
		menu_hide();
		ajax_load(initHTTP());
 //	}
	
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////
// Canvas Setting
///////////////////////////////////////////////////////////////////////////////////////////////////////////


function cvs_set(canvas, width1, height1) {
 	canvas.setAttribute("width", width1);
 	canvas.setAttribute("height", height1);
}
 

function drawImage(filesrc) {

	var loc = new String(parent.document.location);
// 	if ( myIp == true ) {
	
		oC0 = document.getElementById("canvas");
		oC1 = document.getElementById("canvas1");
		oC2 = document.getElementById("canvas2");
		
		var bookimg = document.getElementById("image");
		var Left = bookimg.offsetLeft;
		var Top = bookimg.offsetTop;
		var Width = bookimg.width;
		var Height = bookimg.height;
		fileURL = filesrc;		
		
		ctx0 = oC0.getContext("2d"); //image
		ctx = oC1.getContext("2d"); //annotation
		ctx2 = oC2.getContext("2d"); //temp
		
		img = new Image();
		bookimg.style.visibility = "hidden";
		
		img.onload = function(){
			Left = bookimg.offsetLeft;
			Top = bookimg.offsetTop;
			Width = img.width;
			Height = img.height;
			cvs_set(oC0, Width, Height);//for faster drawing
			cvs_set(oC1, Width, Height);
			cvs_set(oC2, Width, Height);
			oC0.style.cssText =  "position:absolute;left:" + Left + "px;top:" + Top + "px;" + "width:" + Width + "px;height:" + Height + "px;border-width:1;border-color:red;border-style:solid;visibility:visible";
			oC1.style.cssText =  "position:absolute;left:" + (Left) + "px;top:" + Top + "px;" + "width:" + Width + "px;height:" + Height + "px;border-width:1;border-color:blue;border-style:solid;visibility:visible";
			oC2.style.cssText =  "position:absolute;left:" + (Left) + "px;top:" + Top + "px;" + "width:" + Width + "px;height:" + Height + "px;border-width:1;border-color:black;border-style:solid;visibility:visible";
			bookimg.style.visibility = "hidden";
			ctx0.drawImage(img,0,0);
			ajax_load(initHTTP());
			ck_power_img();
		}
		img.src = filesrc;// Set source path
		ctx2.strokeStyle = "rgb(0, 0, 255)" ;
//	}//if	
}

function cvs_clear(context) {
	context.clearRect(0,0,img.width,img.height);
}



function cvs_reDraw()
{
	var i;
	cvs_clear(ctx);
	cvs_clear(ctx2);
	if (showIM != "off") {
		var count = JdbDown.at.length;
		var prelook, beforelook; 
		for(i=0;i<count;i++) {
			if (i < count-1) {
				prelook = (JdbDown.at[i].atid == JdbDown.at[i+1].atid);
			}
			else 
				prelook = false;
			if (i==0) {
				beforelook = false;
			}
			else {
				beforelook = (JdbDown.at[i-1].atid == JdbDown.at[i].atid);
			}

			if (beforelook == false) { //first annotation
				cvs_draw_rect(i);
			} else { // childs memos
			}
			if ( prelook == false) { //closing
			}			
		}
	}
}
function cvs_draw_icon(index, alpha) {
	var x1, x2, y1, y2;
	var r, g, b;
	var ih = 12 , iw = 10, cell = 10;
	var sp = 0, un = 1, fa = 0.9, fill=0.0;
	x1 = parseInt(JdbDown.at[index].x1);
	x2 = parseInt(JdbDown.at[index].x2);
	y1 = parseInt(JdbDown.at[index].y1);
	y2 = parseInt(JdbDown.at[index].y2);

	var kind = 0, numat = 0;
	for(var i=0;i<JdbDown.at.length;i++) {
		if (JdbDown.at[index].atid == JdbDown.at[i].atid) {
			numat++;
			if (JdbDown.at[i].type == "praise") {
				kind++;
			}
		}
	}


	ctx.save();
	
	// praised
	ctx.lineWidth = 2;
	ctx.strokeStyle = "rgba(255,0,0," + alpha + ")";
	ctx.fillStyle = "rgba(255,100,100," + fa + ")";
	ctx.strokeRect(x1+x2-iw*un-sp, y1+y2+3, iw, ih);
	fill = kind/0.3 > 10 ? 10 : kind/0.3;
	ctx.fillRect(x1+x2-iw*un-sp, y1+y2+4+(cell-fill), iw, fill);
	un++;
	sp+=2;

	// # of annotation
	ctx.strokeStyle = "rgba(0,0,255," + alpha + ")";
	ctx.fillStyle = "rgba(100,100,255," + fa + ")";
	ctx.strokeRect(x1+x2-iw*un-sp, y1+y2+3, iw, ih);
	fill = numat/0.3 > 10 ? 10 : numat/0.3;
	ctx.fillRect(x1+x2-iw*un-sp, y1+y2+4+(cell-fill), iw, fill);
	un++;
	sp+=2;

	// group click
	ctx.strokeStyle = "rgba(0,255,255," + alpha + ")";
	ctx.fillStyle = "rgba(100,255,255," + fa + ")";
	ctx.strokeRect(x1+x2-iw*un-sp, y1+y2+3, iw, ih);
	fill = parseInt(JdbDown.at[index].gclick)/1.0 > 10 ? 10 : parseInt(JdbDown.at[index].gclick)/1.0;
	ctx.fillRect(x1+x2-iw*un-sp, y1+y2+4+(cell-fill), iw, fill+1);
	un++;
	sp+=2;
/*
	// searched
	ctx.strokeStyle = "rgba(255,255,0," + alpha + ")";
	ctx.fillStyle = "rgba(255,255,0," + fa + ")";
	ctx.strokeRect(x1+x2-iw*un-sp, y1+y2+3, iw, ih);
	fill = 0;
	ctx.fillRect(x1+x2-iw*un-sp, y1+y2+4+(cell-fill), iw, fill+1);
	un++;
	sp+=2;
*/
	// posed
	ctx.strokeStyle = "rgba(255,0,255," + alpha + ")";
	ctx.fillStyle = "rgba(255,0,255," + fa + ")";
	ctx.strokeRect(x1+x2-iw*un-sp, y1+y2+3, iw, ih);
	fill = 0;
	ctx.fillRect(x1+x2-iw*un-sp, y1+y2+4+(cell-fill), iw, fill+1);
	un++;
	sp+=2;
	ctx.restore();
}

function get_praised(index) {
	var count = JdbDown.at.length;
	var pCount = 0; 
	for(i=0;i<count;i++) {
		if ( (JdbDown.at[index].atid == JdbDown.at[i].atid) && (JdbDown.at[i].type == "praise") && (JdbDown.at[i].userid != myID) ) {
			pCount++;
		}
	}
	return pCount;
}

function get_praised_by_me(index) {
	var count = JdbDown.at.length;
	var pCount = 0; 
	for(i=0;i<count;i++) {
		if ( (JdbDown.at[index].atid == JdbDown.at[i].atid) && (JdbDown.at[i].type == "praise") && (JdbDown.at[i].userid == myID) ) {
			pCount++;
		}
	}
	return pCount;
}

function get_memo(index) {
	var count = JdbDown.at.length;
	var pCount = 0; 
	for(i=0;i<count;i++) {
		if ( (JdbDown.at[index].atid == JdbDown.at[i].atid) && (JdbDown.at[i].userid != myID) ) {
			pCount++;
		}
	}
	return pCount;
}
function get_mynote(index) {
	var count = JdbDown.at.length;
	var pCount = 0; 
	for(i=0;i<count;i++) {
		if ( (JdbDown.at[index].atid == JdbDown.at[i].atid) && (JdbDown.at[i].userid == myID) ) {
			pCount++;
		}
	}
	return pCount;
}

function cvs_draw_rect(index) {
	var x1, x2, y1, y2;
	var myId = u_getURLvar("usr");
	var adjust=1,alpha, r, g, b;
	var offset= 2, layer = true;
	x1 = parseInt(JdbDown.at[index].x1);
	x2 = parseInt(JdbDown.at[index].x2);
	y1 = parseInt(JdbDown.at[index].y1);
	y2 = parseInt(JdbDown.at[index].y2);
	var nMemo = get_memo(index);

	if ( (((myId != JdbDown.at[index].userid) && (showIM == "me")) == false) || (showIM == "all") ) {

		ctx.lineWidth = 1;
		alpha =0.9;
		ctx.strokeStyle = "rgb(255,125,0)";
		r = 255; g = 220; b = 0; 
		
/*		//click
		if (JdbDown.at[index].gclick >= 0 && JdbDown.at[index].gclick < 10) {
			ctx.lineWidth = 2;
		} else if (JdbDown.at[index].gclick >= 10 && JdbDown.at[index].gclick < 20) {
			ctx.lineWidth = 3;
		} else if (JdbDown.at[index].gclick >= 20 && JdbDown.at[index].gclick < 30) {
			ctx.lineWidth = 4;
		} else if (JdbDown.at[index].gclick >= 30) {
			ctx.lineWidth = 5;
		}
*/


		//comment
		if (nMemo > 1) {
			nMemo = nMemo > 6 ? 6 : nMemo;
			//experiment 
			nMemo++;
			ctx.lineWidth = nMemo;
		} else {
			ctx.lineWidth = 1;
		}
		
		//me & other
		//if (myId == JdbDown.at[index].userid) {
		if (get_mynote(index) > 0) {
			if (get_praised_by_me(index)) {
				ctx.fillStyle = "rgba(0,255,0,0.15)";
			} else {
				ctx.fillStyle = "rgba(225,225,0,0.15)";
			}
			ctx.fillRect(x1,y1,x2,y2);
		}
		
		/*
		//memo
		if (1<nMemo) {
			nMemo = nMemo > 10 ? 10 : nMemo;
			nMemo = nMemo / 30; 
			/*var min = 0.0;
			var max = 8.0;	
			r = nMemo <= ((min+max)/2) ? parseInt(255 - (nMemo-1)/((min+max)/2) * 255) : 0;
			if (nMemo <= ((min+max)/2)) {
				g = parseInt((nMemo-1)/((min+max)/2) * 255);
			} else if (nMemo > ((min+max)/2)){
				g = parseInt(500 - (nMemo-1)/(min+max) * 500);
			}
			b = nMemo >= ((min+max)/2) ? parseInt(-255 + (nMemo-1)/((min+max)/2) * 255) : 0;
			a = 0.1;
			ctx.fillStyle = "rgba(" + r + "," + g + "," + b + "," + a + ")";
			*/
//			ctx.fillStyle = "rgba(0,100,255," + nMemo + ")";
//			ctx.fillRect(x1,y1,x2,y2);
//		}

	
			
		//public/private
		/*if (JdbDown.at[index].kind == "0") {
			r = parseInt(255/adjust);
			g = parseInt(180/adjust);
			b = parseInt(0/adjust);
		} else if (JdbDown.at[index].kind == "1") {
			r = parseInt(255/adjust);
			g = parseInt(0/adjust);
			b = parseInt(255/adjust);
		} else if (JdbDown.at[index].kind == "2") {
			r = parseInt(255/adjust);
			g = parseInt(0/adjust);
			b = parseInt(0/adjust);
		}*/
		
		//praised	
		if (get_praised(index) > 0) {
			r = parseInt(0/adjust);
			g = parseInt(255/adjust);
			b = parseInt(0/adjust);
			alpha = get_praised(index) ;
		} 
	
		ctx.strokeStyle = "rgba("+ r + " ," + g + " ," + b + "," + alpha + ")";
		ctx.strokeRect(x1+0.5, y1+0.5, x2, y2);
	
		//public/private
		if (JdbDown.at[index].kind == "1") {
			ctx.save();
			offset = 0.5;
			var dash = 0;
			var inc = 3;
			var spc = 7;
			ctx.strokeStyle = "rgba(255,255,255,1)";
			ctx.beginPath();
			while(dash < x2) {
				ctx.moveTo(x1+dash+offset, y1+y2+offset);
				dash += inc;
				ctx.lineTo(x1+dash+offset, y1+y2+offset);
				dash += spc;
			}
			dash=0;
			while(dash < x2) {
				ctx.moveTo(x1+dash+offset, y1+offset);
				dash += inc;
				ctx.lineTo(x1+dash+offset, y1+offset);
				dash += spc;
			}
			dash=0;
			while(dash < y2) {
				dash += spc;
				ctx.moveTo(x1+offset, y1+dash+offset);
				dash += inc;
				ctx.lineTo(x1+offset, y1+dash+offset);
			}
			dash=0;
			while(dash < y2) {
				dash += spc;
				ctx.moveTo(x1+x2+offset, y1+dash+offset);
				dash += inc;
				ctx.lineTo(x1+x2+offset, y1+dash+offset);
			}
			ctx.stroke();
			ctx.restore();
		}
	
		//cvs_draw_icon(index, alpha);
	
		//resetting
		ctx.strokeStyle = "rgb(255,125,0)";
		ctx.lineWidth = 2;
		
		//ctx.restore();
	}
	
}

function cvs_select_rect(ctx, x1, y1, x2, y2) {
	ctx.lineWidth = 4;
	ctx.strokeStyle = "rgb(00,00,255)";
	ctx.fillStyle = "rgba(255,255,0,0.2)";
	ctx.fillRect(parseInt(x1), parseInt(y1), parseInt(x2), parseInt(y2));
	ctx.strokeRect(parseInt(x1), parseInt(y1), parseInt(x2)-1, parseInt(y2)-1);
	ctx.lineWidth = 2;
}

function side_select_byid(id) {
	var i = 0;
	for(i=0;i<JdbDown.at.length;i++) {
		if (JdbDown.at[i].atid == id) {
			cvs_select_rect(ctx2, JdbDown.at[i].x1, JdbDown.at[i].y1, JdbDown.at[i].x2, JdbDown.at[i].y2 );
			menu_show_balloon(i)
			break;
		}
	}

}
function side_clear_byid(id) {
	ctx2.clearRect(0,0,img.width,img.height);
	menu_show_balloon(-1);
}

function side_show_menu(id) {
	var i = 0;
			menu_hide();

	for(i=0;i<JdbDown.at.length;i++) {
		if (JdbDown.at[i].atid == id) {
			var x = parseInt(JdbDown.at[i].x1);
			var y = parseInt(JdbDown.at[i].y2) + parseInt(JdbDown.at[i].y1);
			menu_show(i , x, y);
			break;
		}
	}
}


function side_get_blogdata(id) {
	var i = 0;
	var title, body="";
	var x1, y1, x2, y2;
	var w, h;
	var book_url;
	var valid = 0;
	var fileid;
	// @@@@
	if (pcloc) {
		book_url = book_path + "/tdo/";
	} else {
		//book_url = "http://ir.exp.sis.pitt.edu/textbooks/hci-books/newman/";
		book_url = book_path + "/tdo/";
	}
	for(i=JdbDown.at.length-1;i>=0;i--) {
		if (JdbDown.at[i].atid == id) {
			x1 = parseInt(JdbDown.at[i].x1);
			x2 = parseInt(JdbDown.at[i].x2);
			y1 = parseInt(JdbDown.at[i].y1);
			y2 = parseInt(JdbDown.at[i].y2);
			w = (x2>400) ? 400 : x2;
			h = (y2>250) ? 250 : y2;
			body += "<canvas id='cvs' style='background-color:#EEEEEE;border-width:1;border-color:gray;border-style:solid;' width='" + w + "' height='" + h + "'></canvas>\n"
			body += "<script src='./excanvas.js'></script>";  
			body += "<script>x1=" + x1 + ";x2=" + x2 + ";y1=" + y1 + ";y2=" + y2 + ";fileid='" + JdbDown.at[i].fileid; 
			body += "';url='" + book_url + "'</script>";
			body += "<script src='./clipblog.js'></script>";
			fileid = JdbDown.at[i].fileid;
			
			title = JdbDown.at[i].annotation;
			if (title.length > 50) {
				title = title.substring(0, 50) + " ...";
			} 			
			if (title)
			valid = 1;	
			break;
		}
	}
	var utype;
	var ukind;
	var uauthor;
	var udate;
	
	switch (parseInt(JdbDown.at[i].kind)) {
		case 0:
		ukind = "Public";
		break;
		case 1:
		ukind = "Private";
		break;
		case 2:
		ukind = "Teacher";
		break;
		default:
		ukind = "Unknown";
		break;
	}
	if (JdbDown.at[i].type == "praise") {
		utype = "<font color=red>Praised</font>";
	} 
	else {
		utype = "General"
	}
	if (JdbDown.at[i].author == "anon") {
 		uauthor += "Anonymous";
	}
	else {
 		uauthor = JdbDown.at[i].author;
	}
	udate = JdbDown.at[i].cdate;
	
	body = "<table>"	

	for(i=JdbDown.at.length-1;i>=0;i--) {
		if (JdbDown.at[i].atid == id) {
			body += "<tr><td class='memo'>" + JdbDown.at[i].annotation + "</td></tr>";			
			body += "<tr><td class='info'>" + utype + ", " + ukind  + " Comment Created by " + uauthor  + "<br/>" + udate +"</td></tr>";
		}
	}
	body += "</table>";	

	if (valid==1) {
//		window.open('./blog.html','Blog', 'width=450, height=550')   
		window.open('./blog.html?'  + "w=" + w + "&h=" + h + "&body=" + encodeURIComponent(body) + "&title=" + encodeURIComponent(title) +  "&x1=" + x1 + "&x2=" + x2 + "&y1=" + y1 + "&y2=" + y2 + "&url=" + encodeURIComponent(book_url) + "&fileid=" + encodeURIComponent(fileid), 'Blog', 'width=450, height=350')
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////
// Mouse Event
///////////////////////////////////////////////////////////////////////////////////////////////////////////

function onMouseDown(e)
{
	if (!e) e = window.event;

	if (e.which == null)
		button= (e.button < 2) ? "LEFT" : ((e.button == 4) ? "MIDDLE" : "RIGHT");
	else
		button= (e.which < 2) ? "LEFT" : ((e.which == 2) ? "MIDDLE" : "RIGHT");

	if (button == "LEFT") {
		var pt = {x:0, y:0};// = getMousePos();
		pt.x = (isIE ? (e.clientX + document.body.scrollLeft) : e.pageX) - oC0.offsetLeft;
		pt.y = (isIE ? (e.clientY + document.body.scrollTop) : e.pageY) - oC0.offsetTop;
		if (isClickonCanvas(pt.x, pt.y) && (showIM != "off") ) {
			if (onMenuInput < 0) {
				sketch.curPt = pt;
				sketch.isMouseDown = true;
				sketch.downPt = sketch.curPt;
			}
		} else {
			if (onMenuInput > -1) {
				menu_hide();
			}
		}
	}

}


function onMouseMove(e)
{
	if (onMenuInput < -1 && (showIM != "off")) {
		if (!e) e = window.event;
		var pt = {x:0, y:0};// = getMousePos();
		pt.x = (isIE ? (e.clientX + document.body.scrollLeft) : e.pageX) - oC0.offsetLeft;
		pt.y = (isIE ? (e.clientY + document.body.scrollTop) : e.pageY) - oC0.offsetTop;
		sketch.curPt = pt;
		if( sketch.isMouseDown ) { // draw rect
			if( u_abs(sketch.curPt.x - sketch.prevPt.x) > 5 || u_abs(sketch.curPt.y - sketch.prevPt.y) > 5 ) {
				ctx2.strokeStyle = "rgb(0, 0, 255)" ;
				ctx2.clearRect(0,0,img.width,img.height);
				ctx2.strokeRect(sketch.downPt.x,sketch.downPt.y, sketch.curPt.x - sketch.downPt.x, sketch.curPt.y - sketch.downPt.y);
				Drawing = 1;
				preAt = -1;
			} else {
				return;
			}
		}
		else { //find box to highlight
			findHighlightBox(pt);
		}
		sketch.prevt = sketch.curPt;
	} // if onmenu	
}


function onMouseUp(e)
{
	if (!e) e = window.event;
	if (e.which == null)
		button= (e.button < 2) ? "LEFT" : ((e.button == 4) ? "MIDDLE" : "RIGHT");
	else
		button= (e.which < 2) ? "LEFT" : ((e.which == 2) ? "MIDDLE" : "RIGHT");


	if (button == "LEFT") {
	
		var pt = {x:0, y:0};// = getMousePos();
		pt.x = (isIE ? (e.clientX + document.body.scrollLeft) : e.pageX) - oC0.offsetLeft;
		pt.y = (isIE ? (e.clientY + document.body.scrollTop) : e.pageY) - oC0.offsetTop;
		sketch.curPt = pt;
		sketch.upPt = sketch.curPt;
		if (sketch.isMouseDown){
			if (preAt >= 0) {
				x = parseInt(JdbDown.at[preAt].x1);
				y = parseInt(JdbDown.at[preAt].y2)+parseInt(JdbDown.at[preAt].y1);
				menu_show(preAt, x, y);
			}
			else { //confirm drawing
				ctx2.clearRect(0,0,img.width,img.height);
				var x1, x2, y1, y2;
				x1 = sketch.downPt.x;
				y1 = sketch.downPt.y;
				x2 = sketch.curPt.x - sketch.downPt.x;
				y2 = sketch.curPt.y - sketch.downPt.y;
				if (u_abs(x2>30) && u_abs(y2>15)) {
					ctx2.strokeRect(x1,y1,x2,y2);
					oAt.x1 = x1; oAt.x2 = x2; oAt.y1 = y1; oAt.y2 = y2;
					menu_show(-1, x1, y1+y2);
				}  // if abs
			} //if pre
		}
	
		sketch.isMouseDown = false;
	}	
	
}

	// addEvent() - event handler add


function addEvent( object, event, listener ) 
{ 
    if ( object.addEventListener ){ 
        object.addEventListener( event, listener, false ); 
    } else if ( object.attachEvent ){ 
        object.attachEvent( "on" + event, listener ); 
    } 
}



function findHighlightBox(pt) {

	Drawing = 0;
	var priority = -1;
	var candAt = new Array();
	
	if (JdbDown != null) {
		for(var i=JdbDown.at.length-1;i>=0;i--) {
			if ( ((JdbDown.at[i].userid == myID) && (showIM=="me")) || (showIM=="all") ) {
				if ((pt.x > JdbDown.at[i].x1) && (JdbDown.at[i].x2 > (pt.x - JdbDown.at[i].x1)) && (JdbDown.at[i].y1 < pt.y) && ((pt.y - JdbDown.at[i].y1) < JdbDown.at[i].y2)) {
					candAt.push(i);
				}
			}
		} //for
		preAt = decideAt(candAt);
		if (preAt>-1) {
			ctx2.clearRect(0,0,img.width,img.height);
			cvs_select_rect(ctx2, JdbDown.at[preAt].x1, JdbDown.at[preAt].y1, JdbDown.at[preAt].x2, JdbDown.at[preAt].y2);
			oC2.style.cursor = "hand";
			menu_show_balloon(preAt);
		} else {
			ctx2.lineWidth = 2;
			ctx2.clearRect(0,0,img.width,img.height);
			oC2.style.cursor = "";
			menu_show_balloon(preAt);
		}
	}
}


function decideAt(candAt) {
	if (candAt.length>0) {
		var x1 = parseInt(JdbDown.at[candAt[0]].x1);
		var x2 = parseInt(JdbDown.at[candAt[0]].x2);
		var y1 = parseInt(JdbDown.at[candAt[0]].y1);
		var y2 = parseInt(JdbDown.at[candAt[0]].y2);
		var atid = candAt[0];//JdbDown.at[candAt[0]].atid;
		for(var i=0;i<candAt.length;i++) {
			if ( x1 < parseInt(JdbDown.at[candAt[i]].x1) && parseInt(JdbDown.at[candAt[i]].x2) < x2 && 
				y1 < parseInt(JdbDown.at[candAt[i]].y1) && parseInt(JdbDown.at[candAt[i]].y2) < y2 )  {
				x1 = JdbDown.at[candAt[i]].x1;
				x2 = JdbDown.at[candAt[i]].x2;
				y1 = JdbDown.at[candAt[i]].y1;
				y2 = JdbDown.at[candAt[i]].y2;
				atid = candAt[i];//JdbDown.at[i].atid;
			}
		}
		return atid;
	} else {
		return -1;
	}
	
}



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////// menu
function menu_show_balloon(preAt) {
	if (preAt >=0 ) {
		var offx = 7, offy = 8;
		var atcount = 0;
		for(var i=0;i<JdbDown.at.length;i++) {
			if (JdbDown.at[preAt].atid == JdbDown.at[i].atid) {
				atcount++;
			}
		}
		var memo = JdbDown.at[preAt].annotation;
		
		if (memo != nonote) {
			memo = memo.replace(/<br\/>/g, " ");
			if (memo.length > 25) {
				memo = memo.substring(0, 25) + " ...";
			} 
		} else {
			memo = "<span style='color:#777777'><i>currently no note</i></span>";
			atcount = 0;
		}
		var tdstyle1 = "text-align:left;font-family:Tahoma;font-size:12px;width:180;";
 		var tdstyle2 = "text-align:right;font-family:Tahoma;font-size:12px;width:180;";
		
		
		oLay.innerHTML = "<table><tr><td style='" + tdstyle1 +  "'>" + memo + "</td></tr>" +
		 "<tr><td style='" + tdstyle2 +  "'>" + atcount + " comment(s)</td></tr></table>";

	 	oBln.style.left = parseInt(JdbDown.at[preAt].x1) + document.getElementById("canvas").offsetLeft;
	 	oBln.style.top = parseInt(JdbDown.at[preAt].y1) + document.getElementById("canvas").offsetTop  - parseInt(oBln.style.height);
	 	oLay.style.left = parseInt(oBln.style.left) + offx;
	 	oLay.style.top = parseInt(oBln.style.top) + offy;
	 	oBln.style.visibility = "visible";
	 	oLay.style.visibility = "visible";
	}
	else {
 		oBln.style.visibility = "hidden";
	 	oLay.style.visibility = "hidden";
	}
	
}

function menu_init_balloon() {
	oLay = document.createElement("div");
	oBln = document.createElement("img");
 	
 	oBln.setAttribute("src", url + "image/balloon.gif");
	
 	oBln.style.cssText =  "position:absolute;left:30px;top:115px;height:63px;width:198px;" +
 			"font-size:12px;border-width:0;border-color:black;border-style: solid;" +
 			"visibility:hidden;z-index:50";
 	oLay.style.cssText =  "position:absolute;left:40px;top:120px;height:60px;width:190px;" +
 			"font-size:12px;border-width:0;border-color:black;border-style: solid;" +
 			"visibility:hidden;z-index:51";
	document.body.appendChild(oBln); 
	document.body.appendChild(oLay); 
	
}

function menu_teacher() {
	if (myID == "peterb" || myID == "ki187cm" || myID == "rosta" ) {
		return "<td style='font-family:Tahoma;font-size:13px;'><input id='kind3' type='radio' value='2' name='kind'>Teacher</td>" + "\n";

	} else {
		return "";

	}
}

function menu_show(atid, x, y)
{
	onMenuInput = atid;	
 	var mHeight = 320;
 	var mWidth = 300;
 	var mComments = "";
 	var mCreated = "";
 	var imauthor = false;
 	var oMenu = null;
 	var oComm = null;
 	var tDiv = null;
	var FirstElement = null;
	var v = null;
	var tFrame = null;
	var doc = null;
 	var i = 0;

	
	mCreated = "<table width=" + (mWidth-2) + "><tr><td style='text-align:right;font-family:Tahoma;font-size:11px'>This annotation is originally created by <i><font color=blue>Anonymous</font></i></td></tr></table>" ;
	
	if (atid < 0) { // create first
		mLabel = "Create";
	}
	else {
		mLabel = "Add";
    if (u_getURLvar("usr") == JdbDown.at[atid].userid) {
			imauthor = true;
			// later to do : delete process
		}
		//console.log("length:" + JdbDown.at.length);
		for(i=0;i<JdbDown.at.length;i++) {
	 		if ((JdbDown.at[i].userid == JdbDown.at[atid].userid) && ((JdbDown.at[atid].atid == JdbDown.at[atid].atkey)) ) {
				mCreated = "<table width=" + (mWidth-2) + "><tr><td style='text-align:right;font-family:Tahoma;font-size:11px'>This annotation is originally created by <i><font color=blue>" + JdbDown.at[i].author + "</font></i></td></tr></table>" ;
	 		}

			if (String(JdbDown.at[i].atkey).charAt(0) == "a") {// give up to do later.
				mCreated = "<table width=" + (mWidth-2) + "><tr><td style='text-align:right;font-family:Tahoma;font-size:11px'>This annotation is originally created by <i><font color=blue>" + ((JdbDown.at[atid].author == "anon")?"Anonymous":JdbDown.at[atid].author) + "</font></i></td></tr></table>" ;
			}

			if ( ((JdbDown.at[atid].atid == JdbDown.at[i].atid) && (JdbDown.at[i].kind != "1")) ||
					((JdbDown.at[i].userid == u_getURLvar("usr") && (JdbDown.at[i].kind == "1") && (JdbDown.at[atid].atid == JdbDown.at[i].atid) )) ) { //all comment on an annotation
				mComments += "<tr><td style='font-family:Tahoma;font-size:13px;width:250'>";
				if (JdbDown.at[i].annotation != "") {
					mComments += JdbDown.at[i].annotation ;
				} else {
					mComments += nonote;
				}
				mComments += "</td></tr>" 
				mComments += "<tr><td style='text-align:right;font-family:Tahoma;font-size:11px'>";
				mComments += "<i>";
				switch (parseInt(JdbDown.at[i].kind)) {
					case 0:
					mComments += "Public";
					break;
					case 1:
					mComments += "Private";
					break;
					case 2:
					mComments += "Teacher";
					break;
					default:
					mComments += "Unknown";
					break;
				}
				mComments += "</i>, <i>";
				if (JdbDown.at[i].type == "praise") {
					mComments += "<font color=red>Praised</font>";
				} 
				else {
					mComments += "General"
				}
				mComments += "</i>, and created by <i><font color='blue'>";
				if (JdbDown.at[i].author == "anon") {
			 		mComments += "Anonymous";
				}
				else {
			 		mComments += JdbDown.at[i].author;
				}
				mComments += "</font></i></td></tr>" + "\n" ;
				mComments += "<tr><td><table width=" + (mWidth-40) + "><tr><td style='text-align:right;font-family:Tahoma;font-size:11px'><i><font color='gray'>";
				mComments += JdbDown.at[i].cdate;
			
				if (u_getURLvar("usr") == JdbDown.at[i].userid) {				
					mComments += "<td style='text-align:right;font-family:Tahoma;font-size:11px;color:#FF0000;cursor:pointer' onclick=\"deleteform('" + JdbDown.at[i].atkey + "','" + (atid <0 ? 0 : JdbDown.at[atid].atid) + "');\">[Delete]</td>"
				}			
				else {
					mComments += "<td></td>";
				}	
				mComments += "</font></i></td></tr></table></td></tr> <tr><td><hr/></td></tr>" + "\n" ;
			} //if var
		} // for
	} //if <

	if (document.getElementById("menu") != null) document.body.removeChild(document.getElementById("menu"));
	if (document.getElementById("iframe") != null) document.body.removeChild(document.getElementById("iframe"));

	oMenu = document.createElement("div");
 	oMenu.setAttribute("id", "menu");
 	oComm = document.createComment("<html>" + "\n" + 
 		"<script type='text/javascript'>" +
 		"function get_radio_value() {for (var i=0; i < document.kform.kind.length; i++) " + "\n" +
 		"if (document.kform.kind[i].checked) return document.kform.kind[i].value;	} " + "\n" +
 		"function saveform() {" + "\n" +
 		"var kind = get_radio_value();" +  "\n" +
 		"var type = document.oform.type.checked ? document.oform.type.value : 'General';" +  "\n" +
 		"var author = document.oform.author.checked ? document.oform.author.value : '';" +  "\n" +
 		"var note = document.tform.note.value;" +  "\n" +
 		"var create = document.sform.bsave.value;" +  "\n" +
 		"parent.menu_save(kind, type, author, note, create);" + "\n" +
 		"}"+ "\n" +
 		"function deleteform(atkey, atid) {" + "\n" +
 		"var kind = get_radio_value();" +  "\n" +
 		"var type = document.oform.type.checked ? document.oform.type.value : 'General';" +  "\n" +
 		"var author = document.oform.author.checked ? document.oform.author.value : '';" +  "\n" +
 		"var note = document.tform.note.value;" +  "\n" +
 		"var create = document.sform.bsave.value;" +  "\n" +
 		"parent.menu_delete(kind, type, atkey, atid);" + "\n" +
 		"}"+ "\n" +
 		"function blogit(atid) {" + "\n" +
 		"parent.side_get_blogdata(atid);" + "\n" +
 		"}"+ "\n" +
 		"function clickform(atid) {" + "\n" +
 		"var kind = get_radio_value();" +  "\n" +
 		"var type = document.oform.type.checked ? document.oform.type.value : 'General';" +  "\n" +
 		"var author = document.oform.author.checked ? document.oform.author.value : '';" +  "\n" +
 		"var note = document.tform.note.value;" +  "\n" +
 		"var create = document.sform.bsave.value;" +  "\n" +
 		"parent.menu_click(atid, kind, type);" + "\n" +
 		"}</script>"+ "\n" +
          "<body onload=\"clickform('" + (atid <0 ? 0 : JdbDown.at[atid].atid) + "')\">" + "\n" +
          "  <div style='position:absolute;left:0;top:0;width:" + (mWidth-2) + ";height:22;border-style:solid;border-top:0;border-left:0;border-right:0;font-family:Tahoma;font-size:14px;border-width:1;background-color:#DDDDDD'><b>&nbsp;&nbsp;Add Your Comment </b></div>" + "\n" +
          "  <div style='position:absolute;left:" + (mWidth-22) + ";top:1;width:18;height:18;text-align:center;cursor: pointer;border-style:solid;border-top:0;border-left:0;border-right:0;font-family:Tahoma;font-size:14px;border-width:1;background-color:#DD7777' onclick='parent.menu_cancel();'><b>X</b></div>" + "\n" +
          "  <div id='bottompage' style='position:absolute;left:0;top:" + (mHeight-20) + ";width:" + (mWidth-2) + ";height:19;border-style:none;BORDER-TOP: black 1px solid;font-family:Arial;font-size:12px;text-align:center;border-width:1;background-color:#DDDDDD'>" + mCreated + "</div>" + "\n" +
          "  <div id='tagpage' style='position:absolute;left:0;top:22;width:" + (mWidth-2) + ";height:" + (mHeight-42) + ";overflow:auto;border-style:none;font-family:Arial;font-size:12px;text-align:center;border-width:1;background-color:#EEEEFF'>" + "\n" +
          "  <center><table>" + "\n" +
          "  <tr><td style='font-family:Tahoma;font-size:13px'>" + "\n" +
			"<table><FORM NAME='kform'>" + "\n" + 
		  	"<td style='font-family:Tahoma;font-size:13px'><input id='kind1' type='radio' value='0'  checked name='kind'>Public</td>" + "\n" +
		  	"<td style='font-family:Tahoma;font-size:13px'><input id='kind2' type='radio' value='1' name='kind'>Private</td>" + "\n" +
		  	"<td style='font-family:Tahoma;font-size:13px'><a onclick='blogit(" + (atid <0 ? 0 : JdbDown.at[atid].atid) + ")'>Blog It</a></td>" + "\n" +
			//menu_teacher() +
			"</FORM></table>" + "\n" + "</td></tr>" + "\n" +
		"	<tr><FORM NAME='tform'><td style='font-family:Tahoma;font-size:13px'>" + "\n" +
         "   	<textarea name='note' cols='30' rows='3' id='note'></textarea>" + "\n" +
          "  </td></FORM></tr>" + "\n" +
          "  <tr><td>" + "\n" +
          "    <table>" + "\n" +
          "    <tr>" + "\n" +
          "        <FORM NAME='oform'><td style='font-family:Tahoma;font-size:13px'><img src='" + url + "image/thumbsup1.gif' width='15' height='15'>" + "\n" +
          "            <input id='type1' type='checkbox' value='praise' name='type'>Praise</td>" + "\n" +
          "        <td style='font-family:Tahoma;font-size:13px'><input id='author1' type='checkbox' value='anon' name='author'>Anonymous</td></FORM>" + "\n" +
			" <FORM NAME='sform'><td style='text-align:right'>&nbsp;&nbsp;&nbsp;&nbsp;<input style='font-family:Tahoma;font-size:13px' id='save' type='button' style='width:60px' value='" + mLabel + "' name='bsave' onclick='saveform();'/>&nbsp;&nbsp;&nbsp;&nbsp;</td>" +
          "    </tr>" + "\n" +
          "    </table>" + "\n" +
          "  </td></tr>" + "\n" +
//			" <tr><FORM NAME='sform'><td style='text-align:right'><input style='font-family:Tahoma;font-size:10px' id='list' type='button' style='width:60px' value='Test:Clipped image list' name='btest' onclick='parent.menu_list();'/>&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>" +
		"	</table>" + "\n" +
         " <table id='comments' width='250'><tr><td><hr/></td></tr>" + mComments + "</table></div>" + "\n" +
         " </body> " + "\n" +
         "</html>");				
	oMenu.appendChild(oComm);
	document.body.appendChild(oMenu); 
	var ofMenu = document.createElement("iframe");
 	ofMenu.setAttribute("id", "iMenu");
 	ofMenu.setAttribute("frameborder", "0");
 	ofMenu.setAttribute("vspace", "0");
 	ofMenu.setAttribute("hspace", "0");
 	ofMenu.setAttribute("marginwidth", "0");
 	ofMenu.setAttribute("marginheight", "0");
 	ofMenu.setAttribute("width", mWidth);
 	ofMenu.setAttribute("height", mHeight);
 	ofMenu.setAttribute("scrolling", "no");
	ofMenu.style.cssText =  "BACKGROUND-COLOR:white;BORDER-RIGHT: black 1px solid; BORDER-TOP: black 1px solid; Z-INDEX: 999;" +
			" LEFT: 0px; BORDER-LEFT: black 1px none; BORDER-BOTTOM: black 1px solid; POSITION: absolute;" +
			" TOP: 0px; visibility:hidden;"
	document.body.appendChild(ofMenu); 


	tDiv = document.getElementById("menu");
	FirstElement = tDiv.firstChild;
	v = FirstElement.nodeValue;
	tFrame = document.getElementById("iMenu");
	doc = tFrame.contentDocument;
	if (doc == undefined || doc == null) 
		doc = tFrame.contentWindow.document;
	
	doc.open();
	doc.write(v);
	doc.close();

	tFrame.style.visibility="visible";
	tFrame.style.left= x + document.getElementById("canvas").offsetLeft;
	tFrame.style.top= y + document.getElementById("canvas").offsetTop + 3;

}


function menu_hide()
{
	var tFrame = document.getElementById("iMenu");
	tFrame.style.visibility="hidden";
	menu_show_balloon(-1);
	if (ctx2 != null) cvs_clear(ctx2);
	onMenuInput = -99;
}


function menu_click(atid, kind, type) {
	var valid = false;
	var ctime = new Date();
	var cdate;
	if (preAt >= 0 && atid != "0") {
		cdate = u_day(ctime.getDay()) + " " + ctime.getDate()  + " " + u_month(ctime.getMonth()) + " " + ctime.getFullYear() + ", " + 
					 ctime.getHours() + ":" + ctime.getMinutes() + ":" + ctime.getMinutes();
		var str = initHTTP() + "&atid=" +  encodeURIComponent(atid)  + "&kind=" + kind + "&type=" + type +  "&cdate=" + cdate ;
		valid = true;
	}
	else {
		console.log("menu click : " + preAt);
	}
	if (valid) ajax_click(str); //also should be end of line
}

function menu_delete(kind, type, key, atid) {
	var answer = confirm("Delete this annotation?");
	var valid = false;
	var ctime = new Date();
	var cdate;
	if (answer) {
		if (preAt >= 0) {
			cdate = u_day(ctime.getDay()) + " " + ctime.getDate()  + " " + u_month(ctime.getMonth()) + " " + ctime.getFullYear() + ", " + 
					 ctime.getHours() + ":" + ctime.getMinutes() + ":" + ctime.getMinutes();
			var str = initHTTP() + "&atid=" +  encodeURIComponent(JdbDown.at[preAt].atid) + "&atkey=" +  encodeURIComponent(key)   + "&kind=" + kind + "&type=" + type +  "&cdate=" + cdate ;
			valid = true;
		}
		else {
			console.log("menu delete : " + preAt);
		}
		menu_hide();
	}

	if (valid) ajax_delete(str); //also should be end of line

}

function menu_list() {
	//url = 
	//window.open(url, name, )
}


function menu_save(kind, type, author, note, create) {
//	if (note!="") {
		
		var userid = u_getURLvar("usr");
        var groupid = u_getURLvar("grp");
		var bookid = u_getURLvar("bookid");
		
		// @@@@
		var dbase = 'kseahci';
		if (bookid=='tdo') var dbase = 'kseatdo';
		
		//add by jennifer
		var docid;
		if (u_getURLvar("docid"))docid=u_getURLvar("docid");
		else docid = this.docid;
			
		var aauthor = author ? author : userid;
		var ctime = new Date();
		var formatdate = "";
		note = note.replace(/</g, "&lt;");
		note = note.replace(/>/g, "&gt;");
		note = note.replace(/\n/g, "<br/>");
		if (create == "Create") {
			oAt.atid = ctime.getTime() + u_random(4, false);
			oAt.atkey = oAt.atid;
		}
		else {
			if (note=="") {
				alert("Please write a comment. Thank you!");
				return;
			}
			oAt.atid = JdbDown.at[preAt].atid;
			oAt.atkey = ctime.getTime() + u_random(4, false);
			oAt.x1 = JdbDown.at[preAt].x1;
			oAt.x2 = JdbDown.at[preAt].x2;
			oAt.y1 = JdbDown.at[preAt].y1;
			oAt.y2 = JdbDown.at[preAt].y2;
		}


		oAt.cdate = u_day(ctime.getDay()) + " " + ctime.getDate()  + " " + u_month(ctime.getMonth()) + " " + ctime.getFullYear() + ", " + 
					 ctime.getHours() + ":" + ctime.getMinutes() + ":" + ctime.getMinutes();
		oAt.setMember(docid, userid, groupid, oAt.x1,oAt.x2,oAt.y1,oAt.y2,note, type, kind, aauthor, oAt.atid, page_disp, fileURL);
		menu_hide();
		cvs_clear(ctx2);
		//ctx.strokeRect(oAt.x1,oAt.y1,oAt.x2,oAt.y2);
		var str = oAt.makeHTTP();
		ajax_save(str); //shouldn't be in the middle of function...pain in my ass for 5 hours..
}


function menu_cancel() {
	menu_hide();
}

///////////////////////////////////////////////////////////////////////////////////////////
// Cookie
function ck_get_cookie(Name) {
	var search = Name + "="
	var returnvalue = "";
	if (document.cookie.length > 0) {
		offset = document.cookie.indexOf(search)
		// if cookie exists
		if (offset != -1) { 
			offset += search.length
			// set index of beginning of value
			end = document.cookie.indexOf(";", offset);
			// set index of end of cookie value
			if (end == -1) end = document.cookie.length;
			returnvalue=unescape(document.cookie.substring(offset, end))
		}
	}
	return returnvalue;
}

function ck_set_cookie(what){
	document.cookie="showIM="+what
}

function ck_power_on(trigger) {
	
	if (trigger == "all") {
		ck_set_cookie("all");
		showIM = "all";
		document.getElementById("power1").setAttribute("src", url + "image/allon.gif");
		document.getElementById("power2").setAttribute("src", url + "image/meoff.gif");
		document.getElementById("power3").setAttribute("src", url + "image/offoff.gif");
	} else if (trigger == "me") {
		ck_set_cookie("me");
		showIM = "me";
		document.getElementById("power1").setAttribute("src", url + "image/alloff.gif");
		document.getElementById("power2").setAttribute("src", url + "image/meon.gif");
		document.getElementById("power3").setAttribute("src", url + "image/offoff.gif");
	} else if (trigger == "off") {
		ck_set_cookie("off");
		showIM = "off";
		document.getElementById("power1").setAttribute("src", url + "image/alloff.gif");
		document.getElementById("power2").setAttribute("src", url + "image/meoff.gif");
		document.getElementById("power3").setAttribute("src", url + "image/offon.gif");
	}
	//console.log(showIM);
	cvs_reDraw();
}

function ck_summary() {
	//var url = "./im/dbList187.jsp?userid=" + u_getURLvar("usr") +  "&groupid=" + u_getURLvar("grp");
	//var title = "Summary";
	//var param = "toolbar=yes,location=yes,directories=yes,status=yes,menubar=yes,scrollbars=yes,copyhistory=yes,resizable=yes";
	//window.open(url, title, param); 
 //document.location.href = "./im/dbList187.jsp?userid=" + u_getURLvar("usr") +  "&groupid=" + u_getURLvar("grp");
}

function ck_power_img() {
	
	if (document.getElementById("power1") != null ) document.body.removeChild(document.getElementById("power1"));
	if (document.getElementById("power2") != null ) document.body.removeChild(document.getElementById("power2"));
	if (document.getElementById("power3") != null ) document.body.removeChild(document.getElementById("power3"));
	if (document.getElementById("summary") != null ) document.body.removeChild(document.getElementById("summary"));
	if (document.getElementById("slink") != null ) document.body.removeChild(document.getElementById("slink"));
	
	var x = parseInt(document.getElementById("canvas").getAttribute("width")) + parseInt(document.getElementById("canvas").offsetLeft);
	var y = parseInt(document.getElementById("canvas").offsetTop);
	var sum = 79;
	var w = 39;
	var h = 16;
	var o = 2;
	var imgall = new Image();
	var imgme = new Image();
	var imgoff = new Image();
	var summary = new Image();
	var slink = document.createElement("a");

	
	var trigger = ck_get_cookie("showIM");

	if (isIE) {
		o = 0;
	}
	 	
	if (trigger == "off") {
		ck_set_cookie("off");
		showIM = "off";
		imgall.setAttribute("src", url + "image/alloff.gif");
		imgme.setAttribute("src", url + "image/meoff.gif");
		imgoff.setAttribute("src", url + "image/offon.gif");
	} else 	if (trigger == "me") {
		ck_set_cookie("me");
		showIM = "me";
		imgall.setAttribute("src", url + "image/alloff.gif");
		imgme.setAttribute("src", url + "image/meon.gif");
		imgoff.setAttribute("src", url + "image/offoff.gif");
	} else {
		ck_set_cookie("all");
		showIM = "all";
		imgall.setAttribute("src", url + "image/allon.gif");
		imgme.setAttribute("src", url + "image/meoff.gif");
		imgoff.setAttribute("src", url + "image/offoff.gif");
	}  
	summary.setAttribute("src", url + "image/sum.gif");
	
	imgall.setAttribute("id", "power1");
	imgme.setAttribute("id", "power2");
	imgoff.setAttribute("id", "power3");
	summary.setAttribute("id", "summary");
	summary.setAttribute("border", "0");
	slink.setAttribute("target", "_summary");

	slink.setAttribute("id", "slink");
	//changed by Jennfier
	// @@@@
	slink.setAttribute("href", annotated_url+"dbList187.jsp?userid=" + u_getURLvar("usr") +  "&groupid=" + u_getURLvar("grp")+"&dbase="+dbase);
	//slink.setAttribute("href", "./im/dbList187.jsp?userid=" + u_getURLvar("usr") +  "&grp=" + u_getURLvar("grp"));
	slink.setAttribute("class", "page_no");
	
	imgall.onclick = function() {ck_power_on("all");};	
	imgme.onclick = function() {ck_power_on("me");};	
	imgoff.onclick = function() {ck_power_on("off");};
	//summary.onclick =	function() {ck_summary();};

		
	imgall.style.cssText =  "position:absolute;left:" + (x-w*3+o+1-sum) + "px;top:" + (y) + "px;width:" + w + "px;height:" + h + "px;" +
	"cursor:pointer;z-index:70;";
	imgme.style.cssText =  "position:absolute;left:" + (x-w*2+o-sum) + "px;top:" + (y) + "px;width:" + w + "px;height:" + h + "px;" +
	"cursor:pointer;z-index:70;";
	imgoff.style.cssText =  "position:absolute;left:" + (x-w+o-sum) + "px;top:" + (y) + "px;width:" + w + "px;height:" + h + "px;" +
	"cursor:pointer;z-index:70;";
	summary.style.cssText =  "position:absolute;left:" + (x-w+o-sum/2) + "px;top:" + (y) + "px;width:" + sum + "px;height:" + h + "px;" +
	"cursor:pointer;z-index:70;";
	slink.style.cssText =  "color:black";
	
	slink.appendChild(summary);
	
	document.body.appendChild(imgall);
	document.body.appendChild(imgme);
	document.body.appendChild(imgoff);
   //document.body.appendChild(summary); 
   document.body.appendChild(slink); 
	
}
	
	
///////////////////////////////////////////////////////////////////////////////////////////
// Utii
function u_random(n, author)
{
	var str = "";
	if (author) { //author id
		str += "a"
	} else {
		str += String.fromCharCode(Math.round(Math.random()*24+98));
	}
	for(var i=0;i<n-1;i++) {
		if (Math.round(Math.random()*10+1)>7) {
			str += String.fromCharCode(Math.round(Math.random()*24+98));
		} else {
			str += String.fromCharCode(Math.round(Math.random()*9+48));
		}
	}
	return str;
}

function u_day(num) {
	var str;
	switch (num) {
		case 0:
		str = "Sun";
		break;
		case 1:
		str = "Mon";
		break;
		case 2:
		str = "Tue";
		break;
		case 3:
		str = "Wed";
		break;
		case 4:
		str = "Thu";
		break;
		case 5:
		str = "Fri";
		break;
		case 6:
		str = "Sat";
		break;
	}
	return str;
}

function u_month(num) {
	var str;
	switch (num) {
		case 0:
		str = "Jan";
		break;
		case 1:
		str = "Feb";
		break;
		case 2:
		str = "Mar";
		break;
		case 3:
		str = "Apr";
		break;
		case 4:
		str = "May";
		break;
		case 5:
		str = "Jun";
		break;
		case 6:
		str = "Jul";
		break;
		case 7:
		str = "Aug";
		break;
		case 8:
		str = "Sep";
		break;
		case 9:
		str = "Oct";
		break;
		case 10:
		str = "Nov";
		break;
		case 11:
		str = "Dec";
		break;
	}
	return str;
}

function u_getURLvar(name) {
   var rs = "";
   if (name == 'usr') {
    rs = window.usr;
   } else if (name == 'grp') {
    rs = window.grp;   
   } else if (name == 'bookid') {
    rs = window.bookid;   
   } else if (name == 'docid') {
    rs = window.docid;   
   } else {
   
     var qparts = parent.document.location.href.split("?");
     //console.log(window);
     if (qparts.length > 1) {
         var vars = qparts[1].split("&");
         for (var i=0; (i < vars.length) && (rs.length == 0); i++) {
             var parts = vars[i].split("=");
             if (parts[0] == (name)) rs = parts[1];
         }
     }

   }

    
	return rs;
}



function u_abs(n) {
	if( n < 0 )
		return -n;
	else 
		return n;
}




function isClickonCanvas(px, py) {
	cx1 = oC1.offsetLeft;
	cx2 = oC1.offsetWidth;
	cy1 = oC1.offsetTop;
	cy2 = oC1.offsetHeight;
	if ((0 < px) && (px < cx2) && (0 < py) && (py < cy2)) return true;
	else return false;
}

function u_nearBound(a, b) {
	a = parseInt(a);
	b = parseInt(b);
	if ( (b-2<a) && (a<b+2) ){ 
		return true; 
	}
	else { 
		return false;
	}
}


function initHTTP() {
	var sendStr = null;
	var userid = window.usr; //u_getURLvar("usr");
	var groupid = window.grp; //u_getURLvar("grp");
	//change by jennifer
	var docid;
	//if (u_getURLvar("docid")) docid = u_getURLvar("docid");
	//else docid = this.docid;
    docid = window.docid;
	//var fileid = u_getURLvar("docid");
	if (userid && groupid && docid)
	{
		sendStr = "userid=" + encodeURIComponent(userid) + "&groupid=" + encodeURIComponent(groupid) + 
		"&docid=" + encodeURIComponent(docid) + "&fileid=" + encodeURIComponent(page_disp) +
		"&bookid=" + encodeURIComponent(bookid)+"&dbase="+dbase;
		return sendStr; 
	}
	else
	{
		console.log("failed to create a HTTP string");
		return null;
	}	
}


function debug( text ) 
{
	var div = document.getElementById("debug");
	if(document.all){
	 	//div.innerText +="\n" + text + " ";
	} else{
	    //div.textContent += "\n" + text + " ";
	}
}
