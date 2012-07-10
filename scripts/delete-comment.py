import config
import string
import sqlite3
import sys

# check whether a comment exists in the database
def comment_exists(comment_id):
  try:
    query = 'SELECT COUNT(*) FROM comments WHERE id = ?'
    cursor = connection.execute(query, [comment_id])

    return cursor.fetchone()[0] == 1

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return False

# get a comment from the database
def get_comment(comment_id):
  assert comment_exists(comment_id)

  try:
    query = 'SELECT id, author, date, tag FROM comments WHERE id = ?'
    cursor = connection.execute(query, [comment_id])

    return cursor.fetchone()

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

# delete a comment from the database
def delete_comment(comment_id):
  assert comment_exists(comment_id)

  try:
    query = 'DELETE FROM comments WHERE id = ?'
    connection.execute(query, [comment_id])

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]


if not len(sys.argv) == 2:
  print 'You must supply one argument, namely the id of the comment you wish to remove'
  raise Exception('Wrong number of arguments')

comment_id = int(sys.argv[1])

print 'Trying to remove the comment with id', int(comment_id)

connection = sqlite3.connect(config.database)

if not comment_exists(comment_id):
  print 'There is no such comment in the database'
else:
  comment = get_comment(comment_id)
  choice = raw_input('Are you sure you wish to remove this comment by ' + comment[1] + ' on tag ' + comment[3] + '? (Y/N): ')
  if string.upper(choice) == 'Y':
    delete_comment(comment_id)
    print 'Comment removed!'

connection.commit()
connection.close()
