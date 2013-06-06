
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
  </head>
  <body>
    <h2>
      Flare code size<br>
      force-directed graph
    </h2>
    <script src="http://d3js.org/d3.v3.min.js"></script>
    <script type="text/javascript">

var w = 1280,
    h = 800,
    node,
    link,
    root;

function distance(d) {
  console.log(d.source.type);
  switch(d.target.type) {
    case "chapter":
      return 150;
    case "section":
      return 10;
    case "tag":
      return 5;
  }
  console.log("magni");
}

var force = d3.layout.force()
    .on("tick", tick)
    .charge(-30)
    .linkDistance(distance)
    .size([w, h - 160]);

var vis = d3.select("body").append("svg:svg")
    .attr("width", w)
    .attr("height", h);

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
      .attr("title", function(d) { return d.name; });

  node.transition()
      .attr("r", function(d) { return d.children ? 4.5 : Math.sqrt(d.size) / 10; });

  // Enter any new nodes.
  node.enter().append("svg:circle")
      .attr("class", "node")
      .attr("cx", function(d) { return d.x; })
      .attr("cy", function(d) { return d.y; })
      .attr("r", function(d) { return d.children ? 4.5 : Math.sqrt(d.size) / 10; })
      .style("fill", color)
      .on("click", click)
      .attr("title", function(d) { return d.name; })
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
  switch (d.type) {
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

