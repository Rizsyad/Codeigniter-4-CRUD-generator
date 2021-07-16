<?php

class Database
{
    private $hostname, $username, $password, $database;
    private $conn;

    public function setHost($hostname)
    {
        $this->hostname = $hostname;
    }

    public function setUsename($username)
    {
        $this->username = $username;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setDatabase($database)
    {
        $this->database = $database;
    }

    public function connection()
    {
        $this->conn = mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
    }

    public function checkConnection()
    {
        return mysqli_connect_errno();
    }

    protected function query_executed($sql)
    {
        $result = mysqli_query($this->conn, $sql);
        return $result;
    }

    protected function get_fetch_data_array($r)
    {
        $array = array();
        while ($rows = mysqli_fetch_array($r)) {
            $array[] = $rows;
        }
        return $array;
    }

    protected function get_fetch_data_assoc($r)
    {
        $array = array();
        while ($rows = mysqli_fetch_assoc($r)) {
            $array[] = $rows;
        }
        return $array;
    }

    public function getTable()
    {
        $sql = "SHOW TABLES";
        $result = $this->query_executed($sql);
        return $this->get_fetch_data_array($result);
    }

    public function getDescTable($table)
    {
        $sql = "DESC $table";
        $result = $this->query_executed($sql);
        return $this->get_fetch_data_assoc($result);
    }

    public function getPrimaryKey($table)
    {
        $sql = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
        $result = $this->query_executed($sql);
        return $this->get_fetch_data_assoc($result);
    }
}
