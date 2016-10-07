if (false){
  var bookStructure = [];
  var weeks = 3;
  //for (var week = 1; week < 4; week++) {
    var selfUrl = selfmodels_url+"?usr=" + usr + "&grp=" + grp + "&weeks=" + weeks;
    //alert(selfUrl);
    $.ajax({
        url:selfUrl,
        dataType: "json",
        async:false,
        success:function (result) {

            if (result.models.length > 0) {
                var progressDiagram = [];
                var week = 0;
                $.each(result.models, function (i_model, model) {
                    var smallSunburst = [];
                    week++;
                    $.each(model.progress, function (i_progress, progress) {
                        var theColor = "";
                        
                        //alert(progress.uprogress);
                        if (progress.uprogress >= 0.0 && progress.uprogress < 0.1) theColor = colors[0];
                        if (progress.uprogress >= 0.1 && progress.uprogress < 0.2) theColor = colors[1];
                        if (progress.uprogress >= 0.2 && progress.uprogress < 0.3) theColor = colors[2];
                        if (progress.uprogress >= 0.3 && progress.uprogress < 0.4) theColor = colors[3];
                        if (progress.uprogress >= 0.4 && progress.uprogress < 0.5) theColor = colors[4];
                        if (progress.uprogress >= 0.5 && progress.uprogress < 0.6) theColor = colors[5];
                        if (progress.uprogress >= 0.6 && progress.uprogress < 0.7) theColor = colors[6];
                        if (progress.uprogress >= 0.7 && progress.uprogress < 0.8) theColor = colors[7];
                        if (progress.uprogress >= 0.8 && progress.uprogress < 0.9) theColor = colors[8];
                        if (progress.uprogress >= 0.9 && progress.uprogress <= 1)  theColor = colors[9];
                        progressDiagram.push({
                            diagramColor:theColor
                        });
                        smallSunburst.push(theColor);
                        //alert(theColor);
                    });
                    //alert(json_file);
                    $.ajax({
                        dataType: "json",
                        url: json_file,
                        async:false,
                        success:function (objcourse) {
                            var sizes = [];
                            $.each(objcourse.children, function (i_lecture, lecture) {  // lecture
                                var countLecture = 0;
                                if (lecture.children.length > 0) {
                                    $.each(lecture.children, function (i_chapter, chapter) {   // chapter
                                        if (chapter.children.length > 0) {
                                            $.each(chapter.children, function (i_reading, reading) {  // reading
                                                if (reading.children.length > 0) {
                                                    countLecture = countLecture + reading.children.length;
                                                } else {
                                                    countLecture++;
                                                }
                                            })
                                        } else {
                                            countLecture++;
                                        }
                                    });
                                } else {
                                    countLecture++;
                                }
    
                                sizes.push(countLecture);
                               // alert(countLecture);
                            });
    
                            structure = [];
                            structure["name"] = week + " week ago";
                            for(var i=0;i<sizes.length;i++){
                                chapterid = "chapter "+(i+1);
                                structure[chapterid] = sizes[i];
                                //alert(sizes[i]);
                            }
                            bookStructure.push(structure);
    
                        }
                    });


                    var width = 100,
                        height = 150,
                        radius = Math.min(width, height) / 2.5,
                        color = d3.scale.category20c();
        
                    var radius = 70,
                        padding = 0;
        
                    var color = d3.scale.ordinal()
                        .range(smallSunburst);
        
                    var arc = d3.svg.arc()
                        .outerRadius(63)// (radius)
                        .innerRadius(10);//(radius - 30);
        
                    var pie = d3.layout.pie()
                        .sort(null)
                        .value(function (d) {
                            return d.population;
                        });
        
                    color.domain(d3.keys(bookStructure[0]).filter(function (key) {
                            return key !== "name";
                        }
                    ));
        
                    bookStructure.forEach(function (d) {
                        d.ages = color.domain().map(function (name) {
                            return {name:name, population:+d[name]};
                        });
                    })
        
                    var svg = d3.select("#self-comp").selectAll(".pie")
                        .data(bookStructure)
                        .enter().append("svg")
                        .attr("class", "pie")
                        .attr("width", radius * 2)
                        .attr("height", radius * 2.5)
                        .append("g")
                        .attr("transform", "translate(" + radius + "," + radius + ")");
        
                    svg.selectAll(".arc")
                        .data(function (d) {
                            return pie(d.ages);
                        })
                        .enter().append("path")
                        .attr("class", function (d) {
                            return ('arc' + (d.data.name.replace(/ /g, "-")) );
                        })
                        .attr("d", arc)
                        .style("fill", function (d) {
                            return color(d.data.name);
                        });
        
                    svg.append("text")
                        .attr("dy", ".35em")
                        .style("text-anchor", "middle")
                        .attr("y", "75px")
                        .text(function (d) {
                            return d.name;
                        });
                });
            }
        }
    });
//}
}




