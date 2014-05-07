<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace system\db;

/**
 *
 * @author masfu
 */
use system\core\DbException;

abstract class DbAdapter {

    protected $host;
    protected $username;
    protected $password;
    protected $database;
    protected $conn;
    protected $dsn;
    protected $port;
    protected $persistent;
    protected $autoinit;
    protected $resultid;

    /*
     * for active record
     */
    protected $column = array();
    protected $criteria = '';
    protected $tables = array();
    protected $join = '';
    protected $distinct = FALSE;
    protected $limit = '';
    protected $offset = '';
    protected $having;
    protected $order;
    protected $orderType;
    protected $group = array();
    protected $sql = '';

    public function connect() {
        try {
            if (!($this->conn = new \PDO($this->dsn, $this->username, $this->password))) {
                throw new DbException("Could not connect to the database");
            }

            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->drivername = $this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (DbException $e) {
            echo $e->printError();
        }
    }

    public function pconnect() {
        try {

            $option = array(PDO::ATTR_PERSISTENT => true);

            if (!($this->conn = new \PDO($this->dsn, $this->username, $this->password, $option))) {
                throw new DbException("Could not connect to the database");
            }

            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->drivername = $this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (DbException $e) {
            echo $e->printError();
        }
    }

    public function disconnect() {
        
    }

    public function initialize() {
        if (!$this->persistent) {
            $this->connect();
        } else {
            $this->pconnect();
        }
    }

    /**
     * 
     * @param type $table
     */
    public function columns($table) {
        $column = array();
        $result = $this->_column($table)->fetchAssoc();

        foreach ($result as $name => $val) {
            $column[$name] = $this->_columnInfo($val);
        }
        return $column;
    }

    /*
     * 
     */

    public function tables($like = null) {
        return $this->_tables($like)->fetchAssoc();
    }

    /**
     * 
     */
    public function transaction() {
        if (!$this->conn->beginTransaction()) {
            throw new DbException();
        }
    }

    /**
     * 
     */
    public function commit() {
        if (!$this->conn->commit()) {
            throw new DbException();
        }
    }

    /**
     * 
     */
    public function rollback() {
        if (!$this->connection->rollback()) {
            throw new DbException();
        }
    }

    public function insert($table, $data = array()) {
        $fields = array_keys($data);
        foreach ($data as $key => $val)
            $data[$key] = $this->escape($val);

        return $this->query("INSERT INTO $table (" . implode(', ', $fields) . ") VALUES ('" . implode("','", $data) . "')");
    }

    public function update($table, $data, $where = null) {

        $datas = array();
        $wheres = array();

        foreach ($data as $key => $val) {
            $datas[] = " $key = '{$this->escape($val)}'";
        }

        if (is_array($where)) {
            foreach ($where as $col => $val) {
                $wheres[] = "$col = '" . $this->escape($val) . "'";
            }
            $where = implode(' AND ', $wheres);
        }
        $this->query("UPDATE $table SET " . implode(', ', $datas) . ' WHERE ' . $where);
        return $this;
    }

    public function delete($table, $where = array()) {

        if (is_array($where)) {

            foreach ($where as $col => $val) {
                $wheres[] = "$col = '" . $this->escape($val) . "'";
            }
            $where = implode(' AND ', $wheres);
        } else {
            return false;
        }
        return $this->query("DELETE FROM $table WHERE $where");
    }

    public function limit($limit = 0, $offset = 0) {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    public function from() {
        $table = func_get_args();
        try {
            if (!empty($table)) {
                $this->tables = array_merge($this->tables, $table);
            } else {
                throw new DbException("please select at least one table");
            }
        } catch (DbException $e) {
            echo $e->printError();
        }
        return $this;
    }

    public function where($where) {

        if (is_array($where)) {
            foreach ($where as $col => $val) {
                $wheres[] = "$col = '" . $this->escape($val) . "'";
            }
            $this->criteria = implode(' AND ', $wheres);
        }
        if (is_string($where)) {
            $this->criteria = $where;
        }

        return $this;
    }

    public function select() {
        $column = func_get_args();
        if (!empty($column)) {
            $this->column = array_merge($this->column, $column);
        }
        return $this;
    }

    public function join($join) {
        $this->joins = $join;
        return $this;
    }

    public function orderBy($column, $type = 'ASC') {
        $this->order = $column;
        $this->orderType = $type;
        return $this;
    }

    public function groupBy($column) {
        $column = func_get_args();
        if (!empty($column)) {
            $this->group = array_merge($this->group, $column);
        }
        return $this;
    }

    public function distinct($val = TRUE) {
        $this->distinct = (is_bool($val)) ? $val : TRUE;
        return $this;
    }

    public function having($having) {
        $this->having = $having;
        return $this;
    }

    public function buildSelect() {

        $sql = "SELECT ";

        $sql .= (empty($this->column)) ? " * " : implode(', ', $this->column);

        $sql .= " FROM " . implode(', ', $this->tables);

        $sql .=($this->join) ? $this->join : "";

        $sql .= ($this->criteria) ? " WHERE " . $this->criteria : "";

        $sql .= (!empty($this->group)) ? " GROUP BY " . implode(', ', $this->group) : "";

        $sql .= (!empty($this->having)) ? " HAVING " . $this->having : "";

        $sql .= ($this->order) ? " ORDER BY " . $this->order . " " . $this->orderType : "";

        if ($this->limit || $this->offset) {
            $sql = $this->_limit($sql, $this->limit, $this->offset);
        }
        return $sql;
    }

    public function get() {
        $this->sql = $this->buildSelect();
        $this->query($this->sql);
        return $this;
    }

    public function query($sql, &$value = array()) {
        try {
            if ($this->autoinit) {
                $this->connect();
            }

            if (!($this->stmt = $this->conn->prepare($sql))) {
                $message = $this->conn->errorInfo();
                $errorCode = $this->conn->errorCode();
                throw new DbException($message, $errorCode);
            }
            $this->bindParam($value);
            $this->stmt->execute($value);
        } catch (DbException $e) {
            $e->printError();
        }
    }

    public function fetchObject() {
        $result = array();
        while ($row = $this->stmt->fetch(\PDO::FETCH_OBJ)) {
            $result[] = $row;
        }
        return $result;
    }

    public function fetchAssoc() {
        $result = array();
        while ($row = $this->stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        return $result;
    }

    public function escape($str) {
        $p = new \PDO;
        $p->
                $s = $p->query($statement);

        return $this->stmt->quote($str);
    }

    public function insertId($sequence = null) {
        $this->conn->lastInsertId($sequence);
    }

    public function bindParam(&$data = array()) {
        try {
            foreach ($data as $key => $value) {
                $name = ":" . $key;

                $this->stmt->bindParam($name, $data[$key]);
            }
        } catch (DbException $e) {
            
        }
    }

    abstract public function _columnInfo($column);

    abstract public function _column($column);

    abstract public function setEncoding($charset);

    abstract public function _tables($like = null);

    abstract public function _limit($sql, $limit, $offset);
}