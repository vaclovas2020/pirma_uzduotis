<?php


namespace DB;


use RuntimeException;

class DbQueryBuilder
{

    public const EXCEPTION_NO_ACTION_AND_TABLE_NAME = 'DbQueryBuilder: Please set action and tableName!';
    public const EXCEPTION_MUST_HAVE_PARAM_LIST = 'DbQueryBuilder: Action %s must have paramList!';
    public const EXCEPTION_NO_NEED_CONDITION_SENTENCE = 'DbQueryBuilder: Action %s no need condition sentence!';
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
        return $this;
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
        $this->throwIfDataIsMissing();
        $queryStr = "";
        $this->addActionToQueryStr($queryStr);
        $this->addTableNameToQueryStr($queryStr);
        switch ($this->action) {
            case DBQueryAction::INSERT_INTO:
            case DBQueryAction::REPLACE_INTO:
                $this->buildInsertReplace($queryStr);
                break;
            case DBQueryAction::SELECT_FROM:
                $this->buildSelectFrom($queryStr);
                break;
            case DbQueryAction::UPDATE:
                $this->buildUpdate($queryStr);
                break;
            default:
                break;
        }
        $this->addInnerJoinToQueryStr($queryStr);
        $this->addConditionSentenceToQueryStr($queryStr);
        return $queryStr;
    }

    private function addInnerJoinToQueryStr(string &$queryStr): void
    {
        if (!empty($this->innerJoinStr)) {
            $queryStr .= " {$this->innerJoinStr}";
        }
    }

    private function addConditionSentenceToQueryStr(string &$queryStr): void
    {
        if (!empty($this->conditionSentence)) {
            $queryStr .= " {$this->conditionSentence}";
        }
    }

    private function addActionToQueryStr(string &$queryStr): void
    {
        $queryStr .= $this->action;
    }

    private function addTableNameToQueryStr(string &$queryStr): void
    {
        $queryStr .= ' `' . $this->tableName . '`';
    }

    private function buildInsertReplace(string &$queryStr): void
    {
        $queryStr .= '(`' . implode('`,`', $this->paramList) . '`)';
        $queryStr .= ' VALUES(:' . implode(',:', $this->paramList) . ')';
        foreach ($this->paramListValues as $param => $value) {
            $queryStr = str_replace(":$param", $value, $queryStr);
        }
    }

    private function buildSelectFrom(string &$queryStr): void
    {
        if (!empty($this->selectList)) {
            $queryStr = str_replace('*',
                implode(',', $this->selectList),
                $queryStr);
        }
    }

    private function buildUpdate(string &$queryStr): void
    {
        $queryStr .= ' SET ';
        $paramArr = [];
        foreach ($this->paramList as $param) {
            array_push($paramArr, "`$param` = :$param");
        }
        $queryStr .= implode(', ', $paramArr);
    }

    private function throwIfDataIsMissing(): void
    {
        if (empty($this->action) && empty($this->tableName)) {
            throw new RuntimeException(self::EXCEPTION_NO_ACTION_AND_TABLE_NAME);
        }
        if (empty($this->paramList) &&
            ($this->action === DBQueryAction::INSERT_INTO || $this->action === DBQueryAction::REPLACE_INTO ||
                $this->action === DBQueryAction::UPDATE)) {
            throw new RuntimeException(sprintf(self::EXCEPTION_MUST_HAVE_PARAM_LIST, $this->action));
        }
        if (!empty($this->conditionSentence) &&
            ($this->action === DBQueryAction::TRUNCATE_TABLE || $this->action === DBQueryAction::REPLACE_INTO ||
                $this->action === DBQueryAction::INSERT_INTO)) {
            throw new RuntimeException(sprintf(self::EXCEPTION_NO_NEED_CONDITION_SENTENCE, $this->action));
        }
    }
}
