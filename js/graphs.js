// capitalize a string
function capitalize(s) {
  return s.charAt(0).toUpperCase() + s.slice(1);
}

/**
 * these functions are part of the "light" tooltip functionality
 */
// general code to display a tooltip
function displayTooltip(node, content) {
  var position = {top: (d3.event.pageY + 20) + "px", left: (d3.event.pageX + 20) + "px", position: "absolute"};

  // element exists, so we show it, while updating its position
  if ($("#" + node.tag + "-tooltip").length) {
    $("#" + node.tag + "-tooltip").css(position).stop().show(100);
  }
  // otherwise we create a new tooltip
  else {
    var tooltip = $("<div>", {class: "tooltip", id: node.tag + "-tooltip"})
      .append("<p>" + content)
      .css({position: "absolute", width: "auto"})
      .css(position);

    $('body').append(tooltip);
  }
}

// generic tooltip for a tag
function displayTagInfo(node) {
  content = "Tag " + node.tag + " pointing to " + capitalize(node.type) + " " + node.book_id;
  if (node.tagName != "" && (node.type != "equation" && node.type != "item"))
    content += " and it is called " + node.tagName;

  content += "<br>It is contained in ";
  if (node.type != "section")
    content += node.section + ", ";
  if (node.type != "item")
    content += "Chapter " + node.book_id.split(".")[0] + ": ";
  content += node.chapter;

  content += "<br>It has " + (node.numberOfChildren - 1) + " descendant tag(s)";

  displayTooltip(node, content);
}

// in the collapsible graph we have 4 types
function displayNodeInfo(node) {
  switch (node.nodeType) {
    case "root":
      return ;
    case "tag":
      return displayTagInfo(node);
    case "section":
      return displaySectionInfo(node);
    case "chapter":
      return displayChapterInfo(node);
  }
}

function displaySectionInfo(node) {
  displayTooltip(node, "Section " + node.book_id + ": " + node.tagName);
}

function displayChapterInfo(node) {
  displayTooltip(node, "Chapter " + node.book_id + ": " + node.tagName);
}

function hideInfo(node) {
  $("#" + node.tag + "-tooltip").stop().hide(200);
}

/**
 * these functions relate to the "full" tooltip functionality, with previewing
 */
// replace 
function hidePreview() {
  // hide all the preview divs
  $("div#information div.tagPreview").stop().hide();
}

function displayPreviewExplanation() {
  // only show the one explaining the system
  if ($("div#information div#general").length == 0) {
    $("div#information").append("<div id='general' class='tagPreview'>Use the mouse, Luke (touch devices are not completely supported). You can <ul><li>hover over nodes to see information<li>drag things around (except in the cluster layout)<li>(double) click on nodes to see subgraphs or collapse<li>zoom and move around by scrolling and dragging (especially if the preview is bothering you)</ul>").click(displayPreviewExplanation);
  }
  else {
    $("div#information div#general").text("Use the mouse, Luke");
    $("div#information div#general").height("18px");
    $("div#information div#general").stop().show(100);
  }
}

// the full-blown preview
function displayPreview(node) {
  // hide all the divs
  $("div#information div.tagPreview").stop().hide(100);

  // the id used for the tag information
  id = "tagPreview-" + node.tag;

  // check whether there is already a parsed version
  if ($("div#" + id).length > 0) {
    $("div#" + id).toggle(50);
  }
  else {
    $("div#information").append("<div class='tagPreview' id='" + id + "'></div>");
    tagPreview = $("div#" + id);
    
    tagPreview.append("Tag " + node.tag + " points to " + capitalize(node.type) + " " + node.book_id);
    if (node.tagName != "" && node.type != "equation" && node.type != "item")
      tagPreview.append(": " + node.tagName);

    tagPreview.append("<br>It is contained in ");
    if (node.type != "section")
      tagPreview.append(node.section + ", ");
    if (node.type != "item")
      tagPreview.append("Chapter " + node.book_id.split(".")[0] + ": ");
    tagPreview.append(node.chapter);

    tagPreview.append("<br>It has " + (node.numberOfChildren - 1) + " descendant tag(s)");

    tagPreview.append("<blockquote class='rendered' id='" + id + "-content'>");
    if (node.type != "section" && node.type != "subsection") {
      $("blockquote#" + id + "-content").append("<p class='loading'>loading the tag preview</p>");
      url = "../../../data/tag/" + node.tag + "/content/statement";
      $("blockquote#" + id + "-content").append("<div>").load(url,
        function() {
          MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
          $("div#" + id).height("auto");
          $("blockquote#" + id + "-content").height("auto");
      }); 
    }
    else {
      $("blockquote#" + id + "-content").text("Sections and subsections are not displayed in this preview due to size constraints.");
    }
  }
}

/**
 * these functions delegate mouseover events to the correct handler
 */
function displayTagInformation(node) {
  switch ($("input[type='radio']:checked").attr('id')) {
    case "none":
      break;

    case "light":
      if (node.nodeType)
        displayNodeInfo(node);
      else
        displayTagInfo(node);
      break;

    case "full":
    default:
      if (node.nodeType) { // we are in the collapsible case
        if (node.nodeType == "tag") // we want to preview the tag
          displayPreview(node);
        else
          displayNodeInfo(node);
      }
      else 
        displayPreview(node);
      break;
  }
  return false;
}

function hideTagInformation(node) {
  switch ($("input[type='radio']:checked").attr('id')) {
    case "none":
      break;
    case "light":
      hideInfo(node);
      break;
    case "full":
    default:
      if (node.nodeType) { // we are in the collapsible case
        if (node.nodeType == "tag") { // we are currently displaying a tag preview
          hidePreview(node);
          displayPreviewExplanation();
        }
        else
          hideInfo(node);
      }
      else {
        hidePreview(node);
        displayPreviewExplanation();
      }
      break;
  }
}


function centerViewport() {
  x = ($(document).width() - $(window).width()) / 2;
  y = ($(document).height() - $(window).height()) / 2;
  $(document).scrollLeft(x);
  $(document).scrollTop(y);
}

function getLinkTo(tag, type) {
  switch (type) {
    case "cluster":
      return "<a href='cluster'>view clustered</a>";
    case "collapsible":
      return "<a href='collapsible'>view collapsible</a>";
    case "force":
      return "<a href='force'>view force-directed</a>";
  }
}

function createControls(tag, type) {
  // the controls for the graph
  $("body").append("<div id='controls' style='overflow: hidden; min-height: 20px'></div>");
  var text = "<p>Tag " + tag + " (<a href='../../" + tag + "'>show tag</a>, ";
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
  text += ")</p>";

  $("div#controls").append(text);

  // add event listener to minimize the controls
  $("div#controls").click(function(e) {
    if (["P", "FORM", "LI", "DIV"].indexOf(e.target.tagName) < 0) {
      e.stopPropagation();
      return;
    }

    if ($("div#controls").height() == "20")
      $("div#controls").height("auto");
    else
      $("div#controls").height("20px");
    
    e.stopPropagation();
  });

  // for the tag preview
  $("body").append("<div id='information'>");
  displayPreviewExplanation();
}

function createTooltipToggle() {
  text = "";
  text += "Action for tooltip: <form id='tooltipToggle'>";
  text += "<label for='full'><input type='radio' checked='checked' name='tooltipChoice' id='full'>preview tag</label>&nbsp&nbsp;&nbsp&nbsp;";
  text += "<label for='light'><input type='radio' name='tooltipChoice' id='light'>only tag information</label>&nbsp&nbsp;&nbsp&nbsp;";
  text += "<label for='none'><input type='radio' name='tooltipChoice' id='none'>none</label>";
  text += "</form>";

  return text;
}

function disableContextMenu() {
  // disable context menu in graph (for right click to act as new window)
  $("svg").bind("contextmenu", function(e) {
    return false;
  }); 
}

function openTag(node, type) {
  if (!d3.event.ctrlKey)
    window.location.href = "../../" + node.tag + "/graph/" + type;
  else
    window.open("../../" + node.tag + "/graph/" + type, "_blank")
}

function openTagNew(node, type) {
  window.open("../../" + node.tag + "/graph/" + type, "_blank")
}

var typeMap = d3.scale.category10().domain(["definition", "lemma", "item", "section", "remark", "proposition", "theorem", "example"])

function bordersLegend() {
  legend = $("<ul></ul>");
  legend.append("<li><svg height='12' width='12'><circle cx='6' cy='6' r='5' fill='white' class='named' '/></svg> this tag has a name");
  legend.append("<li><svg height='12' width='12'><circle cx='6' cy='6' r='5' fill='white' id='root' '/></svg> root");

  return legend;
}

function typeLegend(types) {
  $("body").append("<div class='legend' id='legendType'></div>");
  $("div#legendType").append("<p>Legend for the type mapping</p>");
  $("div#legendType").append("<ul>");
  for (type in types) {
    $("<li><svg height='10' width='10'><circle cx='5' cy='5' r='5' fill='" + typeMap(type) + "'/></svg>").append(" " + capitalize(type)).appendTo($("div#legendType ul"));
  }

  $("div#legendType").append("<br>");
  $("div#legendType").append(bordersLegend());
}

function namedClass(node) {
  if (node.type == "item" || node.type == "equation")
    return "unnamed";
  
  if (node.tagName != "")
    return "named";
  else
    return "unnamed";
}

// zoom event for force-directed and collapsible
var zoom = d3.behavior.zoom()
  .scaleExtent([0.2, 2])
  .on("zoom", redraw);

var numberOfDrags = 0;

// redraw the svg (or rather the <g> inside <svg>) on a zoom event
function redraw() {
  if (!global["mouseDownOnNode"])
    vis.attr("transform", "translate(" + d3.event.translate + ")" + " scale(" + d3.event.scale + ")");
}

