function toggleSection(name, text) {
  $(document).ready(function() {
    // change <<< into >>> and vice versa
    if ($("div#" + name).is(":visible")) {
      $("h2#" + name + "-header span").text('>>>');
      $("h2#" + name + "-header").prop("title", "show " + text);
    }
    else {
      $("h2#" + name + "-header span").text("<<<");
      $("h2#" + name + "-header").prop("title", "hide " + text);
    }

    $("div#" + name).toggle();
  });
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

  // make history header look like link
  $("h2#history-header").css("cursor", "pointer");
  // hide history section, and add the correct toggle symbol
  $("h2#history-header").append("<span style='float: right;'>&lt;&lt;&lt;</span>");
  $("h2#history-header").prop("title", "hide historical remarks");
  // make the h2 for the history act like a toggle
  $("h2#history-header").click(function () { toggleSection("history", "historical remarks"); });

  // make references header look like link
  $("h2#references-header").css("cursor", "pointer");
  // hide references section, and add the correct toggle symbol
  $("h2#references-header").append("<span style='float: right;'>&lt;&lt;&lt;</span>");
  $("h2#references-header").prop("title", "hide references");
  // make the h2 for the references act like a toggle
  $("h2#references-header").click(function () { toggleSection("references", "references"); });

  // make comments header look like link
  $("h2#comments-header").css("cursor", "pointer");
  // hide comment section, and add the correct toggle symbol
  $("h2#comments-header").append("<span style='float: right;'>&lt;&lt;&lt;</span>");
  $("h2#comments-header").prop("title", "hide comments");
  // make the h2 for the comments act like a toggle
  $("h2#comments-header").click(function () { toggleSection("comments", "comments"); });

  // hide comment input section by default
  $('div#comment-input').toggle();
  // make comment input header look like link
  $("h2#comment-input-header").css("cursor", "pointer");
  // make the h2 for the comment input act like a toggle
  $('h2#comment-input-header').append("<span style='float: right;'>&gt;&gt;&gt;</span>");
  $("h2#comment-input-header").prop("title", "show input form for comments");
  $('h2#comment-input-header').click(function() { toggleSection("comment", "input form for comments"); });
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
