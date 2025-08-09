<?php
require_once("database.php");

class CRUD extends database {
    public function __construct() {
        parent::__construct();
    }

    public function getData($query) {
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }

        $result = $stmt->get_result();
        $rows = array();

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }

    public function execute($query) {
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function escape_string($string) {
        return $this->conn->real_escape_string($string);
    }

    public function create($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $values = array_values($data);

        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }

        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    public function read($table, $conditions = [], $fields = "*") {
        $where = "";
        $values = [];

        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $column => $value) {
                $whereParts[] = "$column = ?";
                $values[] = $value;
            }
            $where = "WHERE " . implode(" AND ", $whereParts);
        }

        $query = "SELECT $fields FROM $table $where";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }

        if (!empty($values)) {
            $types = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);
        }

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }

    public function update($table, $data, $conditions) {
        $setParts = [];
        $whereParts = [];
        $values = [];

        foreach ($data as $column => $value) {
            $setParts[] = "$column = ?";
            $values[] = $value;
        }

        foreach ($conditions as $column => $value) {
            $whereParts[] = "$column = ?";
            $values[] = $value;
        }

        $setClause = implode(", ", $setParts);
        $whereClause = implode(" AND ", $whereParts);

        $query = "UPDATE $table SET $setClause WHERE $whereClause";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }

        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
    }

    public function delete($table, $conditions) {
        $whereParts = [];
        $values = [];

        foreach ($conditions as $column => $value) {
            $whereParts[] = "$column = ?";
            $values[] = $value;
        }

        $whereClause = implode(" AND ", $whereParts);
        $query = "DELETE FROM $table WHERE $whereClause";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }

        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
    }

    public function exists($table, $conditions) {
        $result = $this->read($table, $conditions, "COUNT(*) as count");
        return ($result !== false && $result[0]['count'] > 0);
    }

    public function getSingle($table, $conditions) {
        $result = $this->read($table, $conditions);
        return ($result !== false && !empty($result)) ? $result[0] : false;
    }
}