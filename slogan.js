$(document).ready(function () {
  var fields = ["name", "email", "site"];
  for (var i = 0; i < fields.length; i++) {
    field = fields[i];
    if (localStorage[field]) $("#" + field).val(localStorage[field]);
  };
});

$('.stored').keyup(function () {
  localStorage[$(this).attr("name")] = $(this).val();
});
