<?php


namespace Log;


class LogColor
{
    public const EMERGENCY = "\033[35m%s\033[0m";
    public const ALERT = "\033[31m%s\033[0m";
    public const CRITICAL = "\033[31m%s\033[0m";
    public const ERROR = "\033[31m%s\033[0m";
    public const WARNING = "\033[33m%s\033[0m";
    public const NOTICE = "\033[32m%s\033[0m";
    public const INFO = "\033[34m%s\033[0m";
    public const DEBUG = "\033[37m%s\033[0m";
}