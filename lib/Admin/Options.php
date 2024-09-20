<?php

namespace QSOFT\Intranet\Rabbitmq\Admin;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use QSOFT\Intranet\Rabbitmq\Logger\Logger;

class Options
{
    public const MODULE_ID = 'qsoft.rabbitmq';

    public function getTabs(): array
    {
        return [
            [
                'DIV' => 'general',
                'TAB' => Loc::getMessage('QSOFT_RMQ_OPTIONS_GENERAL_TAB'),
                'TITLE' => Loc::getMessage('QSOFT_RMQ_OPTIONS_GENERAL_TITLE'),
                'OPTIONS' => [
                    Loc::getMessage('QSOFT_RMQ_OPTIONS_GENERAL_SECTION'),
                    [
                        'rabbitmq_host',
                        Loc::getMessage('QSOFT_RMQ_OPTION_HOST'),
                        "",
                        ['text', 80],
                    ],
                    [
                        'rabbitmq_port',
                        Loc::getMessage('QSOFT_RMQ_OPTION_PORT'),
                        "",
                        ['text'],
                    ],
                    [
                        'rabbitmq_vhost',
                        Loc::getMessage('QSOFT_RMQ_OPTION_VHOST'),
                        "/",
                        ['text'],
                    ],
                    [
                        'rabbitmq_user',
                        Loc::getMessage('QSOFT_RMQ_OPTION_USER'),
                        "",
                        ['text'],
                    ],
                    [
                        'rabbitmq_password',
                        Loc::getMessage('QSOFT_RMQ_OPTION_PASSWORD'),
                        "",
                        ['password'],
                    ],
                    Loc::getMessage('QSOFT_RMQ_OPTIONS_DETAILS_SECTION'),
                    [
                        'rabbitmq_input_queues',
                        Loc::getMessage('QSOFT_RMQ_OPTION_INPUT_QUEUES'),
                        "",
                        ['text'],
                    ],
                    [
                        'rabbitmq_output_queue',
                        Loc::getMessage('QSOFT_RMQ_OPTION_OUTPUT_QUEUE'),
                        "",
                        ['text'],
                    ],
                    [
                        'listener_lifetime_s',
                        Loc::getMessage('QSOFT_RMQ_OPTION_LISTENER_LIFETIME_S'),
                        55,
                        ['text', 10],
                    ],
                    [
                        'channel_timeout_s',
                        Loc::getMessage('QSOFT_RMQ_OPTION_CHANNEL_TIMEOUT_S'),
                        5,
                        ['text', 10],
                    ],
                    [
                        'requeue_on_failure',
                        Loc::getMessage('QSOFT_RMQ_OPTION_REQUEUE_ON_FAILURE'),
                        'N',
                        ['checkbox'],
                    ],
                ],
            ],
            [
                'DIV' => 'logging',
                'TAB' => Loc::getMessage('QSOFT_RMQ_OPTIONS_LOGGING_TAB'),
                'TITLE' => Loc::getMessage('QSOFT_RMQ_OPTIONS_LOGGING_TITLE'),
                'OPTIONS' => [
                    Loc::getMessage('QSOFT_RMQ_OPTIONS_LOGGING_SECTION'),
                    [
                        'log_level',
                        Loc::getMessage('QSOFT_RMQ_OPTION_LOG_LEVEL'),
                        "info",
                        ['selectbox', [
                            'info' => '[info] ' . Loc::getMessage('QSOFT_RMQ_OPTION_LOG_LEVEL_INFO'),
                            'warning' => '[warning] ' . Loc::getMessage('QSOFT_RMQ_OPTION_LOG_LEVEL_WARNING'),
                            'error' => '[error] ' . Loc::getMessage('QSOFT_RMQ_OPTION_LOG_LEVEL_ERROR'),
                        ]],
                    ],
                    [
                        'log_dir',
                        Loc::getMessage("QSOFT_RMQ_OPTION_LOG_DIR"),
                        Logger::getPath(),
                        ['text', 50],
                    ]
                ],
            ],
        ];
    }

    public static function getConnectionParameters(): array
    {
        return [
            'host' => self::get("rabbitmq_host"),
            'port' => self::get("rabbitmq_port"),
            'vhost' => self::get("rabbitmq_vhost"),
            'user' => self::get("rabbitmq_user"),
            'password' => self::get("rabbitmq_password"),
        ];
    }

    public static function get(string $code)
    {
        $tabs = (new self())->getTabs();

        foreach ($tabs as $tab) {
            foreach ($tab["OPTIONS"] as $option) {
                if (!is_array($option)) {
                    continue;
                }

                if ($option[0] === $code) {
                    return Option::get(
                        self::MODULE_ID,
                        $code,
                        $option[2]
                    );
                }
            }
        }

        throw new \InvalidArgumentException("Настройка $code не найдена");
    }

    public static function getOutputQueue(): string
    {
        return self::get('rabbitmq_output_queue');
    }

    /**
     * @return string[]
     */
    public static function getInputQueues(): array
    {
        return array_filter(array_map(
            function ($item) {
                return trim($item);
            },
            explode(' ', self::get('rabbitmq_input_queues'))
        ));
    }

    public static function getChannelTimeoutS(): int
    {
        return (int)self::get('channel_timeout_s');
    }

    public static function getListenerLifetimeS(): int
    {
        return (int)self::get('listener_lifetime_s');
    }

    public static function getLogLevel(): string
    {
        return self::get('log_level');
    }

    public static function shouldRequeueOnFailure(): bool
    {
        return self::get('requeue_on_failure') === 'Y';
    }
}
