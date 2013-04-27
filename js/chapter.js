$(document).ready(function() {
  // remove all empty lists
  $("#treeview ul").each(
    function() {
      var element = $(this);
      if (element.children().length == 0) {
        element.remove();
      }
    }
  ); 
  // initialize treeview
  $("#treeview").treeview( { control: "#control", collapsed: true, } )
});
