import sqlite3
import config

bibliography_location = '../tex/my.bib'

def clear_bibliography():
  try:
    query = 'DELETE FROM bibliography_items'
    connection.execute(query)

    query = 'DELETE FROM bibliography_values'
    connection.execute(query)

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

def insert_item(item):
  try:
    query = 'INSERT INTO bibliography_items (name, type) VALUES (?, ?)'
    connection.execute(query, (item[0][1], item[0][0]))

    for (key, value) in item[1].iteritems():
      query = 'INSERT INTO bibliography_values (name, key, value) VALUES (?, ?, ?)'
      connection.execute(query, (item[0][1], key, value))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

def import_bibliography(location):
  f = open(location)

  items = []
  
  for line in f:
    # beginning of a new item
    if line[0] == '@':
      # clear previous item
      item = [[], {}]

      bib_type = line.partition('{')[0].strip('@').lower()
      name = line.partition('{')[2].strip().strip(',')

      item[0] = (bib_type, name)
  
    # end of an item
    if line[0] == '}':
      # add a *copy* to the list of items
      items.append(list(item))
    
    if '=' in line:
      key = line.partition('=')[0].strip().lower()
      value = line.partition('=')[2].strip().strip(',')[1:-1]
  
      item[1][key] = value

  for item in items:
    insert_item(item)

connection = sqlite3.connect(config.database)

print 'Clearing bibliography'
clear_bibliography()
print 'Importing bibliography'
import_bibliography(config.bibliography_file)

connection.commit()
connection.close()

