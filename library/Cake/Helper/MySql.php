<?php
namespace Cake;

class Helper_MySql
{

    protected static $_tablesList;

    /**
     *
     * @param array $tables
     */
    public static function createTables(array $tables)
    {
        $db = \XenForo_Application::getDb();

        foreach ($tables as $tableName => $rows) {
            if (!self::isTableExists($tableName)) {
                $sql = "CREATE TABLE IF NOT EXISTS `" . $tableName . "` (";
                $sqlRows = array();
                foreach ($rows as $rowName => $rowParams) {
                    $sqlRows[] = "`" . $rowName . "` " . $rowParams;
                }
                $sql .= implode(",", $sqlRows);
                $sql .= ") ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci";
                $db->query($sql);
                if (self::$_tablesList) {
                    self::$_tablesList[] = strtolower($tableName);
                }
            } else {
                $tableChanges = array(
                    $tableName => $rows
                );
                self::makeTableChanges($tableChanges);
            }
        }
    }

    /**
     *
     * @param array $tables
     */
    public static function dropTables(array $tables)
    {
        $db = \XenForo_Application::getDb();

        foreach ($tables as $tableName => $rows) {
            $sql = "DROP TABLE IF EXISTS `" . $tableName . "` ";
            $db->query($sql);
            if (self::$_tablesList && in_array($tableName, self::$_tablesList)) {
                unset(self::$_tablesList[array_search($tableName, self::$_tablesList)]);
            }
        }
    }

    /**
     *
     * @param array $tableChanges
     */
    public static function makeTableChanges(array $tableChanges)
    {
        $db = \XenForo_Application::getDb();

        $undoTableChanges = array();
        foreach ($tableChanges as $tableName => $rows) {
            if (self::isTableExists($tableName)) {
                $describeTable = $db->describeTable($tableName);
                $keys = array_keys($describeTable);
                $sql = "ALTER IGNORE TABLE `" . $tableName . "` ";
                $sqlAdd = array();
                foreach ($rows as $rowName => $rowParams) {
                    if (!$rowParams) {
                        $undoTableChanges[$tableName][$rowName] = '';
                        continue;
                    }
                    if (!empty($describeTable[$rowName])) {
                        $rowPattern = self::getRowPatternFromTableDescription($describeTable[$rowName]);
                        if (preg_match($rowPattern, $rowParams)) {
                            continue;
                        }
                    }
                    if (strpos($rowParams, 'PRIMARY KEY') !== false) {
                        if (self::getExistingPrimaryKeys($tableName)) {
                            $sqlAdd[] = "DROP PRIMARY KEY ";
                        }
                    }
                    if (in_array($rowName, $keys)) {
                        $sqlAdd[] = "CHANGE `" . $rowName . "` `" . $rowName . "` " . $rowParams;
                    } else {
                        $sqlAdd[] = "ADD `" . $rowName . "` " . $rowParams;
                    }
                }
                if ($sqlAdd) {
                    $sqlAdd[] = 'ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci';
                    $sql .= implode(", ", $sqlAdd);
                    $db->query($sql);
                }
            }
        }
        self::undoTableChanges($undoTableChanges);
    }

    /**
     *
     * @param array $tableChanges
     */
    public static function undoTableChanges(array $tableChanges)
    {
        $db = \XenForo_Application::getDb();

        foreach ($tableChanges as $tableName => $rows) {
            if (self::isTableExists($tableName)) {
                $keys = array_keys($db->describeTable($tableName));
                foreach ($rows as $rowName => $rowParams) {
                    if (in_array($rowName, $keys)) {
                        $sql = "ALTER IGNORE TABLE `" . $tableName . "` DROP `" . $rowName . "`";
                        $db->query($sql);
                    }
                }
            }
        }
    }

    /**
     *
     * @param string $tableName
     */
    public static function getExistingPrimaryKeys($tableName)
    {
        $db = \XenForo_Application::getDb();

        $primaryKeys = array();
        if (self::isTableExists($tableName)) {
            $columns = $db->describeTable($tableName);
            foreach ($columns as $columnName => $column) {
                if ($column['PRIMARY']) {
                    $primaryKeys[] = $columnName;
                }
            }
        }
        return $primaryKeys;
    }

    public static function addPrimaryKeys(array $primaryKeys)
    {
        $db = \XenForo_Application::getDb();

        foreach ($primaryKeys as $tableName => $primaryKey) {
            $oldKey = self::getExistingPrimaryKeys($tableName);
            $keyDiff = array_diff($primaryKey, $oldKey);
            if (!empty($keyDiff)) {
                $sql = "ALTER TABLE `" . $tableName . "`
                    " . (empty($oldKey) ? "" : "DROP PRIMARY KEY, ") . "
                    ADD PRIMARY KEY(" . implode(",", $primaryKey) . ")";
                $db->query($sql);
            }
        }
    }

    public static function getExistingKeys($tableName)
    {
        $db = \XenForo_Application::getDb();

        $keys = array();
        if (self::isTableExists($tableName)) {
            $columns = $db->describeTable($tableName);
            $indexes = $db->fetchAll('SHOW INDEXES FROM  `' . $tableName . '`');
            foreach ($indexes as $index) {
                if (!isset($keys[$index['Key_name']])) {
                    $keys[$index['Key_name']] = $index;
                }
                $keys[$index['Key_name']]['Column_names'][] = $index['Column_name'];
            }
        }
        return $keys;
    }

    public static function addKeys(array $keys, $unique = false, $fullText = false)
    {
        $db = \XenForo_Application::getDb();

        $index = $unique ? 'UNIQUE' : 'INDEX';
        if ($fullText) {
            $index = 'FULLTEXT ' . $index;
        }

        foreach ($keys as $tableName => $key) {
            $oldKeys = self::getExistingKeys($tableName);
            foreach ($key as $keyName => $keyColumns) {
                if (isset($oldKeys[$keyName])) {
                    $keyDiff = array_diff($oldKeys[$keyName]['Column_names'], $keyColumns);
                    if ($keyDiff) {
                        $db->query(
                            '
                                ALTER TABLE `' . $tableName . '`
                                DROP INDEX `' . $keyName . '`,
                                ADD ' . $index . '
                                    `' . $keyName . '`
                                    (' . implode(',', $keyColumns) . ')
                            ');
                    }
                } else {
                    $db->query(
                        '
                            ALTER TABLE `' . $tableName . '`
                            ADD ' . $index . '
                                `' . $keyName . '`
                                (' . implode(',', $keyColumns) . ')
                        ');
                }
            }
        }
    }

    public static function dropKeys(array $keys)
    {
        $db = \XenForo_Application::getDb();

        foreach ($keys as $tableName => $key) {
            $oldKeys = self::getExistingKeys($tableName);
            foreach ($key as $keyName => $keyColumns) {
                if (isset($oldKeys[$keyName])) {
                    $db->query(
                        '
                            ALTER TABLE `' . $tableName . '`
                            DROP INDEX `' . $keyName . '`
                        ');
                }
            }
        }
    }

    public static function isTableExists($tableName)
    {
        $db = \XenForo_Application::getDb();

        if (!self::$_tablesList) {
            self::$_tablesList = array_map('strtolower', $db->listTables());
        }
        return in_array(strtolower($tableName), self::$_tablesList);
    }

    /**
     *
     * @param array $description
     * @return string
     */
    public static function getRowPatternFromTableDescription(array $description)
    {
        $db = \XenForo_Application::getDb();

        return '#' . preg_quote(
            $description['DATA_TYPE'] . ($description['LENGTH'] ? '(' . $description['LENGTH'] . ')' : '') .
                 ($description['UNSIGNED'] ? ' UNSIGNED' : '') . ($description['NULLABLE'] ? ' NULL' : ' NOT NULL') .
                 (!is_null($description['DEFAULT']) ? ' DEFAULT ' . $db->quote($description['DEFAULT']) : '') .
                 ($description['IDENTITY'] ? ' AUTO_INCREMENT' : '')) . '#i';
    }
}