// capitalize a string
function capitalize(s) {
  return s.charAt(0).toUpperCase() + s.slice(1);
}

function displayTooltip(node, content) {
  // element exists, so we show it, while updating its position
  if ($("#" + node.tag + "-tooltip").length) {
    $("#" + node.tag + "-tooltip").css({top: node.y - 10 + "px", left: node.x + 20 + "px"}).stop().fadeIn(100);
  }
  // otherwise we create a new tooltip
  else {
    var tooltip = $("<div>", {class: "tooltip", id: node.tag + "-tooltip"})
      .append("<p>" + content)
      .css({position: "absolute", top: node.y - 10 + "px", left: node.x + 20 + "px"});

    $('body').append(tooltip);
  }
}

function displayTagInfo(node) {
  content = "Tag " + node.tag + " which points to " + capitalize(node.type) + " " + node.book_id;
  if (node.tagName != "" && (node.type != "equation" && node.type != "item"))
    content += " and it is called " + node.tagName;
  content += "<br>It is contained in the file " + node.file + ".tex";
  // TODO possibly improve this with real chapter name (change parse.py)

  displayTooltip(node, content);
}
      
function hideInfo(node) {
  $("#" + node.tag + "-tooltip").stop().fadeOut(200);
}

function centerViewport() {
  x = ($(document).width() - $(window).width()) / 2;
  y = ($(document).height() - $(window).height()) / 2;
  $(document).scrollLeft(x);
  $(document).scrollTop(y);
}

function getLinkTo(tag, type) {
  switch (type) {
    // TODO fix URLs
    case "cluster":
      return "<a href='cluster.php?tag=" + tag + "'>view clustered</a>";
    case "collapsible":
      return "<a href='collapsible.php?tag=" + tag + "'>view collapsible</a>";
    case "force":
      return "<a href='force.php?tag=" + tag + "'>view force-directed</a>";
  }
}

function createControls(tag, type) {
  // the controls for the graph
  $("body").append("<div id='controls'></div>");
  var text = "Tag " + tag + " (<a href='/tag/" + tag + "'>show tag</a>, ";
  switch (type) {
    case "cluster":
      text += getLinkTo(tag, "collapsible") + ", " + getLinkTo(tag, "force");
      break;
    case "collapsible":
      text += getLinkTo(tag, "cluster") + ", " + getLinkTo(tag, "force");
      break;
    case "force":
      text += getLinkTo(tag, "cluster") + ", " + getLinkTo(tag, "collapsible");
      break;
  }
  text += ")<br>";

  $("div#controls").append(text);
}

function disableContextMenu() {
  // disable context menu in graph (for right click to act as new window)
  $("svg").bind("contextmenu", function(e) {
    return false;
  }); 
}

function openTag(node, type) {
  window.location.href = type + ".php?tag=" + node.tag; // TODO change this to the correct URL naming scheme eventually, probably something like tag/0123/force
}
function openTagNew(node, type) {
  window.open(type + ".php?tag=" + node.tag);
}

var typeMap = d3.scale.category10().domain(["definition", "lemma", "item", "section", "remark", "proposition", "theorem", "example"])

function typeLegend(types) {
  $("body").append("<div class='legend' id='legendType'></div>");
  $("div#legendType").append("Legend for the type mapping");
  $("div#legendType").append("<ul>");
  for (type in types) {
    $("<li><svg height='10' width='10'><circle cx='5' cy='5' r='5' fill='" + typeMap(type) + "'/></svg>").append(" " + capitalize(type)).appendTo($("div#legendType ul"));
  }
}

function namedClass(node) {
  if (node.type == "item" || node.type == "equation")
    return "unnamed";
  
  if (node.tagName != "")
    return "named";
  else
    return "unnamed";
}

