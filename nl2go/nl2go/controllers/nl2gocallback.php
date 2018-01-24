<?php

ini_set('display_errors', true);

require_once __DIR__ . '/../Nl2go_ResponseHelper.php';

class nl2goCallback extends oxUBase
{

    public function getCallback()
    {
        $config = $this->getConfig();
        $authKey = $this->getConfig()->getRequestParameter('auth_key');
        $accessToken = $this->getConfig()->getRequestParameter('access_token');
        $refreshToken = $this->getConfig()->getRequestParameter('refresh_token');
        $companyId = $this->getConfig()->getRequestParameter('company_id');

        if (isset($authKey)) {
            oxRegistry::getConfig()->saveShopConfVar(
                'str',
                'nl2goAuthKey',
                $authKey,
                $config->getShopId(),
                'module:Newsletter2Go');
        }

        if (isset($accessToken)) {
            oxRegistry::getConfig()->saveShopConfVar(
                'str',
                'nl2goAccessToken',
                $accessToken,
                $config->getShopId(),
                'module:Newsletter2Go'
            );
        }

        if (isset($refreshToken)) {
            oxRegistry::getConfig()->saveShopConfVar(
                'str',
                'nl2goRefreshToken',
                $refreshToken,
                $config->getShopId(),
                'module:Newsletter2Go'
            );
        }

        if (isset($companyId)) {
            oxRegistry::getConfig()->saveShopConfVar(
                'str',
                'nl2goCompanyId',
                $companyId,
                $config->getShopId(),
                'module:Newsletter2Go'
            );
        }

        $data = Nl2go_ResponseHelper::generateSuccessResponse();
        oxRegistry::getUtils()->setHeader('Content-Type: application/json;');
        oxRegistry::getUtils()->showMessageAndExit($data);
    }
}
