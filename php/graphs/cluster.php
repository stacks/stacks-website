<?php

require_once("../config.php");
$config = array_merge($config, parse_ini_file("../../config.ini"));

require_once("../general.php");
require_once("general.php");

tagForGraphCheck($_GET["tag"], "tree");

$filename = href("data/tag/" . strtoupper($_GET['tag']) . "/graph/cluster");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <script src="<?php print $config["D3"];?>"></script>
    <script src="<?php print href("js/graphs.js"); ?>"></script>
    <script src="<?php print $config["jQuery"];?>"></script>
<?php
print printMathJax();
?>
    <link rel='stylesheet' type='text/css' href='<?php print href("css/graphs.css"); ?>'>
    <link rel='stylesheet' type='text/css' href='<?php print href("css/tag.css"); ?>'>
    <title>Stacks Project &mdash; Cluster graph for tag <?php print htmlentities($_GET["tag"]); ?></title>
    <link rel='icon' type='image/vnd.microsoft.icon' href='<?php print href("stacks.ico"); ?>'> 
    <link rel='shortcut icon' type='image/vnd.microsoft.icon' href='<?php print href("stacks.ico"); ?>'> 
    <style type="text/css">
      body {
        width: 1300px;
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


var w = 1600,
    h = 900,
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

d3.json("<?php print $filename; ?>", function(json) {
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
      .on("mouseover", displayTagInformation)
      .on("mouseout", hideTagInformation)
      .attr("class", namedClass)
      .attr("id", function(d) { if (d.depth == 0) { return "root"; } })
      .on("click", function(node) { openTag(node, "cluster"); })
      .on("contextmenu", function(node) { openTagNewNew(node, "cluster"); })

  nodeEnter
      .append("svg:text")
      .style("height", "15px")
      .style("vertical-align", "middle")
      .style("font-size", "10px")
      .attr("transform", function(d) { return "rotate(" + (d.x - 90) + ")translate(" + d.y + ")"; }) // TODO improve text rotation
      .attr("text-anchor", "start")
      .on("mouseover", displayTagInformation)
      .on("mouseout", hideTagInformation)
      .on("click", function(node) { openTag(node, "cluster"); })
      .on("dblclick", function(node) { openTag(node, "cluster"); })
      .on("contextmenu", function(node) { openTagNew(node, "cluster"); })
      .attr("xml:space", "preserve")
      .text(function(d) { return "  " + d.tag; })

  // add legend for the type coloring
  var types = {};
  for (var i = 0; i < nodes.length; i++) 
    types[nodes[i].type] = true;
  typeLegend(types);


  function minimizeLegend() {
    if ($("div.legend").height() == "18")
      $("div.legend").each(function() { $(this).height("auto");  });
    else
      $("div.legend").each(function() { $(this).height("18px").css("overflow", "hidden");});
  }
  $("div.legend").each(function() { $(this).click(minimizeLegend); } );
});

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

