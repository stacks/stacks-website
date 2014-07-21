$(document).ready(function() {
  // make the query input active
  $("input#keywords").focus();

  // insert collapse / expand all links
  $("div#allResults").before("<a href='javascript:void(0)' onclick='$(\"ul.results pre\").hide();'> <img src='js/jquery-treeview/images/minus.gif'> Collapse all</a>");
  $("div#allResults").before(" <a href='javascript:void(0)' onclick='$(\"ul.results pre\").show();'><img src='js/jquery-treeview/images/plus.gif'> Expand all</a>");

  // insert toggle links for each result
  pre = $("ul.results pre");
  for (var i = 0; i < pre.length; i++) {
    el = $(pre[i]);
    el.prev().append('<span class="preview"><a href="javascript:void(0)" onclick="$(\'#' + pre[i].id + '\').toggle();">preview</a>');
  }

  // hide all results by default
  pre.hide();
});
