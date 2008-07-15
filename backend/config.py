#!/usr/bin/env python

import ConfigParser, os

#load database connection info
config = ConfigParser.ConfigParser()
config.readfp(open('../config.ini'))

prefs = {}
prefs['db_type'] = config.get('db','type')
if prefs['db_type'] == 'mysql':
    prefs['db_host'] = config.get('db','host')
    prefs['db_user'] = config.get('db','user')
    prefs['db_pass'] = config.get('db','pass')
    prefs['db_name'] = config.get('db','name')


#this is for libbox, 'file_tried' is going to be in stderr if someone tries to access a file
prefs['file_tried'] = "!!!!@@@@####$$$$%%|==|FILE ACCESS WAS ATTEMPTED|==|"
prefs['fork_tried'] = "!!!!@@@@####$$$$%%%%|==|FORK WAS ATTEMPTED|==|"
prefs['ld_preload'] = os.getcwd() + "/libbox.so"

#these are useful numbers.. (mostly defined from the old system, so this way I don't need to remember them while coding)
prefs['msg_file'] = '6'
prefs['msg_timeout'] = '3'
prefs['msg_build'] = '1'
prefs['msg_testcase'] = '2'
prefs['msg_success'] = '0'
prefs['msg_proc'] = '7'
prefs['msg_fork'] = '8'
prefs['msg_unknown'] = '15'
prefs['msg_over'] = '15'
