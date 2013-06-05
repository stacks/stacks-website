import json
from collections import deque, defaultdict
import sqlite3
from functions import *

def find_tag(label, label_tags):
    if not label in label_tags:
        return "ZZZZ"
    else:
        return label_tags[label]

path = get_path()

labels = []

lijstje = list_text_files(path)
# Just pick one input file:
#lijstje = ['sites']

# All tags
tags = get_tags(path)

# Dictionary labels --> tags
label_tags = dict((tags[n][1], tags[n][0]) for n in range(0, len(tags)))

# Dictionary tags --> height in graph
tags_nr = dict()
n = 0
while n < len(tags):
    tags_nr[tags[n][0]] = 0
    n = n + 1
tags_nr['ZZZZ'] = 0

# Dictionary tags --> referenced tags
tags_refs = dict()
n = 0
while n < len(tags):
    tags_refs[tags[n][0]] = []
    n = n + 1
tags_refs['ZZZZ'] = []

ext = ".tex"
for name in lijstje:
    filename = path + name + ext
    tex_file = open(filename, 'r')
    in_proof = 0
    line_nr = 0
    verbatim = 0
    next_proof = 0
    refs_proof = []
    for line in tex_file:

        # Update line number
        line_nr = line_nr + 1

        # Check for verbatim, because we do not check correctness
        # inside verbatim environment.
        verbatim = verbatim + beginning_of_verbatim(line)
        if verbatim:
            if end_of_verbatim(line):
                verbatim = 0
            continue

        # Find label if there is one
        label = find_label(line)
        if label:
            if next_proof:
                proof_label = name + "-" + label
                proof_tag = find_tag(proof_label, label_tags)

        # Reset boolean
        next_proof = 0

        # Beginning environment?
        if beginning_of_env(line):
            if proof_env(line):
                next_proof = 1

        # In proofs
        if in_proof:
            if not proof_tag == 'ZZZZ':
                refs = find_refs(line, name)
                refs_proof.extend(refs)
            if end_of_proof(line) and not proof_tag == 'ZZZZ':
                refs_proof_set = set(refs_proof)
                refs_proof_set.discard('ZZZZ')
                refs_proof = list(refs_proof_set)
                nr = -1
                tags_proof = []
                n = 0
                while n < len(refs_proof):
                    ref_tag = find_tag(refs_proof[n], label_tags)
                    tags_proof = tags_proof + [ref_tag]
                    nr_ref = tags_nr[ref_tag]
                    if nr_ref > nr:
                        nr = nr_ref
                    n = n + 1
                tags_nr[proof_tag] = nr + 1
                tags_refs[proof_tag] = tags_proof
                refs_proof = []
                in_proof = 0
        else:
            in_proof = beginning_of_proof(line)

    tex_file.close()


def print_whole_graph():
    print "digraph dependencies {"
    print
    print "edge [dir=back]"
    print
    n = 0
    while n < len(tags):
        print "a" + tags[n][0] + "a",
        print "[label=\"" + tags[n][0] + "\", color=green, fontcolor=blue, shape=rectangle]"
        n = n + 1
    n = 0
    while n < len(tags):
        m = 0
        while m < len(tags_refs[tags[n][0]]):
            print "a" + tags[n][0] + "a",
            print "->",
            print "a" + tags_refs[tags[n][0]][m] + "a"
            m = m + 1
        n = n + 1
    print "}"

def print_max_nodes():
    T = 0
    n = 0
    while n < len(tags):
        if tags_nr[tags[n][0]] > T:
            T = tags_nr[tags[n][0]]
        n = n + 1
    print "Maximum height is",
    print T,
    print "attained by: "
    n = 0
    while n < len(tags):
        if tags_nr[tags[n][0]] == T:
            print tags[n][0],
        n = n + 1
    print
    print "The next few maxima are: "
    m = 1
    while m < 60:
        print "Height",
        print T - m,
        print " : ",
        n = 0
        while n < len(tags):
            if tags_nr[tags[n][0]] == T - m:
                print tags[n][0],
            n = n + 1
        print
        m = m + 1
def print_distribution():
    T = 0
    n = 0
    while n < len(tags):
        if tags_nr[tags[n][0]] > T:
            T = tags_nr[tags[n][0]]
        n = n + 1
    print "Maximum height is",
    print T,
    print ". The distribution: "
    m = T
    while m >= 0:
        print "Height",
        print m,
        print " : ",
        n = 0
        nr = 0
        while n < len(tags):
            if tags_nr[tags[n][0]] == m:
                nr = nr + 1
            n = n + 1
        print nr
        m = m - 1

def print_tree_tag(mytag):
    n = tags_nr[mytag]
    mylist = [mytag]
    oldset = set()
    while n >= 2 and len(mylist) > 0:
        print mylist
        newlist = []
        m = 0
        while m < len(mylist):
            newlist = newlist + tags_refs[mylist[m]]
            m = m + 1
        newset = set(newlist)
        newset -= oldset
        oldset |= newset
        mylist = list(newset)
        n = n - 1

def print_graph_tag(mytag):
    print "digraph dependencies {"
    print
    print "nodesep=0.05"
    print "edge [dir=back]"
    print
    n = tags_nr[mytag]
    mylist = [mytag]
    print "a" + mytag + "a",
    print "[label=\"" + mytag + "\", color=green, fontcolor=blue, shape=rectangle, width=0.5, height=0.25]"
    oldset = set()
    while n >= 0 and len(mylist) > 0:
        m = 0
        newlist = []
        m = 0
        while m < len(mylist):
            prelims = ""
            prelims_txt = ""
            newlist = newlist + tags_refs[mylist[m]]
            k = 0
            while k < len(tags_refs[mylist[m]]):
                if tags_nr[tags_refs[mylist[m]][k]] == 0:
                    if not prelims == "":
                        prelims_txt = prelims_txt + ", "
                    prelims = prelims + tags_refs[mylist[m]][k]
                    prelims_txt = prelims_txt + tags_refs[mylist[m]][k]
                else:
                    print "a" + tags_refs[mylist[m]][k] + "a",
                    print "[label=\"" + tags_refs[mylist[m]][k] + "\", color=green, fontcolor=blue, shape=rectangle, width=0.5, height=0.25]"
                    print "a" + mylist[m] + "a",
                    print "->",
                    print "a" + tags_refs[mylist[m]][k] + "a"
                k = k + 1
            if not prelims == "":
                print "a" + prelims + "a",
                print "[label=\"" + prelims_txt + "\", color=green, fontcolor=blue, shape=rectangle, width=0.5, height=0.25]"
                print "a" + mylist[m] + "a",
                print "->",
                print "a" + prelims + "a"
            m = m + 1
        newset = set(newlist)
        newset -= oldset
        oldset |= newset
        mylist = list(newset)
        n = n - 1
    print "}"

connection = sqlite3.connect("../../stacks-website/database/stacks.sqlite") # TODO configuration

names = {}
def getName(tag):
  try:
    query = "SELECT name FROM tags WHERE tag = :tag"
    cursor = connection.execute(query, [tag])

    result = cursor.fetchone()
    if result != None: # TODO conflicting versions of databases etc require this, will this occur in real life too?
      return result[0]
    else:
      return ""

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

def addNames():
  for tag, label in tags:
    names[tag] = getName(tag)

addNames()

#names = {}
#for tag in tags:
#    print tag

# contains the tags that have been used
#used = set([])
result = {"nodes": [], "links": []}
mapping = {}
n = 0

tags_labels = dict((v, k) for k, v in label_tags.iteritems())

def children(tag):
  return set(tags_refs[tag] + sum([list(children(child)) for child in tags_refs[tag]], []))

mapping = {}
n = 0
def graph(tag):
  global mapping, n

  if tag not in mapping.keys():
    mapping[tag] = n
    n = n + 1
    result["nodes"].append(
      {"tag": tag, 
       "size": tags_nr[tag],
       "file" : tags_labels[tag].split("-")[0],
       "type": tags_labels[tag].split("-")[1],
       "name": names[tag]
      })

    for child in tags_refs[tag]:
      graph(child)
      result["links"].append({"source": mapping[tag], "target": mapping[child]})

#def graph(tag, depth = 0):
#    global mapping, n
#    if tag not in mapping:
#        mapping[tag] = n
#        n = n + 1
#        result["nodes"].append({"tag": tag, "group": 1, "size": tags_nr[tag], "file" : tags_labels[tag].split("-")[0], "type": tags_labels[tag].split("-")[1]})
#
#    if tags_refs[tag] == []:
#        return 
#    else:
#        for child in tags_refs[tag]:
#            graph(child, depth + 1)
#            #used.add(child)
#            print tag
#            print tags_refs[tag]
#            result["links"].append({"source": mapping[tag], "target": mapping[child]})
#
#
#
#def tree(tag):
#    # child node
#    if tags_refs[tag] == []:
#        return {"tag": tag, "size": 2000}
#    else:
#        return {"tag": tag, "children": [tree(child) for child in tags_refs[tag]]}
        

#print_whole_graph()
#print_graph_tag('0200')
#print json.dumps(tree("07D9"))
#used = set([])
#result = {"nodes": [], "links": []}
#mapping = {}
#n = 0
#graph("0200")
#print len(result["nodes"])
#
#used = set([])
#result = {"nodes": [], "links": []}
#mapping = {}
#n = 0


#graph("00QA")
#print "contains " + str(len(result["nodes"])) + " nodes and " + str(len(result["links"])) + " links"
#sources, targets = defaultdict(int), defaultdict(int)
#for link in result["links"]:
#  sources[link["source"]] += 1
#  targets[link["target"]] += 1
#
#print sources
#print targets


for tag in tags:
  # clean data
  mapping = {}
  n = 0
  result = {"nodes": [], "links": []}

  f = open(tag[0] + "-force.json", "w")
  graph(tag[0])
  print "generating " + tag[0] + "-force.json, which contains " + str(len(result["nodes"])) + " nodes and " + str(len(result["links"])) + " links"
  f.write(json.dumps(result, indent = 2))
  f.close()



#print len(result["nodes"])
#print json.dumps(result, indent = 2).replace("'", '"')

#print set([split_label(key)[1] for key in label_tags.keys()])

#print_max_nodes()

#print_distribution()

