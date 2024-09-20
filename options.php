<?php

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use QSOFT\Intranet\Rabbitmq\Admin\Options;
use QSOFT\Intranet\Rabbitmq\Admin\RequestHandler;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('qsoft.rabbitmq')) {
    echo "Не удалось подключить модуль";
}

CUtil::InitJSCore(['window', 'jquery']);

$options = new Options();
$handler = new RequestHandler($options);
$handler->saveOptionsIfNecessary();
$handler->redirectIfNecessary();

$tabs = $options->getTabs();
$tabControl = new CAdminTabControl(
    "tabControl",
    $tabs
);

global $APPLICATION;
Asset::getInstance()->addJs('/local/modules/qsoft.rabbitmq/asset/connection_check.js');
$APPLICATION->SetAdditionalCSS('/local/modules/qsoft.rabbitmq/asset/connection_check.css');


$tabControl->Begin();
?>
    <form action="<?= (Context::getCurrent()->getRequest()->getRequestUri()) ?>" method="post">

        <?php
        foreach ($tabs as $aTab) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($options::MODULE_ID, $aTab["OPTIONS"]);
        }
        $tabControl->Buttons();
        ?>

        <input type="submit" name="apply" value="Сохранить" class="adm-btn-save"/>
        <input type="submit" name="default" value="Вернуть стандартные" />
        <button class="qrmq_check-connection adm-btn" type="button">Проверить соединение</button>

        <span class="qrmq_connection-check-success qrmq_connection-check-result">Соединение успешно установлено</span>
        <span class="qrmq_connection-check-failure qrmq_connection-check-result">
            Не удалось установить соединение: <span class="qrmq_connection-check-message"></span>
        </span>

        <?= bitrix_sessid_post() ?>
    </form>

<?php
$tabControl->End();
