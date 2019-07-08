<?php


namespace Log;


use InvalidArgumentException;
use SplFileObject;

class Logger implements LoggerInterface
{
    private $fileName;

    public function __construct(string $fileName = 'word_hyphenation.log')
    {
        $this->fileName = $fileName;
    }

    public function emergency(string $message, array $context = array()): void
    {
        $message = $this->formatMessage(LogLevel::EMERGENCY, $message, $context);
        $this->writeToLogFile($message);
    }

    public function alert(string $message, array $context = array()): void
    {
        $message = $this->formatMessage(LogLevel::ALERT, $message, $context);
        $this->writeToLogFile($message);
    }

    public function critical(string $message, array $context = array()): void
    {
        $message = $this->formatMessage(LogLevel::CRITICAL, $message, $context);
        $this->writeToLogFile($message);
    }

    public function error(string $message, array $context = array()): void
    {
        $message = $this->formatMessage(LogLevel::ERROR, $message, $context);
        $this->writeToLogFile($message);
    }

    public function warning(string $message, array $context = array()): void
    {
        $message = $this->formatMessage(LogLevel::WARNING, $message, $context);
        $this->writeToLogFile($message);
    }

    public function notice(string $message, array $context = array()): void
    {
        $message = $this->formatMessage(LogLevel::NOTICE, $message, $context);
        $this->writeToLogFile($message);
    }

    public function info(string $message, array $context = array()): void
    {
        $message = $this->formatMessage(LogLevel::INFO, $message, $context);
        $this->writeToLogFile($message);
    }

    public function debug(string $message, array $context = array()): void
    {
        $message = $this->formatMessage(LogLevel::DEBUG, $message, $context);
        $this->writeToLogFile($message);
    }

    public function log(string $level, string $message, array $context = array()): void
    {
        switch($level){
            case LogLevel::EMERGENCY:
                $this->emergency($message, $context);
                break;
            case LogLevel::ALERT:
                $this->alert($message, $context);
                break;
            case LogLevel::CRITICAL:
                $this->critical($message, $context);
                break;
            case LogLevel::ERROR:
                $this->error($message, $context);
                break;
            case LogLevel::WARNING:
                $this->warning($message, $context);
                break;
            case LogLevel::NOTICE:
                $this->notice($message, $context);
                break;
            case LogLevel::INFO:
                $this->info($message, $context);
                break;
            case LogLevel::DEBUG:
                $this->debug($message, $context);
                break;
            default:
                throw new InvalidArgumentException('Bad Log Level');
                break;
        }
    }

    private function writeToLogFile(string $message): void
    {
        $file = new SplFileObject($this->fileName, 'a');
        $file->fwrite($message);
    }

    private function formatMessage(string $level, string $message, array $context = array())
    {
        $levelUpperCase = strtoupper($level);
        return "$levelUpperCase: " . $this->interpolate($message, $context);
    }

    private function interpolate(string $message, array $context = array()): string
    {
        $replace = array();
        foreach ($context as $key => $value) {
            if (!is_array($value) && !is_object($value) || method_exists($value, '__toString')) {
                $replace['{' . $key . '}'] = $value;
            }
        }
        return strstr($message, $replace);
    }
}
