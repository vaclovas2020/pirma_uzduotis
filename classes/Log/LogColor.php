<?php


namespace Log;


class LogColor
{
    public const EMERGENCY = "\033[1;35m%s\033[0m";
    public const ALERT = "\033[1;31m%s\033[0m";
    public const CRITICAL = "\033[1;31m%s\033[0m";
    public const ERROR = "\033[1;31m%s\033[0m";
    public const WARNING = "\033[1;33m%s\033[0m";
    public const NOTICE = "\033[1;32m%s\033[0m";
    public const INFO = "\033[1;34m%s\033[0m";
    public const DEBUG = "\033[1;37m%s\033[0m";
}