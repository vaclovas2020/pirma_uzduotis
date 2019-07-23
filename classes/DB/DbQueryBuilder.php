<?php


namespace DB;


use RuntimeException;

class DbQueryBuilder
{
    private $action;
    private $tableName;
    private $paramList;
    private $paramListValues;
    private $selectList;
    private $innerJoinStr;
    private $conditionSentence;

    public function __construct()
    {
        $this->paramList = [];
        $this->paramListValues = [];
        $this->selectList = [];
        $this->innerJoinStr = "";
    }

    public function insertInto(string $tableName): DbQueryBuilder
    {
        $this->action = DBQueryAction::INSERT_INTO;
        $this->tableName = $tableName;
        return $this;
    }

    public function replaceInto(string $tableName): DbQueryBuilder
    {
        $this->action = DBQueryAction::REPLACE_INTO;
        $this->tableName = $tableName;
        return $this;
    }

    public function deleteFrom(string $tableName): DbQueryBuilder
    {
        $this->action = DbQueryAction::DELETE_FROM;
        $this->tableName = $tableName;
        return $this;
    }

    public function updateTable(string $tableName): DbQueryBuilder
    {
        $this->action = DBQueryAction::UPDATE;
        $this->tableName = $tableName;
        return $this;
    }

    public function selectFrom(string $tableName): DbQueryBuilder
    {
        $this->action = DBQueryAction::SELECT_FROM;
        $this->tableName = $tableName;
        return $this;
    }

    public function truncateTable(string $tableName): DbQueryBuilder
    {
        $this->action = DBQueryAction::TRUNCATE_TABLE;
        $this->tableName = $tableName;
        return $this;
    }

    public function addInnerJoin(string $joinTable, string $leftField, string $rightField): DbQueryBuilder
    {
        $this->innerJoinStr .= " INNER JOIN `$joinTable` ON $leftField = $rightField";
        return $this;
    }

    public function addParam(string $paramName): DbQueryBuilder
    {
        array_push($this->paramList, $paramName);
        return $this;
    }

    public function addParamValue(string $paramName, string $paramValue): DbQueryBuilder
    {
        array_push($this->paramList, $paramName);
        $this->paramListValues[$paramName] = $paramValue;
    }

    public function addSelectField(string $fieldName, string $beforeAndAfter = '`'): DbQueryBuilder
    {
        array_push($this->selectList, (!empty($beforeAndAfter)) ?
            "$beforeAndAfter$fieldName$beforeAndAfter" : $fieldName);
        return $this;
    }

    public function setConditionSentence(string $conditionSentence): DbQueryBuilder
    {
        $this->conditionSentence = $conditionSentence;
        return $this;
    }

    public function build(): string
    {
        if (empty($this->action) && empty($this->tableName)) {
            throw new RuntimeException('DbQueryBuilder: Please set action and tableName!');
        }
        if (empty($this->paramList) &&
            ($this->action === DBQueryAction::INSERT_INTO || $this->action === DBQueryAction::REPLACE_INTO ||
                $this->action === DBQueryAction::UPDATE)) {
            throw new RuntimeException("DbQueryBuilder: Action {$this->action} must have paramList!");
        }
        if (!empty($this->conditionSentence) &&
            ($this->action === DBQueryAction::TRUNCATE_TABLE || $this->action === DBQueryAction::REPLACE_INTO ||
                $this->action === DBQueryAction::INSERT_INTO)) {
            throw new RuntimeException("DbQueryBuilder: Action {$this->action} no need condition sentence!");
        }
        $queryStr = $this->action;
        $queryStr .= ' `' . $this->tableName . '`';
        switch ($this->action) {
            case DBQueryAction::INSERT_INTO:
            case DBQueryAction::REPLACE_INTO:
                $queryStr .= '(`' . implode('`,`', $this->paramList) . '`)';
                $queryStr .= ' VALUES(:' . implode(',:', $this->paramList) . ')';
                foreach ($this->paramListValues as $param => $value) {
                    $queryStr = str_replace(":$param", $value, $queryStr);
                }
                break;
            case DBQueryAction::SELECT_FROM:
                if (!empty($this->selectList)) {
                    $queryStr = str_replace('*',
                        implode(',', $this->selectList),
                        $queryStr);
                }
                break;
            case DbQueryAction::UPDATE:
                $queryStr .= ' SET ';
                $paramArr = [];
                foreach ($this->paramList as $param) {
                    array_push($paramArr, "`$param` = :$param");
                }
                $queryStr .= implode(', ', $paramArr);
                break;
            default:
                break;
        }
        if (!empty($this->innerJoinStr)) {
            $queryStr .= " {$this->innerJoinStr}";
        }
        if (!empty($this->conditionSentence)) {
            $queryStr .= " {$this->conditionSentence}";
        }
        return $queryStr;
    }
}
