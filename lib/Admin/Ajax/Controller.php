<?php

namespace QSOFT\Intranet\Rabbitmq\Admin\Ajax;

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use QSOFT\Intranet\Rabbitmq\Amqp\SingleChannelStore;

class Controller extends \Bitrix\Main\Engine\Controller
{
    public function configureActions(): array
    {
        return [
            'checkConnection' => [
                'prefilters' => [
                    new Authentication(),
                    new HttpMethod([HttpMethod::METHOD_POST]),
                    new Csrf(),
                ],
            ],
        ];
    }

    public function checkConnectionAction()
    {
        try {
            SingleChannelStore::get();

            return [
                'success' => true,
                'message' => null
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
