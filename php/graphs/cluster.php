<?php
require_once("../../stacks-website-new/php/general.php"); # TODO fix path
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <script src="http://d3js.org/d3.v3.min.js"></script>
    <script src="graphs.js"></script> <!-- TODO fix path -->
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<?php
print printMathJax();
?>
    <link rel='stylesheet' type='text/css' href='style.css'>
    <link rel='stylesheet' type='text/css' href='../../stacks-website-new/css/tag.css'> <!-- TODO fix URL -->
    <style type="text/css">
      body {
        width: 1100px;
      }

      div#graph {
        cursor: default;
      }
      
      path.arc {
        cursor: move;
        fill: #fff;
      }
      
      circle {
        cursor: pointer;
      }
      
      .node {
        font-size: 10px;
        pointer-events: none;
      }
      
      .link {
        fill: none;
        stroke: #ccc;
        stroke-width: 1.5px;
      }
    </style>
  </head>
  <body>
    <script type="text/javascript">
      $(document).ready(function () {
        disableContextMenu();
        createControls("<?php print $_GET["tag"]; ?>", "cluster");

      });


var w = 1280,
    h = 800,
    rx = w / 2,
    ry = h / 2,
    m0,
    rotate = 0;


var diameter = 800;

var cluster = d3.layout.cluster()
    .size([360, diameter / 2 - 120])
    .separation(function(a, b) { return (a.parent == b.parent ? 1 : 2) / a.depth; });

var diagonal = d3.svg.diagonal.radial()
    .projection(function(d) { return [d.y, d.x / 180 * Math.PI]; });

var div = d3.select("body").append("div") // this is the non-rotating part of the construction
  .attr("id", "graph")

var information = $("body").append("<div id='information'>");
displayGeneralInformation();

var svg = div.append("div") // this is the rotating part of the construction
    .style("width", w + "px")
    .style("height", w + "px");

var vis = svg.append("svg")
    .attr("width", w)
    .attr("height", w)
  .append("g")
    .attr("transform", "translate(" + rx + "," + ry + ")");

vis.append("path")
    .attr("class", "arc")
    .attr("d", d3.svg.arc().innerRadius(ry - 120).outerRadius(ry).startAngle(0).endAngle(2 * Math.PI))
    .on("mousedown", mousedown);

d3.json("data/<?php print $_GET['tag']; ?>-tree.json", function(json) {
  var nodes = cluster.nodes(json);

  var link = vis.selectAll("path.link")
      .data(cluster.links(nodes))
    .enter().append("svg:path")
      .attr("class", "link")
      .attr("d", diagonal);

  function colorType(node) { return typeMap(node.type); }

  var node = vis.selectAll(".node")
  .data(nodes);

  var nodeEnter = node.enter().append("g");

  nodeEnter
      .append("circle")
      .attr("r", 6)
      .attr("transform", function(d) { return "rotate(" + (d.x - 90) + ")translate(" + d.y + ")"; })
      .style("fill", colorType)
      .on("mouseover", displayTag)
      .attr("class", namedClass)
      .attr("id", function(d) { if (d.depth == 0) { return "root"; } })
      .on("mouseout", displayGeneralInformation)
      .on("click", function(node) { openTag(node, "cluster"); })
      .on("contextmenu", function(node) { openTagNew(node, "cluster"); })

  nodeEnter
      .append("svg:text")
      .style("height", "15px")
      .style("vertical-align", "middle")
      .style("font-size", "10px")
      .attr("transform", function(d) { console.log(d.x); return "rotate(" + (d.x - 90) + ")translate(" + d.y + ")"; }) // TODO improve text rotation
      .attr("text-anchor", "start")
      .on("mouseover", displayTag)
      .on("mouseout", displayGeneralInformation)
      .on("click", function(node) { openTag(node, "cluster"); })
      .on("contextmenu", function(node) { openTagNew(node, "cluster"); })
      .attr("xml:space", "preserve")
      .text(function(d) { return "  " + d.tag; })

  // add legend for the type coloring
  var types = {};
  for (var i = 0; i < nodes.length; i++) 
    types[nodes[i].type] = true;
  typeLegend(types);
});

function displayGeneralInformation() {
  // hide all the divs
  $("div#information div.tagInfo").hide();

  // only show the one explaining the system
  if ($("div#information div#general").length == 0)
    $("div#information").append("<div id='general' class='tagInfo'>Use the mouse, Luke");
  else
    $("div#information div#general").show();
}

function displayTag(node) {
  // hide all the divs
  $("div#information div.tagInfo").hide();

  // the id used for the tag information
  id = "tagInfo-" + node.tag;

  // check whether there is already a parsed version
  if ($("div#" + id).length > 0) {
    $("div#" + id).toggle(50);
  }
  else {
    $("div#information").append("<div class='tagInfo' id='" + id + "'></div>");
    tagInfo = $("div#" + id);
    
    tagInfo.append("Tag " + node.tag + " points to " + capitalize(node.type) + " " + node.book_id);
    if (node.tagName != "")
      tagInfo.append(": " + node.tagName);

    tagInfo.append("<blockquote class='rendered' id='" + id + "-content'>");
    if (node.type != "section" && node.type != "subsection") {
      url = "/new/stacks-graphs/tag.php?tag=" + node.tag + "&type=statement";
      $("blockquote#" + id + "-content").load(url, function() { MathJax.Hub.Queue(["Typeset",MathJax.Hub]); }); // TODO change URL
    }
    else {
      $("blockquote#" + id + "-content").text("Sections and subsections are not displayed in this preview due to size constraints.");
    }
  }
}

d3.select(window)
    .on("mousemove", mousemove)
    .on("mouseup", mouseup);

function mouse(e) {
  return [e.pageX - rx, e.pageY - ry];
}

function mousedown() {
  m0 = mouse(d3.event);
  d3.event.preventDefault();
}

function mousemove() {
  if (m0) {
    var m1 = mouse(d3.event),
        dm = Math.atan2(cross(m0, m1), dot(m0, m1)) * 180 / Math.PI,
        tx = "translate3d(0," + (ry - rx) + "px,0)rotate3d(0,0,0," + dm + "deg)translate3d(0," + (rx - ry) + "px,0)";
    svg
        .style("-moz-transform", tx)
        .style("-ms-transform", tx)
        .style("-webkit-transform", tx);
  }
}

function mouseup() {
  if (m0) {
    var m1 = mouse(d3.event),
        dm = Math.atan2(cross(m0, m1), dot(m0, m1)) * 180 / Math.PI,
        tx = "rotate3d(0,0,0,0deg)";

    rotate += dm;
    if (rotate > 360) rotate -= 360;
    else if (rotate < 0) rotate += 360;
    m0 = null;

    svg
        .style("-moz-transform", tx)
        .style("-ms-transform", tx)
        .style("-webkit-transform", tx);

    vis
        .attr("transform", "translate(" + rx + "," + ry + ")rotate(" + rotate + ")")
      .selectAll("g.node text")
        .attr("dx", function(d) { return (d.x + rotate) % 360 < 180 ? 8 : -8; })
        .attr("text-anchor", function(d) { return (d.x + rotate) % 360 < 180 ? "start" : "end"; })
        .attr("transform", function(d) { return (d.x + rotate) % 360 < 180 ? null : "rotate(180)"; });
  }
}

function cross(a, b) {
  return a[0] * b[1] - a[1] * b[0];
}

function dot(a, b) {
  return a[0] * b[0] + a[1] * b[1];
}

    </script>
  </body>
</html>

