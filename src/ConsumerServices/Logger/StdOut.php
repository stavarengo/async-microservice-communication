<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\Logger;


use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class StdOut implements LoggerInterface
{

    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $caller = (string)($context['caller'] ?? '');
        if ($caller) {
            $caller = " [$caller]";
        }
        unset($context['caller']);

        echo "[$level]$caller $message\n";

        foreach ($context as $key => $value) {
            echo "   $key: $value\n";
        }
    }
}