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
    $("h2#slogans-title").after("<p id='slogans-disclaimer' style='display: none'>By default we hide the existing slogans so you can first think for yourself.");

    toggleSlogans();
  }
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
