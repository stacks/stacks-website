import sqlite3
from functions import *

# check whether a (sub)section number exists in the database
def title_exists(number):
  try:
    # TODO prepared statement
    query = 'SELECT COUNT(*) FROM sections WHERE number = "' + number + '"'
    cursor = connection.execute(query)

    return cursor.fetchone()[0] == 1

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

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
  titles = get_titles(path)
  sections = parse_book_toc(path + 'book.toc')

  for section in sections:
    insert_title(section[1], section[2], '')
  

path = 'tex/tags/tmp/'
database = 'stacks.sqlite'

connection = sqlite3.connect(database)

import_titles(path)

connection.commit()
connection.close()
