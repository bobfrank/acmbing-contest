#!/usr/bin/env python

import re, os, time, sys
from subprocess import PIPE
from subprocess import Popen
import subprocess
from config import prefs

if prefs['db_type'] == "xml":
    from xmldb import mydb
else:
    from sql import mydb

class backend:
    #clean up spacing so that tabs and multiple spaces and newlines are all one
    def cleanup(self, str1):
        str1_ls = re.sub("(\t|\n)", " ", str1).split(" ")
        #get rid of excess whitespace...
        for i in range(str1_ls.count("")):
            str1_ls.remove("")

        return "".join(str1_ls)


    #compare a testcase to an output
    def diff(self, str1, testcase):
        str1 = self.cleanup(str1)

        if testcase != str1:
            return True

        return False


    #start up the database connection and grab latest info
    def __init__(self):
        print "Running backend"
        self.db = mydb()
        self.update()
        self.shutdown = False


    def __del__(self):
        print "Shutting down backend"
        os.popen('rm -f ../build/*').close()


    #this function updates the settings from the database
    def update_settings(self):
        self.settings = {}
        for setting in self.db.get_settings():
            self.settings[setting['name']] = setting['value']


    #the purpose of this function is to update the time on the database, update the contest list, and so on.
    #it should be called every minute or so
    def update(self):
        self.last_update = time.time()
        self.db.update()
        self.update_settings()
        self.testcases = self.db.get_testcases()
        self.active = self.db.get_active_contests()


    #builds the the source and sets some stuff in file, and returns the changes..
    def build(self, fd):

        if fd['filename'][-2:].lower() == '.c':
            fd['compiler'] = 'gcc'
            (child_stdin, child_stdout, child_stderr) = os.popen3("gcc -o ../build/%sa.out ../files/%s" % (fd['submitted_id'],fd['filename']))
            r = file.read(child_stderr) #if we decide to save build errors, that would be here
            if len(r) > 0:
                print "Build Error: ", r
                fd['error'] = True

        if fd['filename'][-4:].lower() == '.cpp':

            fd['compiler'] = 'gcc'
            (child_stdin, child_stdout, child_stderr) = os.popen3("g++ -o ../build/%sa.out ../files/%s" % (fd['submitted_id'],fd['filename']))
            r = file.read(child_stderr) #if we decide to save build errors, that would be here
            if len(r) > 0:
                print "Build Error: ", r
                fd['error'] = True

        elif fd['filename'][-5:].lower() == '.java':
            fd['compiler'] = 'javac'
            (child_stdin, child_stdout, child_stderr) = os.popen3("javac -d ../build ../files/%s" % fd['filename'])
            r = file.read(child_stderr)
            #print "Build Error: ", r
            if len(r) > 0:
                fd['error'] = True
                return fd
            path="../build"
            dirList=os.listdir(path) #also if a package is made (and thus a folder)... handle that...
            for fname in dirList:
                if fname[-6:].lower() == ".class":
                    fd['classname'] = fname[:-6]
                    break
        return fd


    #returns a Popen object which can be used for testing each testcase...
    def test(self, fd):

        if fd['compiler'] == 'gcc':
            try:
                return (fd, Popen("../build/%sa.out" % fd['submitted_id'], stdin=PIPE, stdout=PIPE, stderr=PIPE, env={"LD_PRELOAD": prefs['ld_preload']}))
            except:
                print "Error a.out not found"
                return (fd, -1)

        elif fd['compiler'] == 'javac':
            try:
                #we may want a directory for each submitted_id or something...
                return (fd, Popen(['java', '-Djava.security.manager', '-Djava.security.policy=blankpolicyfile','-classpath','../build', fd['classname']], stdin=PIPE, stdout=PIPE, stderr=PIPE))
            except:
                return (fd, -1)


    def run_testcase(self, fd, testcase):
        (fd, p) = self.test(fd)
        if p == -1:
            #print "Error: could not run."
            self.db.file_update(fd, msg=prefs['msg_build'])
            return False
        try:
            file.write( p.stdin, testcase['input'] )
            file.close( p.stdin )
        except:
            self.db.file_update(fd, msg=prefs['msg_testcase'])
            return False

        #start timer... poll every once in a while...
        mytime = 0.0
        while p.poll() == None:
            mytime += 0.5
            if mytime > int(self.settings['timeout']) * 5: #this is way too long...
                os.kill(p.pid, 9)
                #print "Error: Program timed out."
                self.db.file_update(fd, msg=prefs['msg_timeout'])
                return False
            time.sleep(0.5)

        if os.times()[2] > int(self.settings['timeout']): #and the actual timeout...
            #print "Error: Program timed out."
            self.db.file_update(fd, msg=prefs['msg_timeout'])

        k = file.read(p.stdout)
        er = file.read(p.stderr)

        if er.find(prefs['file_tried']) >= 0:
            self.db.file_update(fd, msg=prefs['msg_file'])
            return False
        elif er.find(prefs['fork_tried']) >= 0:
            self.db.file_update(fd, msg=prefs['msg_fork'])
            return False

        self.db.add_test_output(fd, testcase, k)

        if self.diff(k, testcase['output']):
            #print "Error: Test case failed.", k
            #print testcase, k
            self.db.file_update(fd, msg=prefs['msg_testcase'])
            return False

        return True


    #builds the problem, and tests each testcase on the problem.
    def compile_and_test(self, fd):
        #try:
            fd = self.build(fd)

            if self.testcases.has_key(int(fd['contest_id'])) and self.testcases[ int(fd['contest_id']) ].has_key( int(fd['problem']) ):

                if not fd['error']:
                    for testcase in self.testcases[ int(fd['contest_id']) ][ int(fd['problem']) ]:
                        r, w = os.pipe()
                        if os.fork() == 0:
                            os.close(r)
                            w = os.fdopen(w, 'w')

                            if not self.run_testcase(fd, testcase):
                                w.write("error")
                            w.close()
                            os._exit(0)
                        os.wait()
                        os.close(w)
                        r = os.fdopen(r, 'r')
                        err = len(r.read())
                        r.close()
                        if err > 0:
                            return False # a testcase has failed

                    #print "Success." #or we would have left before...
                    self.db.file_update(fd, msg=prefs['msg_success'], success=True)
                else:
                    self.db.file_update(fd, msg=prefs['msg_build'])
            else:
                #this should never happen (in theory), but we should handle just in case I'm just being dumb
                #print "Error: no such problem or active contest", sys.exc_info()
                #why did we grab it? Did the competition just end seconds ago?
                self.db.file_update(fd, msg=prefs['msg_over'])
        #except:
        #    print "Unexpected Exception while testing ", file, sys.exc_info()
        #    self.db.file_update(file, msg=prefs['msg_unknown'])


    #this is the main loop, it will run the entire time and it manages everything else.
    #it also includes a timer to make sure that everything is up to date
    # since it has a timer type thing, it will also call update as needed
    def mainloop(self):
        #if we are running xml, then start the xml-sql-db server
        while 1:
            if self.shutdown: #TODO maybe a request from the website or cli... or something
                return


            #do we have anything to process?
            for fd in self.db.get_files():
                fd['compiler'] = ""
                fd['error'] = False
                #print "Testing ", file['user_id'], ", Problem ", file['problem']
                if os.fork() == 0:
                    self.db.file_update(fd, msg=prefs['msg_proc']) #set to processing...
                    self.compile_and_test(fd)
                    os._exit(0)

            time.sleep(1)

            #has it been prefs['update'] seconds since our last update? call self.update()
            if time.time() > int(self.last_update) + int(self.settings['update_rate']):
                self.update()


if __name__ == "__main__":
    #perhaps we should run this as a daemon
    b = backend()
    b.mainloop()
    del b
    #file = {"filename": "bob.cpp", "problem": 1, "error": False, "compiler": "", "contest_id": 1}
    #b.compile_and_test(file)
