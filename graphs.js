// capitalize a string
function capitalize(s) {
  return s.charAt(0).toUpperCase() + s.slice(1);
}

function displayTooltip(node, content) {
  // element exists, so we show it, while updating its position
  if ($("#" + node.tag + "-tooltip").length) {
    $("#" + node.tag + "-tooltip").css({top: node.y - 10 + "px", left: node.x + 20 + "px"}).fadeIn(100);
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
  if (node.tagName != "")
    content += " and it is called " + node.tagName;
  content += "<br>It is contained in the file " + node.file + ".tex";

  displayTooltip(node, content);
}
      
function hideInfo(node) {
  $("#" + node.tag + "-tooltip").fadeOut(200);
}

function centerViewport() {
  x = ($(document).width() - $(window).width()) / 2;
  y = ($(document).height() - $(window).height()) / 2;
  $(document).scrollLeft(x);
  $(document).scrollTop(y);
}

function createControls(tag) {
  // the controls for the graph
  $("body").append("<div id='controls'></div>");
  $("div#controls").append("Tag " + tag + " (<a href='tag/" + tag + "'>show</a>)<br>"); // TODO fix URL
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
