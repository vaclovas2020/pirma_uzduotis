<?php


namespace Exception;


use ErrorException;

class DbQueryBuilderException extends ErrorException
{
    public const NO_ACTION_AND_TABLE_NAME = 'DbQueryBuilder: Please set action and tableName!';
    public const MUST_HAVE_PARAM_LIST = 'DbQueryBuilder: Action %s must have paramList!';
    public const NO_NEED_CONDITION_SENTENCE = 'DbQueryBuilder: Action %s no need condition sentence!';
}