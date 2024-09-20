<?php


use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {
    return;
}

echo CAdminMessage::ShowNote(Loc::getMessage('INSTALL_SUCCESS'));
$link = '<a href="' . '/bitrix/admin/settings.php?lang=ru&mid=qsoft.rabbitmq' .'" target="_blank">Настройки модуля.</a>';
?>

<h1 style="color: red"><?= Loc::getMessage('WARNING') ?></h1>
<p><?= Loc::getMessage('SETTINGS') ?></p>
<p><?= $link ?></p>