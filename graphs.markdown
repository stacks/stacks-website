Some ideas and examples for the dependency graph.

1. Right now GraphViz is used to output the `png` (see [`stacks-projects/graph.py`](https://github.com/stacks/stacks-project/blob/master/scripts/graph.py)) and using [canviz](http://code.google.com/p/canviz/) this could be rendered in the browser
  * same look and feel as current graphs
  * hard to navigate, is hyperlinking possible?
  * not very active it seems
2. Use a different tool (see below for candidates), choices to make:
  * what type of graph: treeview, or force-directed graph, or ...?
  * how much navigation / information displayed: tag number, information on location, maybe even the statement?
  * what type of view: integrate in layout or not? as some graphs will be *huge* this will pose a problem
  * colours: related to chapters, or to the level in the dependency graph? other ideas? yes: type of the tag (so you immediately recognise the definitions etc)
  * have a visual indication of which nodes have a name
  * clustering per chapter? there are no forward-references, so this could work

Technical questions to solve:

1. how to implement this? use Python to generate the output, or encode tree structure in database and generate dynamically, or ...?
2. layout (see above)
3. ...


Tools:

* [`d3.js`](http://d3js.org/) seems the best candidate at first glance, I am particularly interested in http://mbostock.github.io/d3/talk/20111018/tree.html
* [`sigma.js`](http://sigmajs.org/examples.html) also very promising
* other more light-weight contenders (a myriad of them)
