function displayInfo(node) {
  // element exists, so we show it, while updating its position
  if ($("#" + node.tag + "-tooltip").length) {
    $("#" + node.tag + "-tooltip").css({top: node.y - 10 + "px", left: node.x + 20 + "px"}).fadeIn(100);
  }
  // otherwise we create a new tooltip
  else {
    var tooltipContent = $("<p>")
      .append("Tag " + node.tag + " which points to " + capitalize(node.type) + " " + node.id)
    if (node.name != "")
      tooltipContent.append(" and it is called " + node.name);

    tooltipContent.append("<br>It is contained in the file " + node.file + ".tex");

    var tooltip = $("<div>", {class: "tooltip", id: node.tag + "-tooltip"})
      .append(tooltipContent)
      .css({position: "absolute", top: node.y - 10 + "px", left: node.x + 20 + "px"});

    $('body').append(tooltip);
  }
}
      
function hideInfo(node) {
  $("#" + node.tag + "-tooltip").fadeOut(200);
}
