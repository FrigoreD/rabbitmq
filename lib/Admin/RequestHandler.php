<?php

namespace QSOFT\Intranet\Rabbitmq\Admin;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;

class RequestHandler
{
    protected Options $options;

    protected ?string $connectionCheckResult = null;

    protected bool $shouldRedirect = false;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * Метод сохраняет настройки модуля если тип запроса POST и прохождение csrf проверки было успешно
     * Предусмотрено два режима сохранения:
     * a) Как есть
     * б) Установить стандартные настройки
     */
    public function saveOptionsIfNecessary()
    {
        $tabs = $this->options->getTabs();

        $request = Context::getCurrent()->getRequest();
        if ($request->isPost() && check_bitrix_sessid()) {
            foreach ($tabs as $tab) {
                foreach ($tab["OPTIONS"] as $option) {
                    if (!is_array($option)) {
                        continue;
                    }

                    if ($request["apply"]) {
                        $optionValue = $request->getPost($option[0]);
                        Option::set(
                            Options::MODULE_ID,
                            $option[0],
                            is_array($optionValue) ? implode(",", $optionValue) : $optionValue
                        );
                    } elseif ($request["default"]) {
                        Option::set(Options::MODULE_ID, $option[0], $option[2]);
                    }
                }
            }

            $this->shouldRedirect = true;
        }
    }

    public function redirectIfNecessary(): void
    {
        if ($this->shouldRedirect) {
            global $APPLICATION;
            LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . Options::MODULE_ID . "&lang=" . LANG);
        }
    }
}
