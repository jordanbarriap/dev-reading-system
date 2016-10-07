/**
 * Created with JetBrains WebStorm.
 * User: VigossZ
 * Date: 13-1-29
 * Time: 上午7:09
 * To change this template use File | Settings | File Templates.
 */
 var pendingAnswers = -1;//added by jbarriapineda in 29-09

$(document).ready(function(){
	
	// a set with all document ID's that are linked to
	var docIdSet = {}; // would use Set object, but cross-browser issues
	var docNoSet = {}; // would use Set object, but cross-browser issues
	var docIdMaxpage = {};
    $.ajax({
        url: json_file, // @@@@
        async: false,
        dataType: "json",
        success: function(result){
            // display name for the course
            $("#index").append("<h4>"+result.name+"</h4>");
            var index = "<ul>";

            // read the lectures and display the name for each
            $.each(result.children, function(i_lecture, lecture){
                var lecture_content = "<h5>" + lecture.title + "</h5>";
                var chapter_content = "<ul>";

                if(lecture.children.length > 0){
                    $.each(lecture.children, function(i_chapter, chapter){
                        var chapter_bookid = chapter.bookid;
                        var chapter_docno = chapter.docno;
                        var chapter_id = chapter.id;

						//No need to consider Chapters.
						//docIdSet[chapter_docno.split('-')[1]] = 0;
                        var bookName = getBookName(chapter_bookid);
                        var reading_content = "<ul>";
                        if(chapter.children.length > 0){
                            $.each(chapter.children, function(i_reading, reading){
							    // Ending page.
							    var epage = reading.epage;

							    var subsection_docids = new Set();
								var subsection_docnos = new Set();
                                var reading_bookid = reading.bookid;
                                var reading_docno = reading.docno;
                                var reading_id = reading.id;
								subsection_docids.add(reading_id + "@" + reading_docno);
								
                                var leaf_content = "<ul>";								
                                if(reading.children.length > 0){
                                    $.each(reading.children, function(i_leaf, leaf){
                                        var leaf_bookid = leaf.bookid;
                                        var leaf_docno = leaf.docno;
                                        var leaf_id = leaf.id;
										if(epage < leaf.epage) {
										  epage = leaf.epage;
										}
										subsection_docids.add(leaf_id + "@" + leaf_docno);
										
                                        leaf_content = leaf_content +
                                            '<li> <a class="doclink ' + 'docid-' + leaf_id + '" href="#" id="readingid-' + leaf_docno + '" onclick="javascript:parent.parent.frames[\'iframe-content\'].location = \'' + reader_url + '?bookid='
                                            + leaf_bookid + '&docno=' + leaf_docno + '&usr='+ usr + '&grp=' + grp + '&sid=' + sid + '&page=1' +'\';">' +
                                            leaf.name + '</a></li>';
                                    });
                                }
                                leaf_content = leaf_content + "</ul>";
                                reading_content = reading_content +
                                    '<li> <a href="#" style="font-weight:bold;font-size:20px" class="qmark doclink docid-' + reading_id + '" onclick="javascript:parent.parent.frames[\'iframe-content\'].location = \''+reader_url+'?bookid='
                                    + reading_bookid + '&docno=' + reading_docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1&fromHierarchical=tree' +'\';">?</a>&nbsp;&nbsp;<a id="readingid-' + reading_docno + '" class="doclink ' + 'docid-' + reading_id + '" href="#" onclick="javascript:parent.parent.frames[\'iframe-content\'].location = \''+reader_url+'?bookid='
                                    + reading_bookid + '&docno=' + reading_docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1' +'\';">' +
                                    reading.name + '</a>'+ leaf_content + '</li>';
								var string_subsectionids = "";
								for(var element of subsection_docids) {
									string_subsectionids += element + ",";
									docIdMaxpage[element.split('@')[0]] = epage;
								}
								string_subsectionids += "-1";
								docIdSet[string_subsectionids.replace(",-1", "")] = 0;
                            });
                        }
                        reading_content = reading_content + "</ul>";
                        // @@@@ this shows the book, under the assumption that each reading of a lecture can belong to a
                        // @@@@ different book. If the model represents only one book, it is not good to repeat the book name 
                        // @@@@ Another global variable from DB must tell the visualization if multiple books or not
                        chapter_content = chapter_content +
                            '<li><h5>BOOK:' + bookName + '<br/><a class="doclink" style = "color:black" href="#" onclick="javascript:parent.parent.frames[\'iframe-content\'].location = \''+reader_url+'?bookid='
                            + chapter_bookid + '&docno=' + chapter_docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1' +'\';">' +
                            chapter.name + '</a></h5>' + reading_content + '</li>';
                    });
                }

                chapter_content = chapter_content + "</ul>";
                lecture_content = lecture_content + chapter_content;
                index = index + "<li>" + lecture_content + "</li>";
            });

            index = index + "<ul>";
            $("#index").append(index);
			var allDocIds = "<input type=\"hidden\" id=\"allDocIds\" value='" + parent.JSON.stringify(Object.keys(docIdSet)) + "'>";
			var usrId = "<input type=\"hidden\" id=\"usr\" value='" + usr + "'>";
			var grpId = "<input type=\"hidden\" id=\"grp\" value='" + grp + "'>";
			$("#index").append(allDocIds);
			$("#index").append(usrId);
			$("#index").append(grpId);
        }
    });	
	
    var questionStatusButton = document.getElementById("toggle-question-status");
	var hiddenQuestionStatusButton = document.getElementById("hidden-question-status");
    var indexElement = document.getElementById("index");
    
//    var data = {
//	        'docids': Object.keys(docIdSet),
//	        'usr': usr,
//	        'grp': grp
//	    };
		
	updateIndexForQuestionStatus(usr, grp, Object.keys(docIdSet), true);
	
	// Decides popoup questions.
	function QuestionPopupWhenReachingSectionEnd(key_each, value) {
	  var currentPage = parent.parent.frames['iframe-content'].document.getElementById('current-page');
	  var docid = parent.parent.frames['iframe-content'].document.getElementById('reader-docid');
	  var current_reader_url = parent.parent.frames['iframe-content'].location.href;
	  //console.log("currentPage: " + currentPage.value + " docid: " + docid.value + "   MaxPage:" + docIdMaxpage[docid.value]);
	  
	  // See whether we need to pop up a question page for readers.
	  if(docid != null && docid.value == key_each
		 && (value == 1 || value == 2)
		 && currentPage != null && currentPage.value == docIdMaxpage[docid.value]) {
		//pops up a window.
		// if questions are not displayed.
		if(pendingAnswers==-1 && !window.parent.skippedQuestions[docid.value] && parent.frames['iframe-content'].document.getElementById('question').style.display == 'none') {//modified by jbarriapineda in 29-09
		  //parent.parent.frames['iframe-content'].location.href = current_reader_url.replace("&fromHierarchical=tree", "") + "&fromHierarchical=tree";
		  displayQuestions();
		}
	  }
	}
	
	function updateIndexForQuestionStatus(usr, grp, docids, scroll) {
	    var data = {
	        'docids': docids,
	        'usr': usr,
	        'grp': grp
	    };
		
		$.ajax({
        url: 'questions/api.php?task=subsectionstatus',
        type: 'POST',
        data: parent.JSON.stringify(data),
	    contentType: 'application/json',
        dataType: "json",
        success: function(result) {
        	for (var key in result) {
        		if (result.hasOwnProperty(key)) {
        			var value = result[key];
					for(var key_each of key.split(",")){
                      QuestionPopupWhenReachingSectionEnd(key_each, value);					
					  var elements = document.getElementsByClassName('docid-' + key_each);
        			  for (var i = 0; i < elements.length; i++) {
        				var element = elements[i];
						// Removes status related CSS.
						for(var status = 0; status <= 4; status++) {
						  element.classList.remove("status-" + status);
						  element.classList.remove("status-" + status + "-qmark");
						}

						if(element.classList.contains('qmark')) {
						  element.classList.add('status-' + value + '-qmark');
						  element.innerHTML = '?';
						  // Removes question mark if status = 3 or 4.
						  if(value == 3 || value == 4) {
						    element.innerHTML = '';
						  }
						} else {
						  element.classList.add('status-' + value);
						}
        			  }
					}
        		}
        	}
			if(scroll) {
			  scrollToView();
			}
          }
      });
	}
/**    
	$.ajax({
        url: 'questions/api.php?task=subsectionstatus',
        type: 'POST',
        data: parent.JSON.stringify(data),
	    contentType: 'application/json',
        dataType: "json",
        success: function(result) {
        	for (var key in result) {
        		if (result.hasOwnProperty(key)) {
        			var value = result[key];
					for(var key_each of key.split(",")){
					  var elements = document.getElementsByClassName('docid-' + key_each);
        			  for (var i = 0; i < elements.length; i++) {
        				var element = elements[i];
						if(element.innerHTML == '?') {
						  element.classList.add('status-' + value + '-qmark');
						  // Removes question mark
						  if(value == 3 || value == 4) {
						    element.innerHTML = '';
						  }
						} else {
						  element.classList.add('status-' + value);
						}
        			  }
					}
        		}
        	}
        }
    });
 */   
    var questionStatus = false; // true when status is shown in index
    var toggleQuestionStatus = function() {
    	questionStatus = !questionStatus;
    	if (questionStatus) {
    		questionStatusButton.style.backgroundColor = 'lightblue';
    		indexElement.classList.add('qShow');
    	} else {
    		questionStatusButton.style.backgroundColor = '';
    		indexElement.classList.remove('qShow');
    	}
    };
	
	var updateAllQuestionStatus = function() {
	  var usr = parent.document.getElementById('usr').value;
	  var grp = parent.document.getElementById('grp').value;
	  var allDocIds = parent.document.getElementById('allDocIds').value;
	  updateIndexForQuestionStatus(usr, grp, parent.JSON.parse(allDocIds), false);
	  indexElement.classList.add('qShow');
	}
    
    questionStatusButton.addEventListener('click', toggleQuestionStatus);
	hiddenQuestionStatusButton.addEventListener('click', updateAllQuestionStatus);
	questionStatusButton.click();
	
	function scrollToView() {
	  var element_current_doc = document.getElementById('readingid-' + currentDocno);
	  element_current_doc.scrollIntoView();
	}
	
});

//code added by jbarriapineda in 09-29
function displayQuestions(){
    var current_reader_url = parent.parent.frames['iframe-content'].location.href;
    var readerdocid = parent.parent.frames['iframe-content'].document.getElementById('reader-docid');//added by jbarriapineda in 29-09
    $('#dialog-questions').dialog({
      resizable: false,
      autoOpen:false,
      modal:true,
      draggable:false,
      closeOnEscape: false,
      title: "Reading questions",
      open: function(event, ui) {
            $(".ui-dialog-titlebar-close").hide();
            $(this).parent().css('position', 'fixed');
            $(this).parent().parent().css('overflow', 'hidden');
      },
      buttons: {
        "Yes, I'm ready": function() {
          $(this).dialog("close");
          parent.parent.frames['iframe-content'].location.href = current_reader_url.replace("&fromHierarchical=tree", "") + "&fromHierarchical=tree";
          $(this).parent().parent().css('overflow', 'initial');
        },
        "No, I'll do it later": function() {
          pendingAnswers=$(readerdocid).val();
          window.parent.docidQs=$(readerdocid).val();
          $(this).dialog("close");
          $(this).parent().parent().css('overflow', 'initial');
        }
      },
      position: { my: 'center', at: 'center', of: window.parent.document}
    });
    
    if ($('.ui-dialog')){
        if(parent.frames['iframe-content'].document.getElementById('question').style.display == 'none') {
            jQuery('#dialog-questions', window.parent.document).dialog("open");
            jQuery('.ui-dialog', window.parent.document).css("top", "40%");
            jQuery('.ui-dialog', window.parent.document).css("left", "40%");

        }
    }else{
        alert("not ready");
    }
}
//end of code added by jbarriapineda
