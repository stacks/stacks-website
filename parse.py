import json
from collections import deque, defaultdict
import sqlite3
from functions import *

def find_tag(label, label_tags):
  if not label in label_tags:
    return "ZZZZ"
  else:
    return label_tags[label]

# path to the TeX files
path = get_path()
# list of TeX files
files = list_text_files(path)

labels = []
tags = get_tags(path)

# dictionary labels -> tags
label_tags = dict((tags[n][1], tags[n][0]) for n in range(0, len(tags)))

# dictionary tags -> height in graph
tags_nr = dict()
n = 0
while n < len(tags):
  tags_nr[tags[n][0]] = 0
  n = n + 1
tags_nr['ZZZZ'] = 0

# dictionary tags -> referenced tags
tags_refs = dict()
n = 0
while n < len(tags):
  tags_refs[tags[n][0]] = []
  n = n + 1
tags_refs['ZZZZ'] = []

ext = ".tex"
for name in files:
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

connection = sqlite3.connect("../../stacks-website/database/stacks.sqlite") # TODO configuration

# get names for tags from the database
names = {}
def getName(tag):
  try:
    query = "SELECT name FROM tags WHERE tag = :tag"
    cursor = connection.execute(query, [tag])

    result = cursor.fetchone()
    if result != None:
      return result[0]
    else:
      return ""

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

def addNames():
  for tag, label in tags:
    names[tag] = getName(tag)

addNames()

# get sections for tags from the database
sections = {}
chapters = {}
def getID(tag):
  try:
    query = "SELECT book_id FROM tags WHERE tag = ?"
    cursor = connection.execute(query, [tag])

    result = cursor.fetchone()
    if result != None:
      return result[0]
    else:
      return ""

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

def getChapter(tag):
  ID = ".".join(getID(tag).split(".")[0:1])
  try:
    query = "SELECT title FROM sections WHERE number = ?"
    cursor = connection.execute(query, [ID])

    result = cursor.fetchone()
    if result != None:
      return (result[0], ID)
    else:
      return ("", "0.0")

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

def getSection(tag):
  ID = ".".join(getID(tag).split(".")[0:2])
  try:
    query = "SELECT title FROM sections WHERE number = ?"
    cursor = connection.execute(query, [ID])

    result = cursor.fetchone()
    if result != None:
      return (result[0], ID)
    else:
      return ("", "0.0")

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

def addSections():
  for tag, label in tags:
    sections[tag] = getSection(tag)
def addChapters():
  for tag, label in tags:
    chapters[tag] = getChapter(tag)

IDs = {}
def addIDs():
  for tag, label in tags:
    IDs[tag] = getID(tag)

addSections()
addChapters()
addIDs()

# dictionary for easy label access
tags_labels = dict((v, k) for k, v in label_tags.iteritems())

# empty variables for graph creation
result = {"nodes": [], "links": []}
mapping = {}
n = 0

def generateGraph(tag, depth = 0):
  global mapping, n, result

  if tag not in mapping.keys():
    mapping[tag] = n
    result["nodes"].append(
      {"tag": tag, 
       "size": tags_nr[tag],
       "file" : split_label(tags_labels[tag])[0],
       "type": split_label(tags_labels[tag])[1],
       "name": names[tag],
       "id": IDs[tag],
       "depth": depth
       # TODO also chapter name etc, but I don't feel like it right now
      })

    for child in tags_refs[tag]:
      generateGraph(child, depth + 1)
      result["links"].append({"source": mapping[tag], "target": mapping[child]})
  else:
    # overwrite depth if necessary
    result["nodes"][mapping[tag]]["depth"] = max(depth, result["nodes"][mapping[tag]]["depth"])


def generateTree(tag, depth = 0, cutoff = 4):
  # child node
  if tags_refs[tag] == [] or depth == cutoff:
    return {"tag": tag, "type": split_label(tags_labels[tag])[1], "size": 2000}
  else:
    return {"tag": tag, "type": split_label(tags_labels[tag])[1], "children": [generateTree(child, depth + 1, cutoff) for child in set(tags_refs[tag])]}
        
def countTree(tree):
  if "children" not in tree.keys():
    return 1
  else:
    return 1 + sum([countTree(tag) for tag in tree["children"]])


def generatePacked(tag):
  children = set(getChildren(tag))
  print len(children)

  packed = defaultdict(list)
  packed["name"] = ""
  packed["type"] = "root"
  chaptersMapping = {}
  sectionsMapping = defaultdict(dict)
  for child in children:
    chapter = chapters[child][0]
    section = sections[child][0]

    if chapter not in chaptersMapping:
      chaptersMapping[chapter] = max(chaptersMapping.values() + [-1]) + 1
      #print "assigning " + str(chaptersMapping[chapter]) + " to " + chapter
      packed["children"].append({"name": chapter, "type": "chapter", "children": []})

    if section not in sectionsMapping[chapter]:
      sectionsMapping[chapter][section] = max(sectionsMapping[chapter].values() + [-1]) + 1
      #print "assigning " + str(sectionsMapping[chapter][section]) + " to " + chapter + ", " + section
      packed["children"][chaptersMapping[chapter]]["children"].append({"name": section, "type": "section", "children": []})

    packed["children"][chaptersMapping[chapter]]["children"][sectionsMapping[chapter][section]]["children"].append({"name": child, "type": "tag", "size": 2000})

  return packed


# force directed dependency graph
def generateGraphs():
  for tag in tags:
    global mapping, n, result
    # clean data
    mapping = {}
    n = 0
    result = {"nodes": [], "links": []}
  
    f = open("data/" + tag[0] + "-force.json", "w")
    generateGraph(tag[0])
    print "generating " + tag[0] + "-force.json, which contains " + str(len(result["nodes"])) + " nodes and " + str(len(result["links"])) + " links"
    f.write(json.dumps(result, indent = 2))
    f.close()

# treeview (or clusterview)
maximum = 150
def optimizeTree(tag):
  cutoffValue = 1
  tree = generateTree(tag, cutoff = cutoffValue)

  while True:
    candidate = generateTree(tag, cutoff = cutoffValue + 1)

    # three reasons to stop: too big a tree, all nodes reached or too deep a tree
    if countTree(candidate) > maximum or countTree(candidate) == countTree(tree) or cutoffValue > 6:
      return tree
    else:
      tree = candidate
      cutoffValue = cutoffValue + 1

def generateTrees():
  for tag in tags:
    f = open("data/" + tag[0] + "-tree.json", "w")
    #result = generateTree(tag[0], cutoff = 3)
    result = optimizeTree(tag[0])
    print "generating " + tag[0] + " which contains " + str(countTree(result)) + " nodes"
    f.write(json.dumps(result, indent = 2))
    f.close()

def getChildren(tag):
  queue = deque([tag])
  result = set([])

  while queue:
    tag = queue.popleft()
    if tag not in result:
      result.add(tag)
      queue.extend(tags_refs[tag])

  return result


# packed view with clusters corresponding to parts and chapters
def generatePackeds():
  for tag in tags:
    f = open("data/" + tag[0] + "-packed.json", "w")
    packed = generatePacked(tag[0])
    print "generating packed view for " + tag[0]
    f.write(json.dumps(packed, indent = 2))
    f.close()


generateGraphs()
generateTrees()
generatePackeds()
