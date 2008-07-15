#!/usr/bin/env python

import time, sys
from config import prefs

#import the right database connection
if prefs['db_type'] == 'mysql':
    import MySQLdb
elif prefs['db_type'] == 'sqlite':
    from pysqlite2 import dbapi2 as sqlite


#used by sqlite to make sure its in the same form as MySQLdb.cursors.DictCursor
def dict_factory(cursor, row):
    d = {}
    for idx, col in enumerate(cursor.description):
        d[col[0]] = row[idx]
    return d

class mydb:
    #this creates a new cursor, based on the database type
    def new_cursor(self):
        if prefs['db_type'] == 'mysql': #we want a dict form of the result
            return self.conn.cursor(MySQLdb.cursors.DictCursor)

        else: #new normal cursor
            return self.conn.cursor()


    #this connects to the proper database
    def connect(self):
        if prefs['db_type'] == 'mysql':
            self.conn = MySQLdb.connect(prefs['db_host'], prefs['db_user'], prefs['db_pass'], prefs['db_name'])

        elif prefs['db_type'] == 'sqlite':
            self.conn = sqlite.connect("mysqlite.db")#, autocommit=True
            self.conn.row_factory = dict_factory


    #this grabs the active contests
    def get_active_contests(self):
        cursor = self.new_cursor()
        cursor.execute ("SELECT contest_id, start, length FROM contests")
        result_set = cursor.fetchall ()
        self.active = []
        for row in result_set:
            if row['start'] < time.time() and row['start'] + row['length'] > time.time():
                row['time_left'] = row['start'] + row['length'] - int(time.time())
                self.active.append(row)
        cursor.close()
        return self.active


    #this gets the active contests' testcases
    def get_testcases(self):
        cursor = self.new_cursor()
        contests = ""
        for contest in self.active:
            contests += "OR contest_id='%d' " % contest['contest_id']
        cursor.execute ("SELECT * FROM testcases WHERE contest_id=-1 %s;" % contests)
        result_set = cursor.fetchall ()
        self.testcases = {}
        for row in result_set:
            if not self.testcases.has_key( row['contest_id'] ):
                self.testcases[ row['contest_id'] ] = {}
            if not self.testcases[ row['contest_id'] ].has_key( row['problem'] ):
                self.testcases[ row['contest_id'] ][ row['problem'] ] = []
            self.testcases[ row['contest_id'] ][ row['problem'] ].append(row)
        cursor.close()
        return self.testcases


    #this updates the database with info about the file (like success or file access attempt)
    def file_update(self, fd, msg='', success=False):
        try:
            cursor = self.new_cursor()
            if success:
                cursor.execute("UPDATE submitted SET tested='1', success='1', message='%s' WHERE submitted_id='%d';" % (msg, fd['submitted_id']) )
            else:
                cursor.execute("UPDATE submitted SET tested='1', success='0', message='%s' WHERE submitted_id='%d';" % (msg, fd['submitted_id']) )
            cursor.close()
        except:
            print "Error while running", sys.exc_info()


    #this updates the database with the current time (used for seeing if the backend is active)
    def update_system_info(self, tm=None):
        cursor = self.new_cursor()
        if tm == None:
            tm = str(int(time.time()))
        cursor.execute ("UPDATE server_info SET value='%s' WHERE name='update_time';" % tm)
        cursor.close()


    #clean up the database input so that sql injection cannot happen
    def mysql_cleanup(self, data):
        return data.replace("'","\\'")


    #every time we have any output for a problem, we let the server know, so that we can easily access it with the frontend
    def add_test_output(self, fd, testcase, output):
        cursor = self.new_cursor()
        output = self.mysql_cleanup(output)
        cursor.execute ("INSERT INTO test_output (testcase_id, submitted_id, output) VALUES ('%d', '%d', '%s');" % (testcase['testcase_id'], fd['submitted_id'], output))
        cursor.close()


    #this grabs the list of submitted files that have not yet been tested
    def get_files(self):
        cursor = self.new_cursor()
        contests = ""
        for contest in self.active:
            contests += "OR (contest_id='%d' AND time < %d) " % (contest['contest_id'], contest['start'] + contest['length'])
        cursor.execute ("SELECT * FROM submitted WHERE tested=0 AND (contest_id=-1 %s);" % contests)
        result_set = cursor.fetchall ()
        cursor.close()
        return result_set


    #this gets the current settings from the database
    def get_settings(self):
        cursor = self.new_cursor()
        cursor.execute ("SELECT * FROM server_info;")
        settings = cursor.fetchall ()
        cursor.close()
        return settings


    #updates the info from the database, and if we disconnected, then we reconnect
    def update(self):
        try:
            self.conn.stat()
        except:
            self.connect()
        self.update_system_info()


    #sets up the connection and grabs info
    def __init__(self):
        self.connect()
        self.update()


    #
    def __del__(self):
        try: # we need to say goodbye... so reconnect even though we're about to close if we lost connection...
            self.conn.stat()
        except:
            self.connect()
        self.update_system_info(0) #set time to 0, so that we know what's up on the frontend...
        self.conn.close()


if __name__ == "__main__":
    j=mydb()
    print j.get_testcases()
    print j.get_active_contests()
