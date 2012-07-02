import os

# invert a dictionary mapping
def invert_dict(dictionary):
  return dict((v, k) for k, v in dictionary.iteritems()) 

# return all files with a given extension
def list_files(path, extension):
  return filter(lambda filename: filename.endswith(extension), os.listdir(path))

# return all TeX files in a directory
def list_aux_files(path):
  return list_files(path, '.aux')

# return all TeX files in a directory
def list_tex_files(path):
  return list_files(path, '.tex')

# get the title from a TeX file
def get_title(path):
  tex_file = open(path, 'r')

  title = ''
  for line in tex_file:
    if line.startswith('\\title'):
      title = line[7:line.find('}')]
      break

  return title

# get the dictionary relating TeX files to their respective titles
def get_titles(path):
  titles = {}

  for tex_file in list_tex_files(path):
    titles[tex_file[0:-4]] = get_title(path + tex_file)

  return titles

# recursively find the filename of a section
def find_file_for_section(titles, sections, number):
  for section in sections:
    # found the correct section
    if section[1] == number:
      # it is a chapter, we can look it up
      if len(number.split('.')) == 1:
        return invert_dict(titles)[section[2]]
      # recurse
      else:
        return find_file_for_section(titles, sections, '.'.join(number.split('.')[0:-1]))

# get the information from a \contentsline macro in a .toc file
def parse_contentsline(contentsline):
  parts = contentsline.split('}{')

  # sanitize first element to determine type
  parts[0] = parts[0][15:]
  # remove clutter
  parts = map(lambda part: part.strip('{}'), parts)

  # TODO document results
  return [parts[0], parts[2], parts[3], parts[4]]

# read and extract all information from a .toc file
def parse_book_toc(filename):
  toc = open(filename, 'r')
  sections = [parse_contentsline(line)[0:4] for line in toc]
  toc.close()

  return sections

# get the information from a \newlabel macro in a .aux file
def parse_newlabel(newlabel):
  parts = newlabel.split('}{')

  # get the actual label
  parts[0] = parts[0][10:]
  # remove clutter
  parts = map(lambda part: part.strip('{}'), parts)

  # TODO document results
  return parts

# read and extract all information from a .aux file
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

