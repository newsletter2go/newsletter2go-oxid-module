<?php

/**
 * Class n2goSettings extends module_config.
 */
class n2goSettings extends Module_Config
{

    const N2GO_INTEGRATION_URL = 'https://ui.newsletter2go.com/integrations/connect/OX/';

    /**
     * Override parent render view.
     *
     * @return string
     */
    public function render()
    {
        $return = parent::render();
        $queryParams = array();

        $oConfig = $this->getConfig();
        $queryParams['password'] = $oConfig->getConfigParam('nl2goApiKey');
        $queryParams['username'] = $oConfig->getConfigParam('nl2goUserName');
        $queryParams['url'] = $oConfig->getShopUrl();

        $oLang = oxRegistry::getLang();
        $lang = $oLang->getTplLanguage();
        $queryParams['language'] = $oLang->getLanguageAbbr($lang);

        $oModuleList = oxNew('oxModuleList');
        $versions = $oModuleList->getModuleVersions();
        $queryParams['version'] = str_replace('.', '', $versions['Newsletter2Go']);
        $queryParams['callback'] = $oConfig->getShopUrl() . '?cl=nl2goCallback&fnc=getCallback';

        $this->_aViewData["n2goConnectUrl"] = self::N2GO_INTEGRATION_URL . '?' . http_build_query($queryParams);

        return $return;
    }

}
