var fields = ["name", "mail", "site"];

$(document).ready(function () {
  // load the name and email field from local storage, if available
  for (var i = 0; i < fields.length; i++) {
    field = fields[i];
    if (localStorage[field]) $("#" + field).val(localStorage[field]);
  };

  $("input").click(saveValues);

  // this is only visible if it makes sense for the thing to be displayed
  $("h2#slogans-title").append("<span style='float:right'></span>");
  $("h2#slogans-title").click(toggleSlogans);
  $("h2#slogans-title").after("<p id='slogans-disclaimer' style='display: none'>By default we hide the existing slogans so you can first think for yourself. <a href='javascript:void(0)' onclick='toggleSlogans()'>Show the existing slogans</a>.");

  toggleSlogans();
  

  // turn certain inputs into required fields
  // this seems odd: but we don't know that we can unrequire them when clicking the 'get new tag' button using JS
  toggleRequired();
  // clicking the 'get new tag' button should not use required fields
  $("input#skip").click(toggleRequired);
});

function saveValues() {
  for (var i = 0; i < fields.length; i++) {
    field = fields[i];
    localStorage[field] = $("#" + field).val();
  }
}

$('.stored').keyup(function () {
  localStorage[$(this).attr("name")] = $(this).val();
});

function toggleSlogans() {
  $("p#slogans-disclaimer").toggle();
  $("div#existing").toggle();

  if ($("div#existing").is(":visible"))
    $("h2#slogans-title span").text("<<<");
  else
    $("h2#slogans-title span").text(">>>");
}

function toggleRequired() {
  var fields = ["textarea#slogan-input", "input#name", "input#mail", "input#check"];

  for (var i = 0; i < fields.length; i++) {
    if ($(fields[i]).prop("required")) // turns undefined into false
      $(fields[i]).prop("required", false);
    else
      $(fields[i]).prop("required", true);
  }
}

function success(tag) {
  // this is a bit hardcoded, sorry
  $("body").append("<div id='notification'>Thanks for suggesting a slogan to <a href='http://stacks.math.columbia.edu/tag/" + tag + "'>tag <code>" + tag + "</code></a>!<span id='cross'>[x]</span></div>").click(function() { $("div#notification").hide(); });
}
