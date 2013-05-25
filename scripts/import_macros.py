import config
import sqlite3

def add_macro(name, value):
  try:
    query = 'INSERT INTO macros (name, value) VALUES (?, ?)'
    connection.execute(query, [name, value])

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

def add_macros():
  f = open("../tex/preamble.tex", "r")

  for line in f:
    if line[0:4] == "\def":
      name = "\\" + line.split("\\")[2][0:-1]
      value = ("\\" + "\\".join(line.split("\\")[3:])).rstrip()[:-1]
  
      add_macro(name, value)

def clear_macros():
  try:
    query = 'DELETE FROM macros'
    connection.execute(query)

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]


connection = sqlite3.connect(config.database)

print 'Emptying the table'
clear_macros()
print 'Importing macros from tex/preamble.tex'
add_macros()

connection.commit()
connection.close()
