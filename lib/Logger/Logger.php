<?php

namespace QSOFT\Intranet\Rabbitmq\Logger;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use QSOFT\Intranet\Rabbitmq\Admin\Options;

class Logger
{
    protected static self $instance;

    private \Monolog\Logger $logger;

    /**
     * Logger constructor.
     * @throws \Exception
     */
    protected function __construct()
    {
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%][%level_name%] %message% %context% %extra%\n";

        $formatter = new LineFormatter($output, $dateFormat, false, true);
        $stream = new StreamHandler(static::getFilePath(), self::getLogLevel(), true, 0775);
        $stream->setFormatter($formatter);

        $logger = new \Monolog\Logger('rabbitmq');
        $logger->pushHandler($stream);
        $this->logger = $logger;
    }

    /**
     * Объекты созданные по паттерну одиночка нельзя клонировать
     */
    protected function __clone()
    {
    }

    /**
     * Объекты созданные по паттерну одиночка не могут быть сериализованы и заново инициализированы
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    protected static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    protected static function getLogLevel(): int
    {
        switch (Options::getLogLevel()) {
            case 'error':
                return \Monolog\Logger::ERROR;
            case 'warning':
                return \Monolog\Logger::WARNING;
            default:
                return \Monolog\Logger::INFO;
        }
    }

    /**
     * Метод записывает сообщение с пометкой о средней важности в лог
     * @param string $message
     * @param array $context
     * @return mixed|void
     */
    public static function warning(string $message, array $context = [])
    {
        $instance = static::getInstance();
        $instance->logger->warning($message, $context);
    }

    /**
     * Метод записывает сообщение об ошибке в лог
     * @param string $message
     * @param array $context
     * @return mixed|void
     */
    public static function error(string $message, array $context = [])
    {
        $instance = static::getInstance();
        $instance->logger->error($message, $context);
    }

    /**
     * Метод записывает информационное сообщение в лог
     * @param string $message
     * @param array $context
     * @return mixed|void
     */
    public static function info(string $message, array $context = [])
    {
        $instance = static::getInstance();
        $instance->logger->info($message, $context);
    }

    /**
     * Метод получает путь к файлу для логирования
     * @return string
     * @throws \Exception
     */
    public static function getFilePath(): string
    {
        return static::getPath() . '/' . static::getFileName();
    }

    /**
     * Метод возвращает название файла для лога
     * @return string
     * @throws \Exception
     */
    public static function getFileName(): string
    {
        $now = new \DateTime();
        $nowDate = $now->format('Ymd');

        return 'log_' . $nowDate . '.log';
    }

    public static function getPath(): string
    {
        return Option::get('qsoft.rabbitmq', 'log_dir')
            ?: (Application::getDocumentRoot(). '/logs/qsoft.rabbitmq');
    }
}
