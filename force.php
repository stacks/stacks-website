<?php
  // TODO check for inexisting file
  $filename = "data/" . $_GET["tag"] . "-force.json";
  $filesize = filesize($filename);
  $size = 500 + 10 * $filesize / 1000;
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <style>
      .named {
        stroke-width: 1.5px;
        stroke: black;
      }

      #root {
        stroke-width: 2px;
        stroke-dasharray: 2, 2;
        stroke: black;
      }
      
      .link {
        stroke: #999;
        stroke-opacity: .6;
      }
      
      body {
        width: <?php print $size; ?>px;
        height: <?php print $size; ?>px;
      }
  
    </style>
    <link rel='stylesheet' type='text/css' href='style.css'>

    <script src="http://d3js.org/d3.v3.min.js"></script>
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

    <script src="graphs.js"></script>
    <script type="text/javascript">
      var colorMapping;
  
      function toggleLegend() {
        $("div.legend").hide();

        switch (colorMapping) {
          case global["colorHeat"]:
            $("div#legendHeat").show();
            return;
          case global["colorType"]:
            $("div#legendType").show();
            return;
          case global["colorChapters"]:
            $("div#legendChapters").show();
            return;
        }
      }

      function toggleHeat() {
        global["node"].style("fill", global["colorHeat"]);

        colorMapping = global["colorHeat"];
        toggleLegend();
      }

      function toggleType() {
        global["node"].style("fill", global["colorType"]);

        colorMapping = global["colorType"];
        toggleLegend();
      }

      function toggleChapters() {
        global["node"].style("fill", global["colorChapters"]);

        colorMapping = global["colorChapters"];
        toggleLegend();
      }

      $(document).ready(function () {
        // scroll to where the graph will be created
        setTimeout(centerViewport, 100);

        disableContextMenu();

        createControls();
        $("div#controls").append("<ul>");
        $("div#controls ul").append("<li><a href='javascript:void(0)' onclick='toggleHeat();'>view as heatmap</a><br>");
        $("div#controls ul").append("<li><a href='javascript:void(0)' onclick='toggleType();'>view types</a>");
        //$("div#controls ul").append("<li><a href='javascript:void(0)' onclick='toggleChapters();'>view chapters</a>");
        $("div#controls").append("</ul>");
      });
    </script>
  </head>
  <body>
    <script type="text/javascript">
      var width = <?php print $size; ?>,
        height = <?php print $size; ?>;
      
      var force = d3.layout.force()
        .charge(-500)
        .linkDistance(10)
        .gravity(.5)
        .size([width, height]);
      
      var svg = d3.select("body").append("svg")
        .attr("width", width)
        .attr("height", height)
        .attr("id", "graph");

      var global = Array(); // this catches some things that need to be available globally
      
      result = d3.json("<?php print $filename; ?>", function(error, graph) {
        var depth = 0
        for (var i = 0; i < graph.nodes.length; i++)
          depth = Math.max(depth, graph.nodes[i].depth);
        // heat scale
        var heatMap = d3.scale.linear()
          .domain([0, depth])
          .range(["red", "blue"]);

        var typeMap = d3.scale.category10().domain(["definition", "lemma", "item", "section", "remark", "proposition", "theorem", "example"])

        var chapters = {};
        for (var i = 0; i < graph.nodes.length; i++) 
          chapters[graph.nodes[i].file] = true;
        var i = 0;
        for (chapter in chapters)
          chapters[chapter] = i++;

        var chapterMap = d3.scale.linear().domain([0, Object.keys(chapters).length]).range(["green", "yellow"]);

        function colorHeat(node) { return heatMap(node.depth); }
        function colorType(node) { return typeMap(node.type); }
        function colorChapters(node) { return chapterMap(chapters[node.file]); }

        global["colorHeat"] = colorHeat;
        global["colorType"] = colorType;
        global["colorChapters"] = colorChapters;

        force
          .nodes(graph.nodes) 
          .links(graph.links)
          .start();

        var link = svg.selectAll(".link")
          .data(graph.links)
          .enter().append("line")
          .attr("class", "link")

        var node = svg.selectAll(".node")
          .data(graph.nodes)
          .enter().append("circle")
          .attr("class", function(d) { console.log(d.tagName); if (d.tagName != "") { return "named"; } else { return "unnamed"; } })
          .attr("id", function(d) { if (d.depth == 0) { return "root"; } })
          .attr("r", function(d) { return 4 * Math.pow(parseInt(d.size) + 1, 1 / 3); })
          .style("fill", function(d) { colorMapping = colorHeat; return colorHeat(d); })
          .on("mouseover", displayTagInfo)
          .on("mouseout", hideInfo)
          .on("click", function(node) { openTag(node, "force"); })
          .on("contextmenu", function(node) { openTagNew(node, "force"); })
          .call(force.drag);

        global["node"] = node;

        force.on("tick", function() {
          link
            .attr("x1", function(d) { return d.source.x; })
            .attr("y1", function(d) { return d.source.y; })
            .attr("x2", function(d) { return d.target.x; })
            .attr("y2", function(d) { return d.target.y; });
           
          node
            .attr("cx", function(d) { return d.x; })
            .attr("cy", function(d) { return d.y; });
        });

        
        // add legend for the type coloring
        var types = {};
        for (var i = 0; i < graph.nodes.length; i++) 
          types[graph.nodes[i].type] = true;

        $("body").append("<div class='legend' id='legendType'></div>");
        $("div#legendType").append("Legend for the type mapping");
        $("div#legendType").append("<ul>");
        for (type in types) {
          $("<li><svg height='10' width='10'><circle cx='5' cy='5' r='5' fill='" + typeMap(type) + "'/></svg>").append(" " + capitalize(type)).appendTo($("div#legendType ul"));
        }

        // add legend for the heat coloring
        $("body").append("<div class='legend' id='legendHeat'></div>");
        $("div#legendHeat").append("Legend for the heat mapping<br>");
        $("div#legendHeat").append("root node&nbsp;&nbsp;");
        for (var i = 0; i <= depth; i++) 
          $("<svg height='10' width='10'><circle cx='5' cy='5' r='5' fill='" + heatMap(i) + "'/></svg>").appendTo($("div#legendHeat"));
        $("div#legendHeat").append("&nbsp;&nbsp;children");

        // add legend for the chapter coloring
        $("body").append("<div class='legend' id='legendChapters'></div>");
        $("div#legendChapters").append("<p>Legend for the chapter mapping</p>");
        $("div#legendChapters").append("<ul>");
        for (chapter in chapters) {
          $("<li><svg height='10' width='10'><circle cx='5' cy='5' r='5' fill='" + chapterMap(chapters[chapter]) + "'/></svg>").append(" " + chapter).appendTo($("div#legendChapters ul"));
        }
        // TODO it would be awesome if the chapters legend had mouseOvers to indicate which results are in which chapter: making all the other chapters slightly lighter for instance

        toggleLegend();
      });
    </script>
  </body>
</html>
