<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

class qsoft_rabbitmq extends CModule
{
    public $MODULE_ID = "qsoft.rabbitmq";
    public $MODULE_NAME;
    public $MODULE_VERSION = "0.1.0";
    public $MODULE_VERSION_DATE = "2020-05-19 12:00:00";
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_GROUP_RIGHTS = "Y";

    public function __construct()
    {
        $this->MODULE_NAME = Loc::getMessage("QSOFT_RABBITMQ_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("QSOFT_RABBITMQ_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("QSOFT_RABBITMQ_MODULE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("QSOFT_RABBITMQ_MODULE_PARTNER_URL");
        $this->MODULE_PATH = $this->getModulePath();
        include_once(dirname(__DIR__) . '/include.php');
    }

    public function DoInstall()
    {
        global $APPLICATION;
        $this->registerModule();

        $APPLICATION->IncludeAdminFile(Loc::getMessage('QSOFT_MODULE_INSTALL_DO'), $this->MODULE_PATH . '/install/step1.php');
    }

    public function DoUninstall()
    {
        \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
        $this->unRegisterModule();
        $this->unRegisterEventHandlers();
    }

    public function registerModule()
    {
        RegisterModule($this->MODULE_ID);
    }

    public function unRegisterModule()
    {
        UnRegisterModule($this->MODULE_ID);
    }

    public function unRegisterEventHandlers()
    {
        $con = Application::getConnection();
        $sqlHelper = $con->getSqlHelper();

        $strSql =
            "DELETE FROM b_module_to_module " .
            "WHERE TO_MODULE_ID='" . $sqlHelper->forSql($this->MODULE_ID) . "'";

        $con->queryExecute($strSql);

        $managedCache = Application::getInstance()->getManagedCache();
        $managedCache->clean('b_module_to_module');
    }

    protected function getModulePath()
    {
        $modulePath = explode('/', __FILE__);
        $modulePath = array_slice(
            $modulePath,
            0,
            array_search($this->MODULE_ID, $modulePath) + 1
        );

        return implode('/', $modulePath);
    }
}
