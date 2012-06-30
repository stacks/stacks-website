import sqlite3

connection = sqlite3.connect("database.sqlite")
queries = open('database.sql')
for query in queries:
    connection.execute(query);

connection.commit()
connection.close()
