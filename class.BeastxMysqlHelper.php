<?

if (!class_exists('BeastxMysqlHelper')) {

class BeastxMysqlHelper {
    
    private $pluginBaseName = null;
    private $tableNamePrefix = null;
    
    public function __construct($pluginBaseName) {
        global $wpdb;
        $this->pluginBaseName = $pluginBaseName;
        $this->tableNamePrefix = $wpdb->prefix . $this->pluginBaseName . '_';
    }
    
    public function newSelect($tableName) {
        return new BeastxSQLSelect($this->tableNamePrefix . $tableName);
    }
    
    public function newInsert($tableName) {
        return new BeastxSQLInsert($this->tableNamePrefix . $tableName);
    }
    
    public function newUpdate($tableName) {
        return new BeastxSQLUpdate($this->tableNamePrefix . $tableName);
    }
    
    public function newReplace($tableName) {
        return new BeastxSQLReplace($this->tableNamePrefix . $tableName);
    }
    
    public function newDelete($tableName) {
        return new BeastxSQLDelete($this->tableNamePrefix . $tableName);
    }
    
    public function newCreateTable($tableName) {
        return new BeastxSQLCreate($this->tableNamePrefix . $tableName);
    }
    
    public function query($sqlObject) {
        global $wpdb;
        $wpdb->query($sqlObject->get());
    }
    
    public function getOne($sqlObject) {
        global $wpdb;
        return $wpdb->get_row($sqlObject->get(), ARRAY_A);
    }
    
    public function getAll($sqlObject) {
        global $wpdb;
        return $wpdb->get_results($sqlObject->get(), ARRAY_A);
    }
    
    public function createSqlTables() {
        global $wpdb;
        require_once $this->pluginBasePath . '/dbSchema.php';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        for ($i = 0; $i < count($BeastxWPProjectsDBSchema); ++$i) {
            $sql = "CREATE TABLE IF NOT EXISTS ";
            $sql.= $wpdb->prefix . str_replace('-', '', $this->pluginBaseName) . "_" . $BeastxWPProjectsDBSchema[$i]['tableName'];
            $sql.= " ( " . $BeastxWPProjectsDBSchema[$i]['schema'] . " )";
            dbDelta($sql);
        }
    }
    
    public function deleteSqlTables() {
        global $wpdb;
        require_once $this->pluginBasePath . '/dbSchema.php';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        for ($i = 0; $i < count($BeastxWPProjectsDBSchema); ++$i) {
            $sql = "DROP TABLE IF EXISTS ";
            $sql.= $wpdb->prefix . str_replace('-', '', $this->pluginBaseName) . "_" . $BeastxWPProjectsDBSchema[$i]['tableName'];
            $wpdb->query($sql);
        }
    }
    
    function getLiteral($value) {
        if ($value === null) {
            return 'null';
        } else if (is_object($value) && method_exists($value, 'toMysql')) {
            return BeastxMysqlHelper::getLiteral($value->toMysql());
        } else if (is_numeric($value)) {
            return $value;
        } else if (is_bool($value)) {
            return $value ? 1 : 0;
        } else {
            return '"' . addslashes($value) . '"';
        }
    }
    
    function escape($text) {
        $text = strtr($text, array('\\' => '\\\\', '\'' => '\\\'', '"' => '\\"',));
        return $text;
    }

    function escapeLike($text) {
        $text = strtr($text, array('\\' => '\\\\', '\'' => '\\\'', '"' => '\\"', '%' => '\\%', '_' => '\\_',));
        return $text;
    }
    
}


class BeastxSQLInsert {
    
    private $fields = array();
    private $table;
    
    public function __construct($table) {
        $this->table = $table;
    }
    
    public function set($field, $value, $trimValue = true) {
        $this->fields[$field] = array(
            'quote' => true,
            'value' => $value,
            'trim' => $trimValue
        );
    }
    
    public function setUnquoted($field, $value) {
        $this->fields[$field] = array(
            'quote' => false,
            'value' => $value,
            'trim' => false
        );
    }
    
    public function get() {
        $sql = "INSERT INTO {$this->table} (";
        $c = 0;
        foreach (array_keys($this->fields) as $fieldName) {
            if ($c > 0) { $sql .= ", "; }
            $sql .= $fieldName;
            $c++;
        }
        $sql .= ") VALUES (";
        $c = 0;
        foreach ($this->fields as $field) {
            if ($c > 0) { $sql .= ", "; }
            if ($field['quote']) {
                $value = $field['value'];
                if ($field['trim'] && is_string($value)) {
                    $value = trim($field['value']);
                }
                $sql .= BeastxMysqlHelper::getLiteral($value);
            } else {
                $sql .= $field['value'];
            }
            $c++;
        }
        $sql .= ")";
        return $sql;
    }
}

class BeastxSQLReplace {
    
    private $fields = array();
    private $table;
    
    public function __construct($table) {
        $this->table = $table;
    }
    
    public function set($field, $value, $trimValue = true) {
        if (is_string($value) && $trimValue) {
            $value = trim($value);
        }
        $this->fields[$field] = $value;
    }
    
    public function get() {
        $sql = "REPLACE INTO {$this->table} (";
        $c = 0;
        foreach ($this->fields as $field => $value) {
            if ($c > 0) { $sql .= ", "; }
            $sql .= $field;
            $c++;
        }
        $sql .= ") VALUES (";
        $c = 0;
        foreach ($this->fields as $field => $value) {
            if ($c > 0) { $sql .= ", "; }
            $sql .= BeastxMysqlHelper::getLiteral($value);
            $c++;
        }
        $sql .= ")";
        return $sql;
    }
}

class BeastxSQLDelete {
    
    private $wheres = array();
    private $table;
    
    public function __construct($table) {
        $this->table = $table;
    }
    
    public function addWhere($condition) {
        $this->wheres[] = $condition;
    }
    
    public function addWhereFieldEquals($field, $value) {
        $this->wheres[] = $field . '=' . BeastxMysqlHelper::getLiteral($value);
    }
    
    public function get() {
        $sql = "DELETE FROM {$this->table} ";
        $c = 0;
        if ($this->wheres) {
            $sql .= ' WHERE ';
            foreach ($this->wheres as $i => $where) {
                if ($i > 0) { $sql .= ' && '; }
                $sql .= '(' . $where . ')';
            }
        }
        return $sql;
    }
}

class BeastxSQLSelect {
    
    private $fields = array();  // SELECT field1, field2...
    private $tables = array();  // FROM table1, table2...
    private $table = false;  // table1
    private $wheres = array();  // WHERE where1, where2...
    private $havings = array(); // HAVING having1, having2...
    private $groups = array();  // GROUP BY group1, group2...
    private $orders = array();  // ORDER BY order1, order2...
    private $start = 0;         // LIMIT start, count
    private $count = false;
    private $distinct = false;
    private $calc_found_rows = false;
    
    private $leftJoinTables = array();
    
    public function __construct($table = false, $as = false) {
        $this->addTable($table, $as);
    }
    
    public function getTableAlias() {
        if ($this->tables[0][1]) {
            return $this->tables[0][1];
        } else {
            return $this->tables[0][0];
        }
    }
    
    public function addField($field) {
        $this->fields[] = $field;
    }
    
    public function setDistinct($value) { /* true or false */
        $this->distinct = (boolean) $value;
    }
    
    public function setCalcFoundRows($value) { /* true or false */
        $this->calc_found_rows = (boolean) $value;
    }
    
    public function addFields($fields, $table = false) {
        foreach ($fields as $field) {
            if ($table !== false) {
                $field = $table . '.' . $field;
            }
            $this->addField($field);
        }
    }
    
    public function addTable($table, $as = false) {
        $this->tables[] = array($table, $as);
    }
    
    public function addLeftJoin($table, $on = false, $as = false) {
        if ($on === false) {
            if ($as) {
                $on = "{$as}.id = " . $this->getTableAlias() . ".{$table}Id";
            } else {
                $on = "{$table}.id = " . $this->getTableAlias() . ".{$table}Id";
            }
        }
        $this->leftJoinTables[] = array($table, $as, '(' . $on . ')');
    }
    
    public function addJoin($table, $on = false, $as = false) {
        if ($on === false) {
            if ($as) {
                $on = "{$as}.id=" . $this->getTableAlias() . ".{$table}Id";
            } else {
                $on = "{$table}.id=" . $this->getTableAlias() . ".{$table}Id";
            }
        }
        $this->addTable($table, $as);
        $this->addWhere($on);
    }
    
    public function addWhereFieldIsInArray($field, $array) {
        $where = '';
        foreach ($array as $value) {
            if ($where) { $where .= ' || '; }
            $where .= '(' . $field . '=' . BeastxMysqlHelper::getLiteral($value) . ')';
        }
        $this->addWhere($where);
    }
    
    public function addWhereFieldIsNotInArray($field, $array) {
        if ($array) {
            $where = '';
            foreach ($array as $value) {
                if ($where) { $where .= ' && '; }
                $where .= '(' . $field . '!=' . BeastxMysqlHelper::getLiteral($value) . ')';
            }
            $this->addWhere($where);
        }
    }
    
    public function addWhereFieldEquals($field, $value) {
       $this->addWhere($field . '=' . BeastxMysqlHelper::getLiteral($value));
    }
    
    public function addWhereFieldNotEquals($field, $value) {
        $this->addWhere($field . '<>' . BeastxMysqlHelper::getLiteral($value));
    }
    
    public function addWhereAllWordsPresentInFields($string, $fieldsToSearch) {
        $conditions = array();
        $words = explode(" ", $string);
        foreach ($words as $word) {
            $word = trim($word);
            if ($word) {
                $optionalParts = array();
                foreach ($fieldsToSearch as $field) {
                    $optionalParts[] = $field . ' LIKE "%' . BeastxMysqlHelper::escapeLike($word) . '%"';
                }
                $conditions[] = "(" . implode(" OR ", $optionalParts) . ")";
            }
        }
        $this->addWhere(implode(" AND ", $conditions));
    }
    
    public function addWhere($string) {
        $this->wheres[] = '(' . $string . ')';
    }
    
    public function addGroupBy($string) {
        $this->groups[] = $string;
    }
    
    public function addOrder($string) {
        $this->orders[] = $string;
    }
    
    public function addLimit($start, $count) {
        $this->start = $start;
        $this->count = $count;
    }
    

    public function get() {
        $sql = "SELECT ";
        
        if ($this->distinct) { $sql .= "DISTINCT "; }
        if ($this->calc_found_rows) { $sql .= "SQL_CALC_FOUND_ROWS "; }
        
        $sql .= implode(', ', $this->fields). " FROM (";
        
        $i = 0;
        foreach ($this->tables as $table) {
            if ($i > 0) { $sql .= ", "; }
            list($tableName, $tableAlias) = $table;
            $sql .= $tableName;
            if ($tableAlias) { $sql .= " AS $tableAlias"; }
            ++$i;
        }
        
        $sql .= ")";
        
        foreach ($this->leftJoinTables as $table_join) {
            list($tableName, $tableAlias, $on) = $table_join;
            $sql .= " LEFT JOIN " . $tableName;
            if ($tableAlias) { $sql .= " AS $tableAlias"; }
            $sql .= " ON $on";
        }
        
        if ($this->wheres) { $sql .= " WHERE " . implode(' && ', $this->wheres); }
        if ($this->groups) { $sql .= " GROUP BY " . implode(', ', $this->groups); }
        if ($this->orders) { $sql .= " ORDER BY " . implode(', ', $this->orders); }
        if ($this->count) { $sql .= " LIMIT {$this->start}, {$this->count}"; }
        return $sql;
    }
    
    public function getCount() {
        $sql = "SELECT COUNT(*) FROM " . implode(', ', $this->tables);
        
        foreach ($this->leftJoinTables as $table_join) {
            list($table, $on) = $table_join;
            $sql .= " LEFT JOIN $table ON $on";
        }
        
        if ($this->wheres) { $sql .= " WHERE " . implode(' && ', $this->wheres); }
        if ($this->groups) { $sql .= " GROUP BY " . implode(', ', $this->groups); }
        if ($this->orders) { $sql .= " ORDER BY " . implode(', ', $this->orders); }
        return $sql;
    }
}

class BeastxSQLUpdate {
    
    private $fields = array();
    private $wheres = array();
    private $table;
    
    public function __construct($table) {
        $this->table = $table;
    }
    
    public function set($field, $value, $trimValue = true) {
        if ($trimValue && is_string($value)) {
            $value = trim($value);
        }
        $this->fields[$field] = BeastxMysqlHelper::getLiteral($value);
    }
    
    public function setUnquoted($field, $value) {
        $this->fields[$field] = $value;
    }
    
    public function addWhere($condition) {
        $this->wheres[] = $condition;
    }
    
    public function addWhereFieldEquals($field, $value) {
        $this->wheres[] = $field . '=' . BeastxMysqlHelper::getLiteral($value);
    }
    
    public function get() {
        $sql = "UPDATE {$this->table} SET ";
        $c = 0;
        foreach ($this->fields as $field => $value) {
            if ($c > 0) { $sql .= ", "; }
            $sql .= "$field=" . $value;
            $c++;
        }
        if ($this->wheres) {
            $sql .= ' WHERE ';
            foreach ($this->wheres as $i => $where) {
                if ($i > 0) { $sql .= ' && '; }
                $sql .= '(' . $where . ')';
            }
        }
        return $sql;
    }
}


class BeastxSQLCreateTable {
    
    private $fields;
    private $table;
    private $indexes = array();
    private $primary_keys = array();
    
    public function __construct($table) {
        $this->table = $table;
    }
    
    public function add($field, $type) {
        $this->fields[$field] = array('type' => $type);
    }
    
    public function setPrimaryKeys($fields) {
        $this->primary_keys = $fields;
    }
    
    public function get() {
        $sql = 'CREATE TABLE ' . $this->table . ' (';
        $comma = false;
        foreach ($this->fields as $name => $field) {
            if ($comma) {
                $sql .= ', ';
            } else {
                $comma = true;
            }
            $sql .= $name . ' ' . $field['type'];
        }
        if ($this->primary_keys) {
            $sql .= ', PRIMARY KEY (' . implode(', ', $this->primary_keys) . ')';
        }
        $sql .= ')';
        return $sql;
    }
}


class BeastxSQLDeleteTable {
    
    private $table;
    
    public function __construct($table) {
        $this->table = $table;
    }
    
    public function get() {
        $sql = "DROP TABLE IF EXISTS {$this->table} ";
        return $sql;
    }
}

}
?>