function toggleSection(name, text) {
  $(document).ready(function() {
    // change <<< into >>> and vice versa
    if ($("div#" + name).is(":visible")) {
      $("h2#" + name + "-header span").text(">>>");
      $("h2#" + name + "-header").prop("title", "show " + text);
    }
    else {
      $("h2#" + name + "-header span").text("<<<");
      $("h2#" + name + "-header").prop("title", "hide " + text);
    }

    $("div#" + name).toggle();
  });
}

function toggableSection(name, text, defaultHideShow) {
  if (defaultHideShow == "hide") {
    $("div#" + name).toggle();
  }
  // make section header look like link
  $("h2#" + name + "-header").css("cursor", "pointer");
  // make the h2 for the section act like a toggle
  if (defaultHideShow == "show") {
    $("h2#" + name + "-header").append("<span style='float: right;'>&lt;&lt;&lt;</span>");
    $("h2#" + name + "-header").prop("title", "hide input form for comments");
  } else if (defaultHideShow == "hide") {
    $("h2#" + name + "-header").append("<span style='float: right;'>&gt;&gt;&gt;</span>");
    $("h2#" + name + "-header").prop("title", "show input form for comments");
  }
  $("h2#" + name + "-header").click(function() { toggleSection(name, text); });
}

var fields = ["name", "mail", "site"];

$(document).ready(function() {
  // hide code display and the link to the rendered result
  $("div#code, p#rendered-link").toggle();
  // associate code / rendered toggle to both links
  $("p#code-link a, p#rendered-link a").click(function(e) {
    // prevent movement
    e.preventDefault();

    $("blockquote.rendered, p#code-link, div#code, p#rendered-link").toggle();
  });

  // load the name and email field from local storage, if available
  for (var i = 0; i < fields.length; i++) {
    field = fields[i];
    if (localStorage[field]) $("#" + field).val(localStorage[field]);
  };

  $("input, textarea").click(saveValues);

  // add toggle for citations
  $("h2#citation-header").append("<span style='float: right;'>&gt;&gt;&gt;</span>").click(function(e) {
    $("div#citation-text-more").toggle();

    if ($("div#citation-text-more").is(":visible"))
      $("h2#citation-header span").text("<<<");
    else
      $("h2#citation-header span").text(">>>");
  });

  // hide the extra information for citations by default
  $("div#citation-text-more").toggle();

  toggableSection("history", "historical remarks", "show");
  toggableSection("references", "references", "show");
  toggableSection("comments", "comments", "show");
  toggableSection("comment-input", "input form for comments", "hide");
});

function saveValues() {
  for (var i = 0; i < fields.length; i++) {
    field = fields[i];
    localStorage[field] = $("#" + field).val();
  }
}

$('.stored').keyup(function () {
  console.log("stored");
  localStorage[$(this).attr("name")] = $(this).val();
});

// function to display a "copy this to the clipboard" message
function copyToClipboard(text) {
  window.prompt ("Copy to clipboard: Ctrl+C (or Cmd+C), Enter", text);
}
