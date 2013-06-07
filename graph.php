<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <style>
      .named {
        stroke-width: 1.5px;
        stroke: black;
      }

      .root {
        stroke-width: 2px;
        stroke-dasharray: 2, 2;
        stroke: black;
      }
      
      .link {
        stroke: #999;
        stroke-opacity: .6;
      }
      
      .tooltip p {
        font-size: .9em;
        border-radius: 5px;
        border: 1px solid black;
        background-color: white;
        padding: 2px;
      }

<?php
// TODO check for inexisting file
$filesize = filesize("data/" . $_GET["tag"] . "-force.json");
$size = 500 + 10 * $filesize / 1000;
?>

      body {
        width: <?php print $size; ?>px;
        height: <?php print $size; ?>px;
      }
      
      svg {
        border: 1px solid black;
      }
  
    </style>
    <script src="http://d3js.org/d3.v3.min.js"></script>
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script type="text/javascript">
      function centerViewport() {
        x = ($(document).width() - $(window).width()) / 2;
        y = ($(document).height() - $(window).height()) / 2;
        $(document).scrollLeft(x);
        $(document).scrollTop(y);
      }

      $(document).ready(function () {
        setTimeout(centerViewport, 100);
        $(document).bind("contextmenu",function(e){
          return false;
        }); 
      });
    </script>
  </head>
  <body>
    <script type="text/javascript">

      function scrollToGraph() {
        console.log("scroll");
        console.log(window.innerHeight);
        window.scrollTo(
          (document.body.offsetWidth -document.documentElement.offsetWidth )/2,
          (document.body.offsetHeight-window.innerHeight)/2);
      }
    </script>
<?php print $size; ?>
    <script>
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

      
      result = d3.json("data/<?php print $_GET["tag"]; ?>-force.json", function(error, graph) {
        var depth = 0
        for (var i = 0; i < graph.nodes.length; i++)
          depth = Math.max(depth, graph.nodes[i].depth);
        // heat scale
        var heat = d3.scale.linear()
          .domain([0, depth])
          .range(["red", "blue"]);

        var typeMap = d3.scale.category10().domain(["definition", "lemma", "item", "section", "remark", "proposition", "theorem", "example"])

        force
          .nodes(graph.nodes) 
          .links(graph.links)
          .start();

        var link = svg.selectAll(".link")
          .data(graph.links)
          .enter().append("line")
          .attr("class", "link")
      
        function displayInfo(node) {
          // element exists, so we show it, while updating its position
          if ($("#" + node.tag + "-tooltip").length) {
            $("#" + node.tag + "-tooltip").css({top: node.y - 10 + "px", left: node.x + 20 + "px"}).fadeIn(100);
          }
          // otherwise we create a new tooltip
          else {
            var tooltipContent = $("<p>")
              .append("tag " + node.tag)
              .append(": " + node.name)
              .append("<br>" + node.type);
      
            var tooltip = $("<div>", {class: "tooltip", id: node.tag + "-tooltip"})
              .append(tooltipContent)
              .css({position: "absolute", top: node.y - 10 + "px", left: node.x + 20 + "px"});
      
            $('body').append(tooltip);
          }
        }
      
        function hideInfo(node) {
          $("#" + node.tag + "-tooltip").fadeOut(200);
        }

        function openTag(node) {
          window.location.href = "graph.php?tag=" + node.tag;
        }
        function openTagNew(node) {
          window.open("graph.php?tag=" + node.tag);
        }

        function colorHeat(node) { return heat(node.depth); }
        function colorType(node) { return typeMap(node.type); }

        var node = svg.selectAll(".node")
          .data(graph.nodes)
          .enter().append("circle")
          .attr("class", function(d) { if (d.name != "") { return "named"; } else { return "unnamed"; } })
          .attr("class", function(d) { if (d.depth == 0) { return "root"; } })
          .attr("r", function(d) { return 4*Math.pow(parseInt(d.size)+1, 1/3); }) // control the size
          .style("fill", colorType)
          .on("mouseover", displayInfo)
          .on("mouseout", hideInfo)
          .on("click", openTag)
          .on("contextmenu", openTagNew)
          .call(force.drag);

        console.log(node[0][0]);
      
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
      });
 //     $(document).ready( function() {

 //       console.log((document.body.offsetWidth -document.documentElement.offsetWidth )/2);
 //       console.log((document.body.offsetHeight-document.documentElement.offsetHeight)/2);
 //       console.log(document.documentElement.offsetHeight);
 //       window.scrollTo(2500, 2500);
 //});
    </script>
  </body>
</html>
