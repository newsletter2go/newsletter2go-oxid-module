<?php

ini_set('display_errors', true);

require_once __DIR__ . '/../Nl2go_ResponseHelper.php';

class nl2goApi extends oxUBase
{

    public function getCustomers()
    {
        $this->authenticate();

        $params = array();
        $params['hours'] = $this->getConfig()->getRequestParameter('hours');
        $params['subscribed'] = $this->getConfig()->getRequestParameter('subscribed');
        $params['offset'] = $this->getConfig()->getRequestParameter('offset');
        $params['limit'] = $this->getConfig()->getRequestParameter('limit');
        $params['group'] = $this->getConfig()->getRequestParameter('group');
        $params['fields'] = $this->getConfig()->getRequestParameter('fields', true);
        $params['emails'] = $this->getConfig()->getRequestParameter('emails', true);

        $model = oxNew('nl2gomodel');
        $customers = $model->getCustomers($params);

        $res = Nl2go_ResponseHelper::generateSuccessResponse(array(
            'customers' => $customers['data'],
            'results' => count($customers['data']),
            'total' => $customers['total']));

        $this->sendResponse($res);
    }

    public function getGroups()
    {
        $this->authenticate();

        $model = oxNew('nl2gomodel');
        $groups = $model->getCustomerGroups();

        $res = Nl2go_ResponseHelper::generateSuccessResponse(array('groups' => $groups));
        $this->sendResponse($res);
    }

    public function getFields()
    {
        $this->authenticate();

        $model = oxNew('nl2gomodel');
        $fields = $model->getCustomerFields();

        $res = Nl2go_ResponseHelper::generateSuccessResponse(array('fields' => $fields));
        $this->sendResponse($res);

    }

    public function getAttributes()
    {
        $this->authenticate();

        $model = oxNew('nl2gomodel');
        $attributes = $model->getProductAttributes();

        $res = Nl2go_ResponseHelper::generateSuccessResponse(array('attributes' => $attributes));
        $this->sendResponse($res);

    }

    public function getStoreLanguages()
    {
        $this->authenticate();

        $languages = array();

        foreach (oxRegistry::getLang()->getLanguageArray() as $lang) {
            $languages[$lang->oxid] = array('id' => $lang->oxid,
                'name' => $lang->name,
                'locale' => $lang->abbr,
                'default' => $lang->selected);
        }


        $res = Nl2go_ResponseHelper::generateSuccessResponse(array('languages' => $languages));
        $this->sendResponse($res);


    }

    public function getProductInfo()
    {
        $this->authenticate();

        $params = array();
        $params['lang'] = $this->getConfig()->getRequestParameter('lang');
        $params['id'] = $this->getConfig()->getRequestParameter('id');

        $language = null;

        foreach (oxRegistry::getLang()->getLanguageArray() as $lang) {
            if (strlen($params['lang']) == 0) {
                if ($lang->selected) {
                    $language = $lang->abbr;
                    break;
                }
            }else {
                    if ($lang->abbr ==$params['lang']) {
                        $language = $lang->abbr;
                        break;
                    }

            }
        }

        if (!$params['id']) {

            $res = Nl2go_ResponseHelper::generateErrorResponse('Parameters lang and/or id not found!',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER, 'getProductInfo');
            $this->sendResponse($res);
        }
        if ($language == null) {
            $res = Nl2go_ResponseHelper::generateErrorResponse('Language not found/supported',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER, 'getProductInfo');
            $this->sendResponse($res);
        }

        $params['lang'] = $language;


        $params['attributes'] = $this->getConfig()->getRequestParameter('attributes', true);

        $model = oxNew('nl2gomodel');
        $product = $model->getProductInfo($params);
        if (!$product) {
            $res = Nl2go_ResponseHelper::generateErrorResponse('Product not found',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER, 'getProductInfo');
            $this->sendResponse($res);
        }


        $res = Nl2go_ResponseHelper::generateSuccessResponse(array('product' => $product));
        $this->sendResponse($res);

    }

    public function unsubscribeCustomer()
    {
        $this->authenticate();

        $email = $this->getConfig()->getRequestParameter('email');
        if (!$email) {
            $res = Nl2go_ResponseHelper::generateErrorResponse('Email parameter not found',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER, 'unsubscribeCustomer');
            $this->sendResponse($res);
        }

        $model = oxNew('nl2gomodel');

        if ($model->unsubscribeCustomer($email)) {
            $res = Nl2go_ResponseHelper::generateSuccessResponse();
            $this->sendResponse($res);
        } else {
            $res = Nl2go_ResponseHelper::generateErrorResponse('No customer has been unsubscribed',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER, 'unsubscribeCustomer');
            $this->sendResponse($res);
        }
    }

    public function subscribeCustomer()
    {
        $this->authenticate();

        $email = $this->getConfig()->getRequestParameter('email');
        if (!$email) {
            $res = Nl2go_ResponseHelper::generateErrorResponse('Email parameter not found',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER, 'unsubscribeCustomer');
            $this->sendResponse($res);
        }

        $model = oxNew('nl2gomodel');

        if ($model->subscribeCustomer($email)) {
            $res = Nl2go_ResponseHelper::generateSuccessResponse();
            $this->sendResponse($res);
        } else {
            $res = Nl2go_ResponseHelper::generateErrorResponse('No customer has been subscribed',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER, 'unsubscribeCustomer');
            $this->sendResponse($res);
        }
    }

    public function setBounce()
    {
        $this->authenticate();

        $email = $this->getConfig()->getRequestParameter('email');
        if (!$email) {
            $res = Nl2go_ResponseHelper::generateErrorResponse('Email parameter not found',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER, 'unsubscribeCustomer');
            $this->sendResponse($res);
        }

        $model = oxNew('nl2gomodel');

        if ($model->bounceEmail($email)) {
            $res = Nl2go_ResponseHelper::generateSuccessResponse();
            $this->sendResponse($res);
        } else {
            $res = Nl2go_ResponseHelper::generateErrorResponse('failed to set bounce flag',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER, 'unsubscribeCustomer');
            $this->sendResponse($res);
        }

    }

    public function testConnection()
    {
        $this->authenticate();
        $res = Nl2go_ResponseHelper::generateSuccessResponse();
        $this->sendResponse($res);
    }

    public function getVersion()
    {
        $this->authenticate();
        require_once __DIR__ . '/../metadata.php';
        $res =
            Nl2go_ResponseHelper::generateSuccessResponse(array('version' => str_replace('.', '', $sMetadataVersion)));
        $this->sendResponse($res);

    }

    public function getCustomerCount()
    {
        $this->authenticate();

        $model = oxNew('nl2gomodel');


        $subscribed = $this->getConfig()->getRequestParameter('subscribed');
        $group = $this->getConfig()->getRequestParameter('group');

        if (($count = $model->getCustomerCount($subscribed, $group)) !== false) {
            $res = Nl2go_ResponseHelper::generateSuccessResponse(array('count' => $count));
            $this->sendResponse($res);
        } else {
            $res = Nl2go_ResponseHelper::generateErrorResponse('error occurred while calculate count',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER, 'getCustomerCount');
            $this->sendResponse($res);
        }

    }

    /**
     * returns true, if authentifaction was successful, else send error-message and stop executing program
     * @return bool
     */
    protected function authenticate()
    {
        $res = null;
        $config = $this->getConfig();
        $myUtilsServer = oxRegistry::get("oxUtilsServer");

        $username = $myUtilsServer->getServerVar('PHP_AUTH_USER') ? $myUtilsServer->getServerVar('PHP_AUTH_USER')
            : $this->getConfig()->getRequestParameter('username');
        $apiKey = $myUtilsServer->getServerVar('PHP_AUTH_PW') ? $myUtilsServer->getServerVar('PHP_AUTH_PW')
            : $this->getConfig()->getRequestParameter('apikey');

        if (strlen($username) == 0) {
            $res = Nl2go_ResponseHelper::generateErrorResponse('username is missing',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_CREDENTIALS_MISSING);
        } elseif (strlen($apiKey) == 0) {
            $res = Nl2go_ResponseHelper::generateErrorResponse('apiKey is missing',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_CREDENTIALS_MISSING);
        } elseif ($username != $config->getConfigParam('nl2goUserName') ||
            $apiKey != $config->getConfigParam('nl2goApiKey')
        ) {
            $res = Nl2go_ResponseHelper::generateErrorResponse('credentials are invalid',
                Nl2go_ResponseHelper::ERRNO_PLUGIN_CREDENTIALS_WRONG);
        }
        if ($res != null) {
            $this->sendResponse($res);
        }
        return true;
    }


    protected function sendResponse($data = array())
    {
        oxRegistry::getUtils()->setHeader('Content-Type: application/json;');
        oxRegistry::getUtils()->showMessageAndExit($data);
    }

}
