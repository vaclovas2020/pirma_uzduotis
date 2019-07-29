<?php


namespace Log;

use DateTime;
use InvalidArgumentException;
use SplFileObject;

class Logger implements LoggerInterface
{
    private $fileName;
    private $printToScreen = false;
    private $writeToFile = true;

    public function __construct(string $fileName = 'word_hyphenation.log')
    {
        $this->fileName = $fileName;
    }

    public function clear(): bool
    {
        return @unlink($this->fileName);
    }

    public function setPrintToScreen(bool $printToAScreen)
    {
        $this->printToScreen = $printToAScreen;
    }

    public function setWriteToFile(bool $writeToFile)
    {
        $this->writeToFile = $writeToFile;
    }

    public function emergency(string $message, array $context = []): void
    {
        $message = $this->formatMessage(LogLevel::EMERGENCY, $message, $context);
        $this->writeToLogFile($message);
        $this->printToScreenIfNeeded($message, LogColor::EMERGENCY);
    }

    public function alert(string $message, array $context = []): void
    {
        $message = $this->formatMessage(LogLevel::ALERT, $message, $context);
        $this->writeToLogFile($message);
        $this->printToScreenIfNeeded($message, LogColor::ALERT);
    }

    public function critical(string $message, array $context = []): void
    {
        $message = $this->formatMessage(LogLevel::CRITICAL, $message, $context);
        $this->writeToLogFile($message);
        $this->printToScreenIfNeeded($message, LogColor::CRITICAL);
    }

    public function error(string $message, array $context = []): void
    {
        $message = $this->formatMessage(LogLevel::ERROR, $message, $context);
        $this->writeToLogFile($message);
        $this->printToScreenIfNeeded($message, LogColor::ERROR);
    }

    public function warning(string $message, array $context = []): void
    {
        $message = $this->formatMessage(LogLevel::WARNING, $message, $context);
        $this->writeToLogFile($message);
        $this->printToScreenIfNeeded($message, LogColor::WARNING);
    }

    public function notice(string $message, array $context = []): void
    {
        $message = $this->formatMessage(LogLevel::NOTICE, $message, $context);
        $this->writeToLogFile($message);
        $this->printToScreenIfNeeded($message, LogColor::NOTICE);
    }

    public function info(string $message, array $context = []): void
    {
        $message = $this->formatMessage(LogLevel::INFO, $message, $context);
        $this->writeToLogFile($message);
        $this->printToScreenIfNeeded($message, LogColor::INFO);
    }

    public function debug(string $message, array $context = []): void
    {
        $message = $this->formatMessage(LogLevel::DEBUG, $message, $context);
        $this->writeToLogFile($message);
        $this->printToScreenIfNeeded($message, LogColor::DEBUG);
    }

    public function log(string $level, string $message, array $context = []): void
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
        if ($this->writeToFile) {
            $file = new SplFileObject($this->fileName, 'a');
            $file->fwrite($message);
            $file = null;
        }
    }

    private function printToScreenIfNeeded(string $message, string $color_str): void
    {
        if ($this->printToScreen) {
            echo sprintf($color_str, $message);
        }
    }

    private function formatMessage(string $level, string $message, array $context = [])
    {
        $levelUpperCase = strtoupper($level);
        $dateTimeStr = (new DateTime())->format('Y-m-d H:i:s,u');
        $message = $this->interpolate($message, $context);
        return $dateTimeStr . '[' . $levelUpperCase . ']: ' . $message . PHP_EOL;
    }

    private function interpolate(string $message, array $context = []): string
    {
        foreach ($context as $key => $value) {
            if (!is_array($value) && !is_object($value) || method_exists($value, '__toString')) {
                $message = str_replace('{' . $key . '}', $value, $message);
            } else {
                $message = str_replace('{' . $key . '}', print_r($value, true), $message);
            }
        }
        return $message;
    }
}
