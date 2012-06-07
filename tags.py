import os
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

  for line in toc:
    parts = parse_contentsline(line)

    print 'type:   {0}'.format(parts[0])
    print 'number: {0}'.format(parts[1])
    print 'title:  {0}'.format(parts[2])
    print 'page:   {0}\n'.format(parts[3])
  
  toc.close()


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


def parse_legacy_tags(filename):
  tags_file = open(filename, 'r')

  tags = {}

  for line in tags_file:
    if not line.startswith('#'):
      (tag, label) = line.strip().split(',')
      tags[tag] = label

  tags_file.close()

  return tags

path = 'tex/tags/tmp/'
titles = get_titles(path)

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
      # nifty way to flatten tuple of tuples
      labels[full_label] = sum((labels[full_label], local[label]), ())
    else:
      print 'ERROR: the label \'{0}\' was found in {1} but not in {2}'.format(
          full_label, path + aux_file, path + 'book.aux')


#for label, information in labels.iteritems():
#  print label, '\n\t', information

#parse_legacy_tags('tex/tags/tags')
