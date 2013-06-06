$(document).ready(function() {
  // make the query input active
  $("input#keywords").focus();

  // insert collapse / expand all links
  // TODO this should somehow always point to the correct files
  $("#results").before("<a href='javascript:void(0)' onclick='$(\"#results pre\").hide();'> <img src='/new/js/jquery-treeview/images/minus.gif'> Collapse all</a>");
  $("#results").before(" <a href='javascript:void(0)' onclick='$(\"#results pre\").show();'><img src='/new/js/jquery-treeview/images/plus.gif'> Expand all</a>");

  // insert toggle links for each result
  pre = $("#results pre");
  for (var i = 0; i < pre.length; i++) {
    el = $(pre[i]);
    el.prev().after('<span class="preview"><a href="javascript:void(0)" onclick="$(\'#' + pre[i].id + '\').toggle();">preview</a>');
  }

  // hide all results by default
  pre.hide();
});
