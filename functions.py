import os

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
