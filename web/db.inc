<?php

global $db;

class db {

//this implements anything that would be something like $this->db_query(''), return resultset

    function rsquery($query)
    {
        $result = $this->query($query);
        $result_set = array();
        while($row = $this->fetch_array($result)) {
            $result_set[] = $row;
        }
        return $result_set;
    }

    function query($str) {}
    function fetch_array($result) {}


    function get_contests() {
        return $this->rsquery("SELECT contest_id, start, length FROM contests ORDER BY start;");
    }

    function get_testcases($contest_id) {
        return $this->rsquery("SELECT * FROM testcases WHERE contest_id=$contest_id ORDER BY problem;");
    }

    function get_users($contest_id) {
        return $this->rsquery("SELECT * FROM users WHERE contest_id=$contest_id;");
    }

    function get_problem_files($contest_id) {
        return $this->rsquery("SELECT problem, filename FROM problem_files WHERE contest_id=$contest_id;");
    }

    function get_submitted($contest_id) {
        return $this->rsquery("SELECT * FROM submitted WHERE contest_id=$contest_id;");
    }

    function get_update_time() {
        return $this->rsquery("SELECT value FROM server_info WHERE name='update_time';");
    }

    function get_cookie() {
        $k = $this->rsquery("SELECT value FROM server_info WHERE name='cookie';");
        return $k[0]['value'];
    }

    function update_cookie($cookie) {
        return $this->rsquery("UPDATE server_info SET value='$cookie' WHERE name='cookie';");
    }

    function get_user_submissions($contest_id, $user_id) {
        return $this->rquery("SELECT submitted_id, problem, filename, tested, success, time, message FROM submitted WHERE contest_id=$contest_id AND user_id=$user_id ORDER BY problem, time;");
    }

    function get_output($submitted_id) {
        return $this->rquery("SELECT testcases.testcase_id, testcases.problem, testcases.input, test_output.output AS returned, testcases.output FROM test_output, testcases WHERE test_output.submitted_id = $submitted_id AND testcases.testcase_id = test_output.testcase_id ORDER BY testcases.testcase_id;");
    }


    function add_submitted($contest_id, $user_id, $program, $filename, $time) {
        return $this->query("INSERT INTO submitted (contest_id,user_id,problem,filename,time) VALUES ($contest_id,$user_id,$program,'$filename','$time');");
    }

    function add_user($contest_id, $user_id, $name) {
        return $this->query("INSERT INTO users (contest_id,user_id,name) VALUES ($contest_id,$user_id,'$name');");
    }

    function add_contest($date, $len) {
        return $this->query("INSERT INTO contests (start,length) VALUES ($date,$len);");
    }

    function add_testcase($contest_id, $problem, $input, $output) { //sql clean input and output
        return $this->query("INSERT INTO testcases (contest_id,problem,input,output) VALUES ($contest_id,$problem,'$input','$output');");
    }

    function add_problem_file($contest_id, $problem, $filename) {
        return $this->query("INSERT INTO problem_files (contest_id,problem,filename) VALUES ($contest_id,$problem,'$filename');");
    }
}
class dbxml extends db {

    var $xml;
    var $fp;

    function get_contests() {
        return $this->db_select('contests', array());
    }

    function get_testcases($contest_id) {
        return $this->db_select('testcases', array('contest_id'=>$contest_id));
    }

    function get_users($contest_id) {
        return $this->db_select('users', array('contest_id'=>$contest_id));
    }

    function get_problem_files($contest_id) {
        return $this->db_select('problem_files', array('contest_id'=>$contest_id));
    }

    function get_submitted($contest_id) {
        return $this->db_select('submitted', array('contest_id'=>$contest_id));
    }

    function get_update_time() {
        return $this->db_select('server_info', array('name'=> 'update_time'));
    }

    function get_user_submissions($contest_id, $user_id) {
        return $this->db_select('submitted', array('contest_id'=>$contest_id, 'user_id'=>$user_id));
    }

    function get_output($submitted_id) {
        $result = array();
        $submission = $this->db_select('test_output', array('submitted_id'=>$submitted_id));
        if(count($submission) > 0) {
            $testcases = $this->db_select('testcases', array());
            foreach($submission as $output) {
                $mytest = array();
                foreach($testcases as $testcase) {
                    if($output['testcase_id'] == $testcase['testcase_id']) {
                        $mytest = $testcase;
                        break;
                    }
                }
                $row = $mytest;
                $row['returned'] = $output['output'];
                $result[] = $row;
            }
        }
        return $result;
    }

    function get_cookie() {
        $k = $this->db_select('server_info', array('name'=>'cookie'));
        return $k[0]['value'];
    }

    function update_cookie($cookie) {
        $this->db_update('server_info', array('name'=>'cookie'), array('value'=>$cookie));
    }

    function add_submitted($contest_id, $user_id, $problem, $filename, $time) {
        $this->db_insert('submitted', array("contest_id"=>$contest_id, 'user_id'=>$user_id, 'problem'=>$problem, 'filename'=>$filename, 'time'=>$time));
    }

    function add_user($contest_id, $user_id, $name) {
        $this->db_insert('users', array('contest_id'=>$contest_id, 'user_id'=> $user_id, 'name'=>$name));
    }

    function add_contest($date, $len) {
        $this->db_insert('contests', array('start'=>$date, 'length'=>$len));
    }

    function add_testcase($contest_id, $problem, $input, $output) {
        $this->db_insert('testcases', array('contest_id'=>$contest_id, 'problem'=>$problem, 'input'=>$input, 'output'=>$output));
    }

    function add_problem_file($contest_id, $problem, $filename) {
        $this->db_insert('problem_files', array('contest_id'=>$contest_id, 'problem'=>$problem, 'filename'=>$filename));
    }

    function __construct() {
        $filename = '../backend/db.xml';

        $this->fp = fopen($filename, "r+");

        $string = "";
        if (flock($this->fp, LOCK_EX)) { // do an exclusive lock
            $string = fread($this->fp, filesize($filename));
        } else {
            echo "Couldn't lock the file!";
            die();
        }
        $this->xml = simplexml_load_string($string);
    }

    function save() {
        rewind($this->fp);
        ftruncate($this->fp, 1);
        fwrite($this->fp, $this->xml->asXML());
    }

    function __destruct() {
        $this->save();
        fclose($this->fp);
    }

    function get_attribute($xmlo, $attr) {
        $attribs = $xmlo->attributes();
        return $attribs[$attr];
    }

    function db_select($tbl, $where) {
        $result = array();
        foreach($this->xml->table as $table) {
            if($table["name"] == $tbl) {
                foreach($table->row as $row) {
                    $add = true;
                    $myrow = array();
                    foreach($row->children() as $col) {
                        $myrow[ (string)$col->getName() ] = (string)$col['value'];
                        if(array_key_exists((string)$col->getName(), $where)) {
                            if( (string)$col['value'] != (string)$where[ (string)$col->getName() ]) {
                                $add = false;
                            }
                        }
                    }
                    if($add) {
                        $result[] = $myrow;
                    }
                }
            }
        }
        return $result;
    }

    function db_insert($tbl, $cols) {
        foreach($this->xml->table as $table) {
            if($table["name"] == $tbl) {
                foreach($table->column as $column) { /* Update auto_increment's and use them. */
                    $attrs = $column->attributes();
                    $str = (string) $column['name'];
                    if(!array_key_exists($str, $cols)) {
                        $cols[$str] = "0";
                    }
                    foreach($attrs as $attr => $v) {
                        if((string)$attr == "auto_increment") {
                        $cols[ (string)$attrs['name'] ] = (string)$attrs['auto_increment'];
                        $column['auto_increment'] = (string)( (int)$attrs['auto_increment'] + 1);
                        }
                    }
                }
                $row = $table->addChild("row");
                foreach($cols as $col => $val) {
                    $column = $row->addChild($col);
                    $column->addAttribute("value", $val);
                }
            }
        }
        $this->save();
    }

    function db_update($tbl, $where, $cols) {
        foreach($this->xml->table as $table) {
            if($table["name"] == $tbl) {
                foreach($table->row as $row) {
                    $update = true;
                    foreach($row->children() as $col) {
                        if(array_key_exists((string)$col->getName(), $where)) {
                            if( $col['value'] != $where[ $col->getName() ]) {
                                $update = false;
                            }
                        }
                    }
                    if($update) {
                        foreach($row->children() as $col) {
                            if(array_key_exists((string)$col->getName(), $cols)) {
                                $col['value'] = $cols[ $col->getName() ];
                            }
                        }
                    }
                }
            }
        }
        $this->save();
    }
}

class dbmysql extends db {

    var $conn;

    function __construct()
    {
        global $dbname, $dbhost, $dbuser, $dbpass;
        $this->conn = mysql_connect($dbhost, $dbuser, $dbpass) or die('Error connecting to mysql');
        mysql_select_db($dbname);
    }

    function query($str)
    {
        return mysql_query($str);
    }

    function fetch_array($result)
    {
        return mysql_fetch_array($result, MYSQL_ASSOC);
    }
}

class dbsqlite extends db {

    var $conn;

    function __construct()
    {
        $this->conn = sqlite_open("../backend/mysqlite.db", 0666, $sqliteerror);
    }

    function query($str)
    {
        return sqlite_query($this->conn, $str);
    }

    function fetch_array($result)
    {
        return sqlite_fetch_array($result, SQLITE_ASSOC);
    }
}

global $dbtype;
if($dbtype == "mysql") {
    $db = new dbmysql;
} elseif($dbtype == "sqlite") {
    $db = new dbsqlite;
} elseif($dbtype == "xml") {
    $db = new dbxml;
}
?>