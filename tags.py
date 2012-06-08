import os
import sqlite3
from functions import *

def parse_contentsline(contentsline):
  parts = contentsline.split('}{')

  # sanitize first element to determine type
  parts[0] = parts[0][15:]
  # remove clutter
  parts = map(lambda part: part.strip('\{\}'), parts)

  return [parts[0], parts[2], parts[3], parts[4]]


def parse_book_toc(filename):
  toc = open(filename, 'r')
  sections = [parse_contentsline(line)[0:4] for line in toc]
  toc.close()

  return sections


def parse_newlabel(newlabel):
  parts = newlabel.split('}{')

  # get the actual label
  parts[0] = parts[0][10:]
  # remove clutter
  parts = map(lambda part: part.strip('\{\}'), parts)

  return parts


def parse_aux(filename):
  aux = open(filename)

  labels = {}

  for line in aux:
    # not interesting, go to next line
    if not line.startswith("\\newlabel{"):
      continue

    parts = parse_newlabel(line)

    # not an actual label, just LaTeX layout bookkeeping, go to next line
    if len(parts) == 2:
      continue
    # it is a label, add it with what we already know about it
    else:
      labels[parts[0]] = (parts[1], parts[2])

  return labels


# read all .aux files and generate a dictionary containing all labels and
# their whereabouts
def get_labels_from_source(path):
  # we'll do book.aux first, getting a complete overview of all labels
  aux_files = list_aux_files(path)
  aux_files.remove('book.aux')
  labels = parse_aux(path + 'book.aux')
  
  # now merge every other .aux file against the current dictionary
  for aux_file in aux_files:
    print 'parsing {0}'.format(aux_file)
  
    local = parse_aux(path + aux_file)
    for label, information in local.iteritems():
      # we prepend the current filename to get the full label
      full_label = aux_file[0:-4] + '-' + label
  
      if full_label in labels:
        labels[full_label] = [aux_file[0:-4], labels[full_label], local[label]]
      else:
        print 'ERROR: the label \'{0}\' was found in {1} but not in {2}'.format(
            full_label, path + aux_file, path + 'book.aux')

  return labels


# read all tags from the current tags/tags file
def parse_legacy_tags(filename):
  tags_file = open(filename, 'r')

  tags = {}

  for line in tags_file:
    if not line.startswith('#'):
      (tag, label) = line.strip().split(',')
      tags[tag] = label

  tags_file.close()

  return tags


# create the tags database from scratch using the current tags/tags file
def import_legacy_tags(filename, labels):
  tags = parse_legacy_tags(filename)
  for tag, label in tags.iteritems():
    info = labels[label]
  
    insert_tag(connection, tag, (label, info[0], info[2][1], info[1][1], info[1][0]))


def import_titles(path):
  titles = get_titles(path)
  sections = parse_book_toc(path + 'book.toc')

  print invert_dict(titles)
  for section in sections:
    if len(section[1].split('.')) == 1:
      print section
      print invert_dict(titles)[section[2]]

  #for section in sections:
  #  insert_title(section[1], section[2], '')
  


def insert_title(number, title, filename):
  try:
    query = 'INSERT INTO sections (number, title, filename) VALUES (?, ?, ?)'
    connection.execute(query, (number, title, filename))
  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]


def insert_tag(tag, value):
  try:
    query = 'INSERT INTO tags (tag, label, file, chapter_page, book_page, book_id) VALUES (?, ?, ?, ?, ?, ?)'
    connection.execute(query, (tag, value[0], value[1], value[2], value[3], value[4]))
  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]


path = 'tex/tags/tmp/'

connection = sqlite3.connect('stacks.sqlite')

#import_legacy_tags('tex/tags/tags', get_labels_from_source(path))

import_titles(path)

connection.commit()
connection.close()
