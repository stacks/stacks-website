# local version
directory = '';

# location of the database, relative to the scripts/ directory
database = '../db/stacks.sqlite'

# location of the tags file
tags_file = '../tex/tags/tags'

# location of the bibliography file
bibliography_file = '../tex/my.bib'

# location of the bootstrap file
bootstrap_file = 'bootstrap.txt'

# location of the temporary folder
tmp_folder = '../tex/tags/tmp/'

def full_url(path):
  return directory + path
