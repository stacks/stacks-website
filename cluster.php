<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <script src="http://d3js.org/d3.v3.min.js"></script>
    <script src="graphs.js"></script>
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <link rel='stylesheet' type='text/css' href='style.css'>
    <style type="text/css">
      div#graph {
        cursor: default;
        border: 1px solid #d9d8d1;
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
        createControls();
      });

        var type_map = {
          "definition": d3.rgb("green"),
          "remark": d3.rgb("black"),
          "item": d3.rgb("yellow"),
          "section": d3.rgb("red"),
          "lemma": d3.rgb("orange"),
          "proposition": d3.rgb("blue"),
          "theorem": d3.rgb("purple"),
          "example": d3.rgb("grey"),
        }

var w = 1280,
    h = 800,
    rx = w / 2,
    ry = h / 2,
    m0,
    rotate = 0;


var diameter = 800;

var cluster = d3.layout.tree()
    .size([360, diameter / 2 - 120])
    .separation(function(a, b) { return (a.parent == b.parent ? 1 : 2) / a.depth; });

var diagonal = d3.svg.diagonal.radial()
    .projection(function(d) { return [d.y, d.x / 180 * Math.PI]; });

var div = d3.select("body").append("div") // this is the non-rotating part of the construction
  .attr("id", "graph")

var tagInfo = $("div#graph").append("<div id='tagInfo'>");
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

  function colorType(node) { return type_map[node.type]; }

  var node = vis.selectAll(".node")
  .data(nodes);

  var nodeEnter = node.enter().append("g");

  nodeEnter
      .append("circle")
      .attr("r", 6)
      .attr("transform", function(d) { return "rotate(" + (d.x - 90) + ")translate(" + d.y + ")"; })
      .style("fill", colorType)
      .on("mouseover", displayTag)
      .on("mouseout", displayGeneralInformation)
      .on("click", function(node) { openTag(node, "cluster"); })
      .on("contextmenu", function(node) { openTagNew(node, "cluster"); })

  nodeEnter
      .append("svg:text")
      .style("font-size", "12px")
      .attr("transform", function(d) { return "rotate(" + (d.x - 90) + ")translate(" + d.y + ")"; })
      .attr("text-anchor", "start")
      .on("mouseover", displayTag)
      .on("mouseout", displayGeneralInformation)
      .on("click", function(node) { openTag(node, "cluster"); })
      .on("contextmenu", function(node) { openTagNew(node, "cluster"); })
      .attr("xml:space", "preserve")
      .text(function(d) { return "  " + d.tag; })
});

function displayGeneralInformation() {
  tagInfo = $("div#tagInfo");
  tagInfo.empty();

  tagInfo.append("<p>If you move your cursor over a node you can see the tag's contents.");
}

function displayTag(node) {
  tagInfo = $("div#tagInfo");
  tagInfo.empty();

  console.log(node);

  content = "Tag " + node.tag + " which points to " + capitalize(node.type) + " " + node.book_id;
  if (node.tagName != "")
    content += " and it is called " + node.tagName;
  content += "<br>It is contained in the file " + node.file + ".tex";

  tagInfo.append(content);
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

