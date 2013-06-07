
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <style type="text/css">

circle.node {
  cursor: pointer;
  stroke: #000;
  stroke-width: .5px;
}

line.link {
  fill: none;
  stroke: #9ecae1;
  stroke-width: 1.5px;
}

    </style>
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="graphs.js"></script>
    <script type="text/javascript">
      $(document).ready(function () {
        // scroll to where the graph will be created
        setTimeout(centerViewport, 100);

        disableContextMenu();

        createControls();
        $("div#controls").append("<ul>");
        $("div#controls ul").append("<li><a href='javascript:void(0)' onclick=''>expand all nodes</a><br>");
        $("div#controls").append("</ul>");
      });
    </script>
  </head>
  <body>
    <script src="http://d3js.org/d3.v3.min.js"></script>
    <link rel='stylesheet' type='text/css' href='style.css'>
    <script type="text/javascript">

var w = 1000, // TODO dynamic size
    h = 1000,
    node,
    link,
    root;

function distance(d) {
  switch(d.target.nodeType) {
    case "chapter":
      return 150;
    case "section":
      return 10;
    case "tag":
      return 5;
  }
}

var force = d3.layout.force()
    .on("tick", tick)
    .charge(-30)
    .linkDistance(distance)
    .size([w, h - 160]);

var vis = d3.select("body").append("svg")
    .attr("width", w)
    .attr("height", h)
    .attr("id", "graph");

function displaySectionInfo(node) {
  displayTooltip(node, "Section " + node.book_id + ": " + node.tagName);
}

function displayChapterInfo(node) {
  displayTooltip(node, "Chapter " + node.book_id + ": " + node.tagName);
}
// TODO on drag we should not display tooltips

d3.json("data/<?php print $_GET["tag"]; ?>-packed.json", function(json) {
  root = json;
  root.fixed = true;
  root.x = w / 2;
  root.y = h / 2 - 80;
  update();
});

function update() {
  var nodes = flatten(root),
      links = d3.layout.tree().links(nodes);

  // Restart the force layout.
  force
      .nodes(nodes)
      .links(links)
      .start();

  // Update the links…
  link = vis.selectAll("line.link")
      .data(links, function(d) { return d.target.id; });

  // Enter any new links.
  link.enter().insert("svg:line", ".node")
      .attr("class", "link")
      .attr("x1", function(d) { return d.source.x; })
      .attr("y1", function(d) { return d.source.y; })
      .attr("x2", function(d) { return d.target.x; })
      .attr("y2", function(d) { return d.target.y; });

  // Exit any old links.
  link.exit().remove();

  // Update the nodes…
  node = vis.selectAll("circle.node")
      .data(nodes, function(d) { return d.id; })
      .style("fill", color)

  node.transition()
    .attr("r", function(d) { return d.children ? 4.5 : Math.sqrt(d.size) / 10; });

  function displayInfo(node) {
    switch (node.nodeType) {
      case "root":
      case "tag":
        return displayTagInfo(node);
      case "section":
        return displaySectionInfo(node);
      case "chapter":
        return displayChapterInfo(node);
    }
  }

  // Enter any new nodes.
  node.enter().append("svg:circle")
      .attr("class", "node")
      .attr("cx", function(d) { return d.x; })
      .attr("cy", function(d) { return d.y; })
      .attr("r", function(d) { return d.children ? 4.5 : Math.sqrt(d.size) / 10; })
      .style("fill", color)
      .on("click", click)
      .on("mouseover", displayInfo)
      .on("mouseout", hideInfo)
      .call(force.drag);

  // Exit any old nodes.
  node.exit().remove();
}

function tick() {
  link.attr("x1", function(d) { return d.source.x; })
      .attr("y1", function(d) { return d.source.y; })
      .attr("x2", function(d) { return d.target.x; })
      .attr("y2", function(d) { return d.target.y; });

  node.attr("cx", function(d) { return d.x; })
      .attr("cy", function(d) { return d.y; });
}

// Color leaf nodes orange, and packages white or blue.
function color(d) {
  console.log(d.nodeType);
  switch (d.nodeType) {
    case "root":
      return "green";
    case "chapter":
      return "#3182bd";
    case "section":
      return "#c6dbef";
    case "tag":
      return "#fd8d3c";
  }
}

// Toggle children on click.
function click(d) {
  if (d.children) {
    d._children = d.children;
    d.children = null;
  } else {
    d.children = d._children;
    d._children = null;
  }
  update();
}

// Returns a list of all nodes under the root.
function flatten(root) {
  var nodes = [], i = 0;

  function recurse(node) {
    if (node.children) node.size = node.children.reduce(function(p, v) { return p + recurse(v); }, 0);
    if (!node.id) node.id = ++i;
    nodes.push(node);
    return node.size;
  }

  root.size = recurse(root);
  return nodes;
}

    </script>
  </body>
</html>

