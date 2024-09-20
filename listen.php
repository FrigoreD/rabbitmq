<?php

// phpcs:disable PSR1.Files.SideEffects

use QSOFT\Intranet\Rabbitmq\Admin\Options;
use QSOFT\Intranet\Rabbitmq\Amqp\SingleChannelStore;
use QSOFT\Intranet\Rabbitmq\Consumers\TimedMultiqueueConsumer;

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__DIR__, 3));

const NO_KEEP_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
const BX_NO_ACCELERATOR_RESET = true;
const CHK_EVENT = true;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
@ignore_user_abort(true);

require('include.php');

$isCli = empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0;
if ($isCli) {
    try {
        (new TimedMultiqueueConsumer(
            SingleChannelStore::get(),
            Options::getInputQueues()
        ))
            ->setTimeLimit(Options::getListenerLifetimeS())
            ->declareQueues()
            ->run();



    } catch (\Throwable $e) {
        fprintf(STDERR, '%s', "Ошибка при выполнении консьюмера: " . $e->getMessage());
    }
}

CMain::FinalActions();
