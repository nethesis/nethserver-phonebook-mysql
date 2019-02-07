import os
import glob
import sys
import json
import signal
import logging
import MySQLdb
import argparse
import datetime
LOG_PATH = '/var/log/phonebook-import.log'
DEST_CONFIG = '/etc/phonebook/destination-import.json'
SOURCES_PATH = '/etc/phonebook/sources.d/'
startTime = sourceId = importedCount = toTransfer = errCount = sources = dest = None
#
def signalHandler(sig, frame):
  logSourceRes()
  logger.critical('interrupted by SIGINT')
  sys.exit(0)

signal.signal(signal.SIGINT, signalHandler)

def getDbConn(config):
  if config['dbtype'] == 'mysql':
    port = unixSocket = None
    try:
      port = int(config['port'])
    except:
      port = None
      unixSocket = config['port']
    try:
      if port:
        return MySQLdb.connect(host=config['host'], port=port, user=config['user'], passwd=config['password'], db=config['dbname'])
      else:
        return MySQLdb.connect(host=config['host'], unix_socket=unixSocket, user=config['user'], passwd=config['password'], db=config['dbname'])
    except Exception as err:
      logger.error(str(err))
      return None

def checkDbConn(config):
  logger.info('Check mysql db connection')
  dbConn = getDbConn(config)
  if dbConn != None:
    dbCur = dbConn.cursor()
    try:
      dbCur.execute('SELECT COUNT(*) FROM ' + config['dbtable'])
      dbCur.fetchone()[0]
      logger.info('db connection: OK')
    except Exception as err:
      logger.error('getting number of entries to be remove from destination ' + dest['dbname'] + '.' + dest['dbtable'] + ' before importing of source "' + sourceId + '": FAILED')
      logger.error(str(err))
    dbCur.close()
    dbConn.close()
    return 0
  else:
    logger.error('db connection: FAILED')
    return 1

def getDbCols(config):
  logger.info('get columns list of "' + str(config) + '"')
  dbConn = getDbConn(config)
  if dbConn != None:
    dbCur = dbConn.cursor()
    dbCur.execute('SHOW COLUMNS FROM ' + config['dbtable'])
    res = dbCur.fetchall()
    dbCur.close()
    dbConn.close()
    return [col[0] for col in res]
  else:
    return []

def test():
  global sourceId
  global importedCount
  global toTransfer
  global errCount
  global startTime
  global sources
  global dest
  logger.info('Start TEST mysql db phonebooks import into phonebook.phonebook')
  # read destination file config
  try:
    with open(DEST_CONFIG, 'r') as configFile:
      dest = json.load(configFile)
      logger.info('check ' + DEST_CONFIG + ': OK')
  except Exception as err:
    logger.error('check ' + DEST_CONFIG + ': FAILED')
    logger.error(str(err))
    sys.exit(1)

  dbDest = getDbConn(dest)
  if dbDest != None:
    logger.info('check destination db connection: OK')
  else:
    logger.error('check destination db connection: FAILED')
    sys.exit(1)
  curDest = dbDest.cursor()

  # cycle all files of sources dir
  filepaths = glob.glob(os.path.join(SOURCES_PATH, '*.json'))
  for f in filepaths:
    try:
      with open(f, 'r') as sourceFile:
        config = json.load(sourceFile)
        logger.info('check source ' + f + ': OK')
    except Exception as err:
      logger.error('check source ' + f + ': FAILED')
      logger.error('reading ' + f)
      logger.error(str(err))

    for sourceId, config in config.items():
      # check if the source is enabled
      if config['enabled'] == False:
        logger.warn('check db source "' + sourceId + '" (' + f + '): DISABLED')
        continue
      dbSource = getDbConn(config)
      if dbSource != None:
        logger.info('check db source "' + sourceId + '" connection: OK')
      else:
        logger.error('check db source "' + sourceId + '" connection: FAILED')
        continue
      try:
        curDest.execute('SELECT COUNT(*) FROM ' + dest['dbtable'] + ' WHERE source="{}"'.format(sourceId))
        delcount = curDest.fetchone()[0]
        logger.info(str(delcount) + ' entries with "source=' + sourceId + '" will be removed from destination ' + dest['dbname'] + '.' + dest['dbtable'] + ' before importing of source "' + sourceId + '"')
      except Exception as err:
        logger.error('getting number of entries to be remove from destination ' + dest['dbname'] + '.' + dest['dbtable'] + ' before importing of source "' + sourceId + '": FAILED')
        logger.error(str(err))
      # get total number of entries to be copied
      curSource = dbSource.cursor()
      curSource.execute('SELECT COUNT(*) FROM ' + config['dbtable'])
      toTransfer = curSource.fetchone()[0]
      logger.info(str(toTransfer) + ' entries will be copied from db source "' + sourceId + '" into destination ' + dest['dbname'] + '.' + dest['dbtable'])
      curSource.close()
      dbSource.close()
  
  dbDest.close()
  curDest.close()
  logger.info('End TEST mysql db phonebooks import into phonebook.phonebook')

def start():
  global sourceId
  global importedCount
  global toTransfer
  global errCount
  global startTime
  global sources
  global dest
  logger.warning('START mysql db phonebooks import into phonebook.phonebook')
  # read destination file config
  try:
    with open(DEST_CONFIG, 'r') as configFile:
      dest = json.load(configFile)
  except Exception as err:
    logger.error('reading ' + DEST_CONFIG)
    logger.error(str(err))
    sys.exit(1)

  dbDest = getDbConn(dest)
  if dbDest != None:
    logger.info('check destination db connection: OK')
  else:
    logger.error('connecting to the destination db (check ' + DEST_CONFIG + ')')
    sys.exit(1)
  curDest = dbDest.cursor()

  # cycle all files of sources dir
  filepaths = glob.glob(os.path.join(SOURCES_PATH, '*.json'))
  for f in filepaths:
    try:
      logger.info('read ' + f)
      with open(f, 'r') as sourceFile:
        config = json.load(sourceFile)
    except Exception as err:
      logger.error('reading ' + f)
      logger.error(str(err))

    for sourceId, config in config.items():
      # check if the source is enabled
      if config['enabled'] == False:
        logger.warn('skip db source "' + sourceId + '"')
        continue

      logger.info('importing source "' + sourceId + '"...')
      startTime = datetime.datetime.now().replace(microsecond=0)
      destCols = config['mapping'].values()
      destCols.append('source')
      sourceCols = config['mapping'].keys()
      # try connection
      dbSource = getDbConn(config)
      if dbSource != None:
        logger.info('check destination db connection: OK')
      else:
        logger.error('connecting to source db "' + sourceId + '" (check ' + f + ')')
        continue
      # clean destination
      try:
        delcount = curDest.execute('DELETE FROM ' + dest['dbtable'] + ' WHERE source="{}"'.format(sourceId))
        logger.info('removed all contacts (#' + str(delcount) + ') of source "' + sourceId + '" from destination ' + dest['dbname'] + '.' + dest['dbtable'])
      except Exception as err:
        logger.error('removing all contacts of source "' + sourceId + '" from destination ' + dest['dbname'] + '.' + dest['dbtable'])
        logger.error(str(err))
      # get total number of entries to be copied
      curSource = dbSource.cursor()
      curSource.execute('SELECT COUNT(*) FROM ' + config['dbtable'])
      toTransfer = curSource.fetchone()[0]
      # start copying
      curSource = dbSource.cursor(MySQLdb.cursors.SSCursor)
      curSource.execute('SELECT ' + ','.join(sourceCols) + ' FROM ' + config['dbtable'])
      row = curSource.fetchone()
      importedCount = 0
      errCount = 0
      if config['type'] != None:
        destCols.append('type')
      while row is not None:
        row = row + (str(sourceId), str(config['type']))
        sql = 'INSERT INTO ' + dest['dbtable'] + ' (' + ','.join(destCols) + ') VALUES {}'.format(row)
        try:
          curDest.execute(sql)
          importedCount += 1
        except Exception as err:
          errCount += 1
          logger.error('error copying contact ' + str(row))
          logger.error(str(err))
        dbDest.commit()
        row = curSource.fetchone()
      curSource.close()
      dbSource.close()
      logSourceRes()
  dbDest.close()
  curDest.close()
  logger.warning('END mysql db phonebooks import into phonebook.phonebook')

def logSourceRes():
  global sourceId
  global importedCount
  global toTransfer
  global errCount
  global startTime
  end = datetime.datetime.now().replace(microsecond=0)
  if toTransfer > 0:
    percent = str(importedCount*100/toTransfer)
  else:
    percent = '0'
  logger.warning('source "' + sourceId + '" imported ' + percent + '% (#' + str(importedCount) + ' imported - #' + str(errCount) + ' errors - #' + str(toTransfer) + ' tot - duration ' + str(end-startTime) + ')')

def extractArgsDbParams(data):
  for arg in data:
    if arg.startswith('dbtype='):
      dbtype = arg.split('=')[1]
    elif arg.startswith('host='):
      host = arg.split('=')[1]
    elif arg.startswith('port='):
      port = arg.split('=')[1]
    elif arg.startswith('user='):
      user = arg.split('=')[1]
    elif arg.startswith('password='):
      password = arg.split('=')[1]
    elif arg.startswith('dbname='):
      dbname = arg.split('=')[1]
    elif arg.startswith('dbtable='):
      dbtable = arg.split('=')[1]
  return { 'dbtype': dbtype, 'host': host, 'port': port, 'user': user, 'password': password, 'dbname': dbname, 'dbtable': dbtable }

if __name__ == '__main__':
  descr = 'MySQL Phonebook importer. You can import more db sources into one sinlge db destination. The sources and destination configuration data has to be declared using json files into the /etc/phonebook/ directory. The import results are written to ' + LOG_PATH + '.'
  parser = argparse.ArgumentParser(description=descr)
  parser.add_argument('-lv', '--log_verbose', action='store_true', help='enable debug log level in ' + LOG_PATH)
  parser.add_argument('-v', '--verbose', action='store_true', help='enable console debug')
  parser.add_argument('-t', '--test', action='store_true', help='test the source and destination configurations making some checks and writing some debug output to the console')
  parser.add_argument('--check-db-conn', nargs=7, metavar=('dbtype=mysql', 'host=<ADDRESS>', 'port=<PORT>', 'user=<USERNAME>', 'password=<PASSWORD>', 'dbname=<DBNAME>', 'dbtable=<DBTABLE>'), help='check database connection returning 0 if the connection is successful, 1 otherwise')
  parser.add_argument('--get-db-cols', nargs=7, metavar=('dbtype=mysql', 'host=<ADDRESS>', 'port=<PORT>', 'user=<USERNAME>', 'password=<PASSWORD>', 'dbname=<DBNAME>', 'dbtable=<DBTABLE>'), help='returns the column list of the db table')
  args = parser.parse_args()
  logger = logging.getLogger(__name__)
  logger.setLevel(logging.DEBUG)
  cHandler = logging.StreamHandler()
  fHandler = logging.FileHandler(LOG_PATH)
  cHandler.setLevel(logging.DEBUG if args.verbose == True else logging.NOTSET)
  fHandler.setLevel(logging.DEBUG if args.log_verbose == True else logging.WARNING)
  logFormat = logging.Formatter('%(asctime)s [%(process)s] %(levelname)s: %(message)s', datefmt='%d-%b-%y %H:%M:%S')
  cHandler.setFormatter(logFormat)
  fHandler.setFormatter(logFormat)
  if args.verbose == True or args.test == True or args.check_db_conn:
    logger.addHandler(cHandler)
  logger.addHandler(fHandler)
  if args.test == True:
    test()
  elif args.get_db_cols:
    sys.stdout.write(json.dumps(getDbCols(extractArgsDbParams(args.get_db_cols))))
  elif args.check_db_conn:
    sys.exit(checkDbConn(extractArgsDbParams(args.check_db_conn)))
  else:
    start()
