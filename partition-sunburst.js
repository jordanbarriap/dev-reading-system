// set the url for get user progress
progress_url = progress_url + "?usr="+usr+"&grp="+grp+"&sid="+sid+"&mode=all"

$.ajax({
    url: progress_url,
    async:false,
    success:function (result) {
        // get user progress and paint sunburst
        
        var docAmount = result.progress.length;
        var countDoc = 0;
        var diagram = [];
        for (countDoc = 0; countDoc < docAmount; countDoc++) {
            var uprogress = result.progress[countDoc].uprogress;
            if (uprogress >= 0 && uprogress < 0.1) {
                var theColor = colors[0];
            }
            if (uprogress >= 0.1 && uprogress < 0.2) {
                var theColor = colors[1];
            }
            if (uprogress >= 0.2 && uprogress < 0.3) {
                var theColor = colors[2];
            }
            if (uprogress >= 0.3 && uprogress < 0.4) {
                var theColor = colors[3];
            }
            if (uprogress >= 0.4 && uprogress < 0.5) {
                var theColor = colors[4];
            }
            if (uprogress >= 0.5 && uprogress < 0.6) {
                var theColor = colors[5];
            }
            if (uprogress >= 0.6 && uprogress < 0.7) {
                var theColor = colors[6];
            }
            if (uprogress >= 0.7 && uprogress < 0.8) {
                var theColor = colors[7];
            }
            if (uprogress >= 0.8 && uprogress < 0.9) {
                var theColor = colors[8];
            }
            if (uprogress >= 0.9 && uprogress <= 1) {
                var theColor = colors[9];
            }
            diagram.push({
                docNum:result.progress[countDoc].docno,
                diagramColor:theColor
            })
        }
        // ----------------------------------------------
        // draw the sunburst
        var width1 = 450,
            height1 = 380,
            radius1 = 190,
            color = d3.scale.category20c();

        var vis = d3.select("#chart").append("svg")
            .attr("width", width1)
            .attr("height", height1)
            .attr("pointer-events", "all")
            .append("g")
            .attr("transform", "translate(" + width1 * 0.48 + "," + height1 * 0.51 + ")")
            // using scroll mouse to resize sunburst
 //           .call(d3.behavior.zoom().on("zoom", redraw))
            .append('svg:g');

        function redraw() {
            vis.attr("transform",
                "translate(" + d3.event.translate + ")"
                    + " scale(" + d3.event.scale + ")");
        }

        var partition = d3.layout.partition()
            .sort(null)
            .size([2 * Math.PI, radius1 * radius1])
            .value(function (d) {
                return 1;
            });

        var arc = d3.svg.arc()
            .startAngle(function (d) {
                return d.x;
            })
            .endAngle(function (d) {
                return d.x + d.dx;
            })
            .innerRadius(function (d) {
                return Math.sqrt(d.y);
            })
            .outerRadius(function (d) {
                return Math.sqrt(d.y + d.dy);
            });
        // @@@@
        d3.json(json_file, function (error, json1) {
            color.domain(d3.keys(json1[0]).filter(function (key) {
                    return key !== "name";
                }
            ));

            //jQuery.each(json1, function (i, val) {
            //    val.ages = color.domain().map(function (name) {
            //        return {name:name, population:+d[name]};
            //    });
            //});

            var g = vis.datum(json1).selectAll("g")
                .data(partition.nodes)
                .enter().append("svg:g")
                // I don't get this part of the code: how this prevents the initial circle? by denis
                .attr("display", function (d) {
                    if (d.depth == 0 || d.depth == 4){
                        return "none";
                    }
                    else{
                        return null;
                    }
                    // return d.depth ? null : "none";
                })
                .attr("class", function (d) { 
                    classname = "partition_depth_" + d.depth;

                    if (d.depth > 0){
                        classname += " partition_docno_" + d.docno;
                    }

                    return classname;
                })
                .on("click", click)// hide inner ring
                .on("mouseover", mouseover)
                .on("mouseout", mouseout);


            d3.select("#chart").on("mouseleave", mouseleave);

            var lastcolor = '';
            var pieslice = g.append("svg:path")
                .attr("d", arc)
                .attr("class", "arc_path")
                .attr("stroke", "#fff")
                .attr("fill", function (d) {
                    var count = 0;
                    for (count = 0; count < diagram.length; count++) {
                        if (diagram[count].docNum == d.docno) {
                            return d3.rgb(diagram[count].diagramColor)
                        }
                    }
                });

            g.append("svg:text")
                .attr("transform", function (d) {
                    return "rotate(" + (d.x + d.dx / 2 - Math.PI / 2) / Math.PI * 180 + ")";
                })
                .attr("x", function (d) {
                    return Math.sqrt(d.y);
                })
                .attr("dx", "+2")// margin
                .attr("dy", ".35em")// vertical-align
                .style("font-size", "70%")
                .text(function (d) {
                    return (d.type == "lecture" ? d.name : ''); // @@@@
                });

            function click(d) {
                var actionsrc = "sunburst_model"
                var actiontype = "display_content"
                var dialogText = '<h3>' + d.name + '</h3>';
                var bmc = '#basic-modal-content-2';
                var link = "";
                var link2 = "";
                var link3 = "";

                var goToLink = null;
                
                dialogText = dialogText + '<ul>';
                
                currentDocno = d.docno;
                //alert(currentDocno);
                
                // select the branch clicked
                d3.selectAll("path").style("opacity", 0.3);

                // Then highlight only those that are an ancestor of the current segment.
                var sequenceArray = getAncestors(d);

                vis.selectAll("path")
                    .filter(function(node) {
                        return (sequenceArray.indexOf(node) >= 0);
                    })
                    .style("opacity", 1);


                if(d.children != null && d.children.length > 0){
                    jQuery.each(d.children, function (i, val) {
                        var docsrc = val.bookid;
                        var docno = val.docno;
                        var bookName = getBookName(docsrc);
                        
                        if (val.links.length > 0) {
                            //alert(docno);
                            jQuery.each(val.links, function (i_links, val_links) {
                                link = '<a onClick="javascript:parent.parent.frames[\'iframe-content\'].location = \'' + reader_url + '?bookid='
                                    + docsrc + '&docno=' + docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1' +'\'; "href=\"#\">' + val.name + '</a>&nbsp;';

                                if(goToLink == null){
                                    goToLink = reader_url + '?bookid=' + docsrc + '&docno=' + docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1';
                                }
                            });
                        }

                        //dialogText = dialogText + '<li>' + '<h6>BOOK: '+ bookName +'</h6>'+ link + '</li>'
                        dialogText = dialogText + '<li>' + link + '</li>'

                        //check chapter children
                        if (val.children != null && val.children.length > 0) {
                      //      dialogText = dialogText + '<ul>';
                            var dialogText2 = '<ul>'
                            jQuery.each(val.children, function (i2, val2) {
                                var docsrc = val2.bookid;
                                var docno = val2.docno;
                                if (val2.links.length > 0) {
                                    jQuery.each(val.links, function (i_links2, val_links2) {
                                        link2 = '<a onClick="javascript:parent.parent.frames[\'iframe-content\'].location = \'' + reader_url + '?bookid='
                                            + docsrc + '&docno=' + docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1' +'\'; "href=\"#\">' + val2.name + '</a>&nbsp;';

                                        if(goToLink == null){
                                            goToLink = reader_url + '?bookid=' + docsrc + '&docno=' + docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1';
                                        }
                                    });
                                }

                                dialogText2 = dialogText2 + '<li>' + link2 + '</li>';

                                if(val2.children != null && val2.children.length > 0){
                            //        dialogText = dialogText + '<ol>';
                                 var dialogText3 = "<ul>"
                                    jQuery.each(val.children, function (i3, val3){
                                        var docsrc = val3.bookid;
                                        var docno = val3.docno;
                                        if (val3.links.length > 0) {
                                            jQuery.each(val.links, function (i_links3, val_links3) {
                                                link3 = '<a onClick="javascript:parent.parent.frames[\'iframe-content\'].location = \'' + reader_url + '?bookid='
                                                    + docsrc + '&docno=' + docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1' +'\'; "href=\"#\">' + val3.name + '</a>&nbsp;';

                                                if(goToLink == null){
                                                    goToLink = reader_url + '?bookid='+ docsrc + '&docno=' + docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1';
                                                }
                                            });
                                        }
                                        dialogText3 = dialogText3 + '<li>' + link3 + '</li>';
                                    })
                                 dialogText2 = dialogText2 + dialogText3 + "</ul>";
                                }
                            });

                            dialogText = dialogText + dialogText2 + '</ul>';
                        }
                        dialogText = dialogText + '</li>';
                    });
                }else{
                    var docsrc = d.bookid;
                    var docno = d.docno;

                    if(d.links != null && d.links.length>0){
                        jQuery.each(d.links, function(i_links, val_links){
                            link = '<a onClick="javascript:parent.parent.frames[\'iframe-content\'].location = \'' + reader_url + '?bookid='
                                + docsrc + '&docno=' + docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1' +'\'; "href=\"#\">' + d.name + '</a>&nbsp;';

                             if(goToLink == null){
                                goToLink = reader_url + '?bookid=' + docsrc + '&docno=' + docno + '&usr='+ usr + '&grp=' + grp + '&sid='+ sid + '&page=1';
                            }
                        });
                    }

                    var bookName = getBookName(docsrc);
                    
                    //dialogText = dialogText + '<li>' + '<h6>BOOK: '+ bookName +'</h6>'+ link + '</li>'
                }

                dialogText = dialogText + '</ul>';

                // Experimental: Do not display the modal window but scroll to instead.
                // $(bmc).html(dialogText);
                // $(bmc).modal();

                parent.$("#readings").attr('src', goToLink);

                return false;
            }

            function mouseover(d, i) {
                d3.select(g[0][i]).select("path").style("opacity", 1.0);
                
				d3.select(g[0][i]).select("path").style("stroke","#000");
				d3.select(g[0][i]).style("stroke-width", "1");

                /*
                // Fade all the segments.
                d3.selectAll("path").style("opacity", 0.3);

                // Then highlight only those that are an ancestor of the current segment.
                var sequenceArray = getAncestors(d);

                vis.selectAll("path")
                    .filter(function(node) {
                        return (sequenceArray.indexOf(node) >= 0);
                    })
                    .style("opacity", 1);

                */
                //d.bookid = "tdo"; // @@@@
                
                if(d.type == "lecture"){
                    //$("#tip").html(d.name + ":<br/>" + d.title); // @@@@
                    $("#tip").html(d.title);
                }else{
                    var bookName = getBookName(d.bookid);
                    $("#tip").html("[Book] <b>" + bookName + "</b>:<br />" + d.name);
                }
                $("#tip").css("color","#777777");

                /* BEGIN iframe selection */
                // @@@@ this was intended to show selection in the small multiples frame, but it is broken, and commented 
                // @@@@ in order to prevent a javascript error
                if (d.type == "lecture") {
                    //parent.frames['iframe-sm'].selectFunction('arc' + (d.name.replace(/ /g, "-")), 0.1);
                } else {
                    //parent.frames['iframe-sm'].selectFunction('arc' + (searchParent(d).replace(/ /g, "-")), 0.1);
                }
                /* END iframe selection */
                return false;
            }

            function mouseout(d, i) {
				d3.select(g[0][i]).select("path").style("stroke","#fff");			
				d3.select(g[0][i]).style("stroke-width", "1");				
				d3.select(g[0][i]).style("opacity", 1);
                setHighlight(currentDocno);
                /* BEGIN iframe selection */
                if (d.type == "lecture") {
                    //parent.frames['iframe-sm'].selectFunction('arc' + (d.name.replace(/ /g, "-")), 1);
                } else {
                    //parent.frames['iframe-sm'].selectFunction('arc' + (searchParent(d).replace(/ /g, "-")), 1);
                }
                /* END iframe selection */
                return false;
            }
            

            
            function mouseleave(d){
                // // Deactivate all segments during transition.
                //d3.selectAll("path").on("mouseover", null);

                // Transition each segment to full opacity and then reactivate it.
                //d3.selectAll("path")
                 //     .transition()
                 //     .duration(200)
                      //.style("opacity", 1)
                 //     .each("end", function() {
                 //             d3.select(this).on("mouseover", mouseover);
                 //           });
            }

            reloadChartDetail();
            setHighlight(currentDocno);
        });

        function searchParent(node) {
            if (node.parent.type == "lecture") {
                return node.parent.name;
            } else {
                return searchParent(node.parent);
            }
        }

// Stash the old values for transition.
        function stash(d) {
            d.x0 = d.x;
            d.dx0 = d.dx;
        }

// Interpolate the arcs in data space.
        function arcTween(a) {
            var i = d3.interpolate({x:a.x0, dx:a.dx0}, a);
            return function (t) {
                var b = i(t);
                a.x0 = b.x;
                a.dx0 = b.dx;
                return arc(b);
            };
        }
    }
    
    //setHighlight(currentDocno);
});

// Given a node in a partition layout, return an array of all of its ancestor
// nodes, highest first, but excluding the root.
function getAncestors(node) {
  var path = [];
  var current = node;
  while (current.parent) {
    path.unshift(current);
    current = current.parent;
  }
  return path;
}

function unsetHighlight(){
    d3.selectAll("path").style("opacity", 1);
}

function setHighlight(docno){
    //alert('setting high light');
    console.log("Set highlight called with docno:" + docno);
    
    g = d3.select(".partition_docno_"+docno);

    if (!g)
        return;

    d = g.data()[0]

    if(!d)
        return;

    // Fade all the segments.
    d3.selectAll("path").style("opacity", 0.3);

    // Then highlight only those that are an ancestor of the current segment.
    var sequenceArray = getAncestors(d);

    d3.selectAll("path")
        .filter(function(node) {
            return (sequenceArray.indexOf(node) >= 0);
        })
        .style("opacity", 1);
     if(d.type == "lecture"){
         $("#tip").html(d.title);
     }else{
         var bookName = getBookName(d.bookid);
         $("#tip").html("[Book] <b>" + bookName + "</b>:<br />" + d.name);
     }
     $("#tip").css("color","#000000");
}

