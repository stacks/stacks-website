import sqlite3
import config
from functions import *

# check whether a (sub)section number exists in the database
def title_exists(number):
  try:
    query = 'SELECT COUNT(*) FROM sections WHERE number = ?'
    cursor = connection.execute(query, [number])

    return cursor.fetchone()[0] == 1

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return False

def insert_title(number, title, filename):
  try:
    if title_exists(number):
      query = 'UPDATE sections SET title = ?, filename = ? WHERE number = ?'
      connection.execute(query, (title, filename, number))
    else:
      query = 'INSERT INTO sections (number, title, filename) VALUES (?, ?, ?)'
      connection.execute(query, (number, title, filename))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

def import_titles(path):
  print 'Creating a database version of the table of contents'
  print 'Parsing the files, linking chapters to file names'
  titles = get_titles(path)
  print 'Parsing the big table of contents'
  sections = parse_book_toc(path + 'book.toc')

  print 'Inserting the information into the database'
  for section in sections:
    # the bibliography doesn't correspond to a file, we can safely ignore it
    if section[2] == 'Bibliography':
      continue

    insert_title(section[1], section[2], find_file_for_section(titles, sections, section[1]))
  

connection = sqlite3.connect(config.database)

import_titles(config.tmp_folder)

connection.commit()
connection.close()
