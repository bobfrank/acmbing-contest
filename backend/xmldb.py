#!/usr/bin/env python

import xml.dom.minidom
import fcntl, time, sys
from config import prefs

class mydb:
    def __init__(self):
        self.active = []
        self.update()


    def opendoc(self, lock=False):
        fd = open('db.xml', 'r+')
        if lock:
            fcntl.flock(fd, fcntl.LOCK_EX)
        dat = fd.read()
        return (fd, xml.dom.minidom.parseString(dat))


    def closedoc(self, fd, doc, lock=False):
        if lock:
            fd.seek(0)
            fd.truncate()
            fd.write(doc.toxml())
        fd.close()


    def dbinsert(self, tbl, row): #row format: {'colunm': 'value', 'colunm2': 'value2'} also, no checks are performed here...
        (fd, doc) = self.opendoc(True)
        tables = doc.childNodes[0].childNodes
        for table in tables:
            try:
                if table.getAttribute('name') == tbl:
                    newrow = doc.createElement("row")
                    for col in row:
                        newcol = doc.createElement(col)
                        newcol.setAttribute("value", str(row[col]))
                        newrow.appendChild(newcol)
                    table.appendChild(newrow)
            except:
                pass
        self.closedoc(fd, doc, True)


    def dbselect(self, tbl, where): #where = {'column': 'is this'}
        (fd, doc) = self.opendoc(True)
        tables = doc.childNodes[0].childNodes
        results = []
        for table in tables:
            try:
                if table.getAttribute('name') == tbl:
                    for row in table.childNodes:
                        if row.localName == 'row':
                            k = True
                            values = {}
                            for col in row.childNodes:
                                values[ col.localName ] = col.getAttribute('value')
                                if where.has_key(col.localName):
                                    if col.getAttribute('value') != where[col.localName]:
                                        k = False
                            if k:
                                results.append(values)
            except:
                pass
        return results
        self.closedoc(fd, doc, True)


    def dbupdate(self, tbl, where, setto): #params = {'column': 'isthis'}, setto = {'column': 'tovalue'}
        (fd, doc) = self.opendoc(True)
        tables = doc.childNodes[0].childNodes
        for table in tables:
            try:
                if table.getAttribute('name') == tbl:
                    for row in table.childNodes:
                        if row.localName == 'row':
                            k = True
                            for col in row.childNodes:
                                if where.has_key(col.localName):
                                    if col.getAttribute("value") != where[col.localName]:
                                        k = False
                            if k:
                                for col in row.childNodes:
                                    if setto.has_key(col.localName):
                                        col.setAttribute('value', setto[col.localName])
            except:
                pass
        self.closedoc(fd, doc, True)


    def update(self):
        self.dbupdate('server_info', {'name': 'update_time'}, {'value': str(time.time()) })
        self.get_active_contests()

    def file_update(self, fd, msg='', success=False):
        try:
            if msg != prefs['msg_proc']:
                print "Submission", fd['submitted_id'], ", User", fd['user_id'], ", Problem", fd['problem']
            if success:
                print "Success"
                self.dbupdate('submitted', {'submitted_id': str(fd['submitted_id'])}, {'tested': '1', 'success': '1', 'message': msg})
            else:
                message = {'1':  "Error: Did Not Build",
                           '2':  "Error: Testcase Failed",
                           '3':  "Error: Program timed out",
                           '15': "Error: Unknown Error"}
                if msg != prefs['msg_proc']:
                    print message[msg]
                self.dbupdate('submitted', {'submitted_id': str(fd['submitted_id'])}, {'tested': '1', 'success': '0', 'message': msg})
        except:
            print "Error while running", sys.exc_info()


    #every time we have any output for a problem, we let the server know, so that we can easily access it with the frontend
    def add_test_output(self, fd, testcase, output):
        self.dbinsert('test_output', {'testcase_id': testcase['testcase_id'], 'submitted_id': fd['submitted_id'], 'output': output})


    #this grabs the active contests
    def get_active_contests(self):
        result_set = self.dbselect('contests',{})
        self.active = []
        for row in result_set:
            if int(row['start']) < time.time() and int(row['start']) + int(row['length']) > time.time():
                row['time_left'] = int(row['start']) + int(row['length']) - int(time.time())
                self.active.append(row)
        return self.active


    #this gets the active contests' testcases
    def get_testcases(self):
        if len(self.active) == 1:
            self.testcases = {}
            result_set = self.dbselect('testcases', {'contest_id': self.active[0]['contest_id']})
            for row in result_set:
                if not self.testcases.has_key( int(row['contest_id']) ):
                    self.testcases[ int(row['contest_id']) ] = {}
                if not self.testcases[ int(row['contest_id']) ].has_key( int(row['problem']) ):
                    self.testcases[ int(row['contest_id']) ][ int(row['problem']) ] = []
                self.testcases[ int(row['contest_id']) ][ int(row['problem']) ].append(row)
            return self.testcases
        else:
            return {}


    #this grabs the list of submitted files that have not yet been tested
    def get_files(self):
        if len(self.active) == 1:
            results = self.dbselect('submitted', {'tested': '0', 'contest_id': self.active[0]['contest_id']})
            result_set = []
            for result in results:
                if int(result['time']) < time.time():
                    result_set.append(result)
            return result_set
        else:
            return []


    #this gets the current settings from the database
    def get_settings(self):
        return self.dbselect('server_info',{})


    def __del__(self):
        #set time to 0, so that we know what's up on the frontend...
        self.dbupdate('server_info', {'name': 'update_time'}, {'value': '0'})


if __name__ == "__main__":
    db = mydb()
    db.update()
    #print db.dbselect('server_info', {})
    #db.dbinsert("users", {"testcase_id": 1, "contest_id": 1, "problem": 1, "input": "hahaha", "output": "this is madness"})

