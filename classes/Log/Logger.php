<?php


namespace Log;


use DateTime;
use InvalidArgumentException;
use SplFileObject;

class Logger implements LoggerInterface
{
    private $fileName;
    private $printToScreenAlso = false;

    public function __construct(string $fileName = 'word_hyphenation.log')
    {
        $this->fileName = $fileName;
    }

    public function setPrintToScreenAlso(bool $printToAScreenAlso){
        $this->printToScreenAlso = $printToAScreenAlso;
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
        switch ($level) {
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
        $file = null;
        $this->printToScreenIfNeeded($message);
    }

    private function printToScreenIfNeeded(string $message): void{
        if ($this->printToScreenAlso){
            echo "$message\n";
        }
    }

    private function formatMessage(string $level, string $message, array $context = array())
    {
        $levelUpperCase = strtoupper($level);
        $dateTimeStr = (new DateTime())->format('Y-m-d H:i:s,u');
        $message = $this->interpolate($message, $context);
        return "$dateTimeStr [$levelUpperCase]: $message\n";
    }

    private function interpolate(string $message, array $context = array()): string
    {
        foreach ($context as $key => $value) {
            if (!is_array($value) && !is_object($value) || method_exists($value, '__toString')) {
                $message = str_replace('{' . $key . '}', $value, $message);
            }
        }
        return $message;
    }
}
