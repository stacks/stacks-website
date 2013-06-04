function toggleComments() {
  $(document).ready(function() {
    // change <<< into >>> and vice versa
    if ($("div#comments").is(":visible")) {
      $("h2#comments-header span").text('>>>');
      $("h2#comments-header").prop("title", "show comments");
    }
    else {
      $("h2#comments-header span").text("<<<");
      $("h2#comments-header").prop("title", "hide comments");
    }

    $("div#comments").toggle();
  });
}

$(document).ready(function() {
  // hide code display and the link to the rendered result
  $("div#code, p#rendered-link").toggle();
  // associate code / rendered toggle to both links
  $("p#code-link a, p#rendered-link a").click(function(e) {
    // prevent movement
    e.preventDefault();

    $("blockquote#rendered, p#code-link, div#code, p#rendered-link").toggle();
  });

  // make comments header look like link
  $("h2#comments-header").css("cursor", "pointer");
  // hide comment section, and add the correct toggle symbol
  $("h2#comments-header").append("<span style='float: right;'>&lt;&lt;&lt;</span>");
  $("h2#comments-header").prop("title", "hide comments");
  // make the h2 for the comments act like a toggle
  $("h2#comments-header").click(toggleComments);

  // hide comment input section by default
  $('div#comment-input').toggle();
  // make comment input header look like link
  $("h2#comment-input-header").css("cursor", "pointer");
  // make the h2 for the comment input act like a toggle
  $('h2#comment-input-header').append("<span style='float: right;'>&gt;&gt;&gt;</span>");
  $("h2#comment-input-header").prop("title", "show input form for comments");
  $('h2#comment-input-header').click(function() {
    $('div#comment-input').toggle();

    // change <<< into >>> and vice versa
    if ($('div#comment-input').is(':visible')) {
      $('h2#comment-input-header span').text('<<<');
      $("h2#comment-input-header").prop("title", "hide input form for comments");
    }
    else {
      $('h2#comment-input-header span').text('>>>');
      $("h2#comment-input-header").prop("title", "show input form for comments");
    }
  });
});

// function to display a "copy this to the clipboard" message
function copyToClipboard(text) {
  window.prompt ("Copy to clipboard: Ctrl+C (or Cmd+C), Enter", text);
}
