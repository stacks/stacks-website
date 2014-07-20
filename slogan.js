$(document).ready(function () {
  // load the name and email field from local storage, if available
  var fields = ["name", "email"];
  for (var i = 0; i < fields.length; i++) {
    field = fields[i];
    if (localStorage[field]) $("#" + field).val(localStorage[field]);
  };

  // if there are slogans we hide them by default and display a link
  if ($("ol#slogans").length == 1) {
    $("h2#slogans-title").append("<span style='float:right'></span>");
    $("h2#slogans-title").click(toggleSlogans);
    $("h2#slogans-title").after("<p id='slogans-disclaimer' style='display: none'>By default we hide the existing slogans so you can first think for yourself. <a href='javascript:void(0)' onclick='toggleSlogans()'>Show the existing slogans</a>.");

    toggleSlogans();
  }

  // turn certain inputs into required fields
  // this seems odd: but we don't know that we can unrequire them when clicking the 'get new tag' button using JS
  toggleRequired();
  // clicking the 'get new tag' button should not use required fields
  $("input#skip").click(toggleRequired);
});

$('.stored').keyup(function () {
  localStorage[$(this).attr("name")] = $(this).val();
});

function toggleSlogans() {
  $("p#slogans-disclaimer").toggle();
  $("ol#slogans").toggle();

  if ($("ol#slogans").is(":visible"))
    $("h2#slogans-title span").text("<<<");
  else
    $("h2#slogans-title span").text(">>>");
}

function toggleRequired() {
  var fields = ["textarea#slogan-input", "input#name", "input#email", "input#tag"];

  for (var i = 0; i < fields.length; i++) {
    if ($(fields[i]).prop("required")) // turns undefined into false
      $(fields[i]).prop("required", false);
    else
      $(fields[i]).prop("required", true);
  }
}
