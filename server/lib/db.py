#!/usr/bin/python -O
import threading
import sqlite3

g_db = {}
g_params = {}

# This is a way to get the column names after grabbing everything
# I guess it's also good practice
SCHEMA = {
  'intents': [
     ('id', 'INTEGER PRIMARY KEY'),
     ('key', 'TEXT UNIQUE'),
     ('start', 'INTEGER'),
     ('end', 'INTEGER'), 
     ('read_count', 'INTEGER DEFAULT 0'),
     ('created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'),
     ('accessed_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'),
   ],
  'kv': [
     ('id', 'INTEGER PRIMARY KEY'),
     ('key', 'TEXT UNIQUE'),
     ('value', 'TEXT'),
     ('created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'),
   ],
  'streams': [
     ('id', 'INTEGER PRIMARY KEY'), 
     ('name', 'TEXT UNIQUE'),
     ('start', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'),
     ('end', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'),
     ('created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'),
     ('accessed_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'),
   ]
}

def all(table, field_list='*'):
  """ Returns all entries from the sqlite3 database for a given table """
  db = connect()

  column_count = 1
  if type(field_list) is not str:
    column_count = len(field_list)
    field_list = ','.join(field_list)

  query = db['c'].execute('select %s from %s order by id asc' % (field_list, table))
  if column_count == 1:
    return [record[0] for record in query.fetchall()]
  else:
    return [record for record in query.fetchall()]

def schema(table):
  """ Returns the schema for a given table """
  if table not in SCHEMA:
    return None 

  return [ key for key, value in SCHEMA[table] ]

def connect():
  """
  A "singleton pattern" or some other fancy $10-world style of maintaining 
  the database connection throughout the execution of the script.

  Returns the database instance
  """
  global g_db

  #
  # We need to have one instance per thread, as this is what
  # sqlite's driver dictates ... so we do this based on thread id.
  #
  # We don't have to worry about the different memory sharing models here.
  # Really, just think about it ... it's totally irrelevant.
  #
  thread_id = threading.current_thread().ident
  if thread_id not in g_db:
    g_db[thread_id] = {}

  instance = g_db[thread_id]

  if 'conn' not in instance:
    conn = sqlite3.connect('config.db')
    instance['conn'] = conn
    instance['c'] = conn.cursor()

    for table, schema in SCHEMA.items():
      dfn = ','.join(["%s %s" % (key, klass) for key, klass in schema])
      instance['c'].execute("CREATE TABLE IF NOT EXISTS %s(%s)" % (table, dfn))

    instance['conn'].commit()

  return instance


def shutdown():
  """ Closes the individual database connections in each thread. """
  global g_db

  for instance in g_db.items():
    if 'conn' in instance:
      instance['conn'].close()

def incr(key, value=1):
  """
  Increments some key in the database by some value.  It is used
  to maintain statistical counters.
  """

  db = connect()

  try:
    db['c'].execute('insert into kv(value, key) values(?, ?)', (value, key, ))

  except Exception as exc:
    db['c'].execute('update kv set value = value + ? where key = ?', (value, key, ))

  db['conn'].commit()


def set(key, value):
  """ 
  Sets (or replaces) a given key to a specific value.  

  Returns the value that was sent
  """
  global g_params

  db = connect()
  
  # From http://stackoverflow.com/questions/418898/sqlite-upsert-not-insert-or-replace
  res = db['c'].execute('''
    INSERT OR REPLACE INTO kv (key, value, created_at) 
      VALUES ( 
        COALESCE((SELECT key FROM kv WHERE key = ?), ?),
        ?,
        current_timestamp 
    )''', (key, key, value, ))

  db['conn'].commit()

  g_params[key] = value

  return value


def get(key, expiry=0, use_cache=False):
  """ 
  Retrieves a value from the database, tentative on the expiry. 
  If the cache is set to true then it retrieves it from in-memory if available, otherwise
  it goes out to the db. Other than directly hitting up the g_params parameter which is 
  used internally, there is no way to invalidate the cache.
  """
  global g_params

  if use_cache and key in g_params:
    return g_params[key]

  db = connect()

  if expiry > 0:
    # If we let things expire, we first sweep for it
    db['c'].execute('delete from kv where key = ? and created_at < (current_timestamp - ?)', (key, expiry, ))
    db['conn'].commit()

  res = db['c'].execute('select value, created_at from kv where key = ?', (key, )).fetchone()

  if res:
    g_params[key] = res[0]
    return res[0]

  return False


def register_stream(name, start_ts, end_ts):
  """
  Registers a stream as existing to be found later when trying to stitch and slice files together.
  This is all that ought to be needed to know if the streams should attempt to be stitched.
  """
  db = connect()
  res = db['c'].execute('select id from streams where name = ?', (name, )).fetchone()

  if res == None:
    db['c'].execute('insert into streams(name, start, end) values(?, ?, ?)', (name, start_ts, end_ts, ))
    db['conn'].commit()
    return db['c'].lastrowid

  return res[0]
 
def find_streams(start_time = False, end_time = False):
  """
  Find streams 
  """
  db = connect()
  res = db['c'].execute('select * from streams').fetchall()


def register_intent(minute_list, duration):
  """
  Tells the server to record on a specific minute for a specific duration when
  not in full mode.  Otherwise, this is just here for statistical purposes.
  """
  db = connect()

  for minute in minute_list:
    key = str(minute) + ':' + str(duration)
    res = db['c'].execute('select id from intents where key = ?', (key, )).fetchone()

    if res == None:
      db['c'].execute('insert into intents(key, start, end) values(?, ?, ?)', (key, minute, minute + duration, ))

    else:
      db['c'].execute('update intents set read_count = read_count + 1, accessed_at = (current_timestamp) where id = ?', (res[0], )) 

    db['conn'].commit()
    return db['c'].lastrowid

  return None

  
