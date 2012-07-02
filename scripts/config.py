# current test version on paard.math.columbia.edu
#directory = '/~pieter/algebraic_geometry/stacks-website/'
# local version
directory = '/'

# location of the database, relative to the scripts/ directory
# TODO must be moved one level up
database = '../stacks.sqlite'

# location of the tags file
tags_file = '../tex/tags/tag'

# location of the temporary folder
tmp_folder = '../tex/tags/tmp'

def full_url(path):
  return directory + path
