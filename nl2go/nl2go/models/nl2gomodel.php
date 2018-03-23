<?php

class nl2goModel extends oxUBase
{

    public function getCustomerGroups()
    {
        $lang = oxRegistry::getLang()->getLanguageAbbr();

        $sViewName = 'oxv_oxgroups_' . $lang;
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $rs = $oDb->execute('SELECT * FROM ' . $sViewName);
        $result = array();

        while (!$rs->EOF) {
            //$rs2 = $oDb->execute("SELECT COUNT(*) AS total FROM oxobject2group WHERE OXOBJECTID NOT LIKE 'oxid%' AND OXGROUPSID='{$rs->fields['OXID']}' ;");
            $result[] = array(
                'id' => $rs->fields['OXID'],
                'name' =>$rs->fields['OXTITLE'],
                'description' => null
                //'count' => $rs2->fields['total'],
            );
            $rs->moveNext();
        }

        return $result;
    }

    public function getCustomerFields()
    {

        $descriptions = array(
            'COLUMN_NAME' => 'COLUMN_COMMENT',
            'OXID' => 'User id',
            'OXACTIVE' => 'Is active',
            'OXRIGHTS' => 'User rights: user, malladmin',
            'OXSHOPID' => 'Shop id (oxshops)',
            'OXUSERNAME' => 'Username',
            'OXPASSWORD' => 'Hashed password',
            'OXPASSSALT' => 'Password salt',
            'OXCUSTNR' => 'Customer number',
            'OXUSTID' => 'VAT ID No.',
            'OXCOMPANY' => 'Company',
            'OXFNAME' => 'First name',
            'OXLNAME' => 'Last name',
            'OXSTREET' => 'Street',
            'OXSTREETNR' => 'House number',
            'OXADDINFO' => 'Additional info',
            'OXCITY' => 'City',
            'OXCOUNTRYID' => 'Country id (oxcountry)',
            'OXSTATEID' => 'State id (oxstates)',
            'OXZIP' => 'ZIP code',
            'OXFON' => 'Phone number',
            'OXFAX' => 'Fax number',
            'OXSAL' => 'User title (Mr/Mrs)',
            'OXBONI' => 'Credit points',
            'OXCREATE' => 'Creation time',
            'OXREGISTER' => 'Registration time',
            'OXPRIVFON' => 'Personal phone number',
            'OXMOBFON' => 'Mobile phone number',
            'OXBIRTHDATE' => 'Birthday date',
            'OXURL' => 'Url',
            'OXUPDATEKEY' => 'Update key',
            'OXUPDATEEXP' => 'Update key expiration time',
            'OXPOINTS' => 'User points (for registration, invitation, etc)',
            'OXFBID' => 'Facebook id (used for openid login)',
            'OXTIMESTAMP' => 'Timestamp'

        );

        //get oxuser-fields dynamically
        $oxUserFields = array();

        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $metaFields = $oDb->metaColumns('oxuser');


        foreach ($metaFields as $field) {
            switch ($field->type) {
                case 'int':
                case 'tinyint':
                    $type = 'Integer';
                    break;
                case 'double':
                case 'float':
                    $type = 'Float';
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    $type = 'Date';
                    break;
                default:
                    $type = 'String';
            }
            $oxUserFields[] = array('id' => 'oxuser.' . $field->name,
                'name' => isset($descriptions[$field->name]) ? $descriptions[$field->name] : $field->name,
                'description' => $field->name,
                'type' => $type);

        }

        //removed calculated fields in fact of performance problems
        $additionalFields = array(
            /* array(
                 'id' => 'totalorders',
                 'name' => 'Total Orders',
                 'description' => 'Count of total orders by customer',
                 'type' => 'Integer',
             ),
             array(
                 'id' => 'totalrevenue',
                 'name' => 'Total Revenue',
                 'description' => 'Total revenue of customer',
                 'type' => 'Float',
             ),
             array(
                 'id' => 'averagecartsize',
                 'name' => 'Average cart size',
                 'description' => 'Average cart size',
                 'type' => 'Float',
             ),
             array(
                 'id' => 'lastorder',
                 'name' => 'Last order',
                 'description' => 'Last order ISO - Date',
                 'type' => 'Date',
             ),*/

            array(
                'id' => 'oxcountry.OXTITLE',
                'name' => 'Country name',
                'description' => 'Country name',
                'type' => 'String',
            ),
            array(
                'id' => 'oxstates.OXTITLE',
                'name' => 'State name',
                'description' => 'State name',
                'type' => 'String',
            ),
            array(
                'id' => 'oxnewssubscribed.OXDBOPTIN',
                'name' => 'Opt in',
                'description' => 'Subscription status: 0 - not subscribed, 1 - subscribed, 2 - not confirmed',
                'type' => 'Integer',
            ),
            array(
                'id' => 'oxnewssubscribed.OXEMAILFAILED',
                'name' => 'Email bounce',
                'description' => 'Subscription email sending status',
                'type' => 'Integer',
            ),
            array(
                'id' => 'oxnewssubscribed.OXSUBSCRIBED',
                'name' => 'Subscription date',
                'description' => 'Subscription date',
                'type' => 'Date',
            ),
            array(
                'id' => 'oxnewssubscribed.OXUNSUBSCRIBED',
                'name' => 'Unsubscription date',
                'description' => 'Unsubscription date',
                'type' => 'Date',
            ),
        );

        return array_merge($oxUserFields, $additionalFields);
    }

    public function getCustomers($params = array())
    {

        $start = microtime();
        $result = array();
        $queryWhere = '';
        $queryLimit = '';
        $conditions = array();

        if ($params['fields']) {
            $fieldIds = json_decode($params['fields'], true);
        } else {
            $fields = $this->getCustomerFields();
            $fieldIds = array();
            foreach ($fields as $field) {
                $fieldIds[] = $field['id'];
            }

            unset($fields);
        }


        $querySelect = $this->createCustomersSelect($fieldIds);
        $queryFrom = ' FROM oxuser
                        LEFT JOIN oxnewssubscribed ON oxnewssubscribed.OXUSERID = oxuser.OXID
                        LEFT JOIN oxstates ON oxuser.OXSTATEID = oxstates.OXID
                        LEFT JOIN oxcountry ON oxuser.OXCOUNTRYID = oxcountry.OXID ';

        //removde groupBy fields
        /* $queryOrderFields = " LEFT JOIN (
                                 SELECT  OXUSERID, count(oxorder.OXID) as 'totalorders',
                                 sum(oxorder.OXTOTALORDERSUM) as 'totalrevenue',
                                 avg(oxorder.OXTOTALORDERSUM) as 'averagecartsize',
                                 max(oxorder.OXORDERDATE) as 'lastorder' FROM oxorder GROUP BY oxorder.OXUSERID) g_oxorder
                                   ON g_oxorder.OXUSERID = oxuser.OXID ";*/

        if ($params['group']) {
            $conditions[] =
                " oxuser.OXID IN (SELECT oxobject2group.OXOBJECTID FROM oxobject2group WHERE oxobject2group.OXGROUPSID = '" .
                $params['group'] . "') ";
        }

        if ($params['subscribed']) {
            $conditions[] = ' oxnewssubscribed.OXDBOPTIN = 1 ';
        }

        if ($params['emails']) {
            $emails = json_decode($params['emails'], true);
            $conditions[] = " oxuser.OXUSERNAME IN ('" . implode("','", $emails) . "') ";
        }

        if ($params['hours']) {
            $ts = date('Y - m - d H:i:s', time() - 3600 * $params['hours']);
            $conditions[] = " oxuser.OXTIMESTAMP >= '$ts' ";
        }

        if (count($conditions)) {
            $queryWhere = ' WHERE ' . implode(' AND ', $conditions);
        }

        if ($params['limit']) {
            $limit = $params['limit'];
            $offset = $params['offset'] ? $params['offset'] : 0;
            $queryLimit = "LIMIT $offset, $limit";
        }

        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $rsCustomers = $oDb->execute($querySelect . $queryFrom . $queryWhere . $queryLimit);


        $rsTotal = $oDb->execute('SELECT COUNT(*) AS total ' . $queryFrom . $queryWhere);


        $hasId = array_search('oxuser.OXID', $fieldIds) === false;

        ///$orderFields = $this->getOrderSelectFields($fieldIds);
        //$queryOrder = 'SELECT '.implode(', ', $orderFields).' FROM oxorder WHERE OXUSERID = ';


        while (!$rsCustomers->EOF) {
            $customer = $rsCustomers->fields;

            /*if (!empty($orderFields)) {
                $rsOrder = $oDb->execute($queryOrder."'".$customer['oxuser.OXID']."'");
                $customer = array_merge($customer, $rsOrder->fields);
            }

            if ($hasId) {
                unset($customer['oxuser.OXID']);
            }*/

            $result[] = $customer;

            $rsCustomers->moveNext();
        }

        return array('data' => $result, 'total' => $rsTotal->fields['total']);
    }

    public function getCustomerCount($subscribed, $group)
    {

        $start = microtime();
        $result = array();
        $queryWhere = '';
        $queryLimit = '';
        $conditions = array();

        $sql =
            'SELECT COUNT(oxuser.OXID) as count FROM oxuser LEFT JOIN oxnewssubscribed ON oxnewssubscribed.OXUSERID = oxuser.OXID';
        if ($subscribed) {
            $conditions[] = ' oxnewssubscribed.OXDBOPTIN = 1 ';
        }
        if (strlen($group) > 0) {
            $conditions[] =
                " oxuser.OXID IN (SELECT oxobject2group.OXOBJECTID FROM oxobject2group WHERE oxobject2group.OXGROUPSID = '" .
                $group . "') ";
        }

        if (count($conditions)) {
            $queryWhere = ' WHERE ' . implode(' AND ', $conditions);
        }

        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $rsCustomers = $oDb->execute($sql . $queryWhere);

        return $rsCustomers->fields['count'];


    }

    public function unsubscribeCustomer($email)
    {
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $oDb->execute("UPDATE oxnewssubscribed SET OXDBOPTIN = 0 WHERE OXEMAIL=?", array($email));

        return $oDb->Affected_Rows();
    }

    public function subscribeCustomer($email)
    {
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $oDb->execute("UPDATE oxnewssubscribed SET OXDBOPTIN = 1 WHERE OXEMAIL=?", array($email));

        return $oDb->Affected_Rows();
    }

    public function bounceEmail($email)
    {
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $oDb->execute("UPDATE oxnewssubscribed SET OXEMAILFAILED = 1 WHERE OXEMAIL=?", array($email));

        return $oDb->Affected_Rows();
    }

    public function getProductInfo($params)
    {
        $result = array();

        if ($params['attributes']) {
            $attributeIds = json_decode($params['attributes'], true);
        } else {
            $attributes = $this->getProductAttributes($params['lang']);
            $attributeIds = array();
            foreach ($attributes as $attribute) {
                $attributeIds[] = $attribute['id'];
            }

            unset($attributes);
        }

        $articleView = 'oxv_oxarticles_' . $params['lang'];
        $shopView = 'oxv_oxshops_' . $params['lang'];
        $vendorView = 'oxv_oxvendor_' . $params['lang'];
        $manufView = 'oxv_oxmanufacturers_' . $params['lang'];
        $artextView = 'oxv_oxartextends_' . $params['lang'];

        $querySelect = $this->createProductSelect($attributeIds, $params['lang']);
        $queryWhere = " WHERE $articleView.OXID = '" .
            $params['id'] . "' OR $articleView.OXARTNUM = '" .
            $params['id'] . "' ";
        $queryFrom = " FROM $articleView
                            LEFT JOIN $shopView ON $shopView.OXID = $articleView.OXSHOPID
                            LEFT JOIN $vendorView ON $vendorView.OXID = $articleView.OXVENDORID
                            LEFT JOIN $manufView ON $manufView.OXID = $articleView.OXMANUFACTURERID
                            LEFT JOIN $artextView ON $artextView.OXID = $articleView.OXID ";


        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $rsProduct = $oDb->execute($querySelect . $queryFrom . $queryWhere);
        $result = $rsProduct->fields;
        if (!$result) {
            return false;
        }


        $article = oxNew('oxarticle');
        $article->load($result['oxarticles.OXID']);
        if (!in_array('oxarticles.OXID', $attributeIds, true)) {
            unset($result['oxarticles.OXID']);
        }

        if (in_array('images', $attributeIds, true)) {
            $pictureLimit = oxRegistry::getConfig()->getConfigParam('iPicCount');
            $result['images'] = array();
            foreach (range(1, $pictureLimit) as $index) {
                if (($imageUrl = $article->getPictureUrl($index)) && strpos($imageUrl, 'nopic.jpg') === false) {
                    $result['images'][$index] = $imageUrl;
                }
            }
        }
        $link = $article->getLink();
        $host = parse_url($link,PHP_URL_HOST).'/';
        if (in_array('link', $attributeIds, true)) {
            //$linkHelp = oxNew('oxseoencoderarticle');

            $result['link'] =  ltrim(substr($link, strpos($link,$host) + strlen($host)), '/');

            if(strpos($result['link'], '?force_sid') !== false){
                $result['link'] = substr($result['link'], 0 , strpos($result['link'], '?force_sid'));
            }
        }

        if (in_array('vat', $attributeIds, true)) {
            $result['vat'] = $this->getConfig()->getConfigParam('dDefaultVAT') * 0.01;
        }

        if (in_array('url', $attributeIds, true)) {
            $result['url'] =  rtrim(substr($link,0, strpos($link, $host) + strlen($host)), '/').'/';
        }

        $vat =(isset($result['oxarticles.OXVAT']) && strlen($result['oxarticles.OXVAT']) > 0 ? $result['oxarticles.OXVAT'] :$this->getConfig()->getConfigParam('dDefaultVAT')) *0.01;


        if ($vat> 0 && isset($result['oldPriceNet'])) {
            $result['oldPriceNet'] = number_format($result['oldPriceNet'] / (1 + $vat), 2);
        }

        if ($vat > 0 && isset($result['newPriceNet'])) {
            $result['newPriceNet'] = number_format($result['newPriceNet'] / (1 + $vat), 2);
        }


        return $result;
    }

    public function getProductAttributes($lang = null)
    {

        if($lang == null){
            $lang = oxRegistry::getLang()->getLanguageAbbr();
        }


        $descriptions = array(
            'OXID' => 'Product Id.',
            'OXSHOPID' => 'Shop id(oxshops)',
            'OXVENDORID' => 'Vendor id(oxvendor)',
            'OXREMINDAMOUNT' => 'Defines the amount, below which notification email will be sent if oxremindactive is set to 1',
            'OXREMINDACTIVE' => 'Enables sending of notification email when oxstock field value falls below oxremindamount value',
            'OXFREESHIPPING' => 'Free shipping(variants inherits parent setting',
            'OXNONMATERIAL' => 'Intangible article, free shipping is used(variants inherits parent setting)',
            'OXSOLDAMOUNT' => 'Amount of sold articles including variants(used only for parent articles)',
            'OXSORT' => 'Sorting',
            'OXSUBCLASS' => 'Subclass',
            'OXFOLDER' => 'Folder',
            'OXBUNDLEID' => 'Bundled article id',
            'OXVARMAXPRICE' => '>Highest price in active article variants',
            'OXVARMINPRICE' => 'Lowest price in active article variants',
            'OXVARSELECT' => 'Variant article selections(separated by | )',
            'OXVARCOUNT' => 'Total number of variants that article has(active and inactive)',
            'OXVARSTOCK' => 'Sum of active article variants stock quantity',
            'OXVARNAME' => 'Name of variants selection lists(different lists are separated by | )',
            'OXISCONFIGURABLE' => 'Can article be customized',
            'OXISSEARCH' => 'Should article be shown in search',
            'OXQUESTIONEMAIL' => 'E - mail for question',
            'OXSEARCHKEYS' => 'Search terms',
            'OXFILE' => 'File, shown in article media list',
            'OXHEIGHT' => 'Article dimensions: Height',
            'OXWIDTH' => 'Article dimensions: Width',
            'OXLENGTH' => 'Article dimensions: Length',
            'OXTIMESTAMP' => 'Timestamp of last modification',
            'OXINSERT' => 'Insert time',
            'OXDELIVERY' => 'Date, when the product will be available again if it is sold out',
            'OXSTOCKTEXT' => 'Message, which is shown if the article is in stock',
            'OXSTOCKFLAG' => 'Delivery Status: 1 - Standard, 2 - If out of Stock, offline, 3 - If out of Stock, not orderable, 4 - External Storehouse',
            'OXSTOCK' => 'Article quantity in stock',
            'OXWEIGHT' => 'Weight(kg)',
            'OXVAT' => 'Value added tax.If specified, used in all calculations instead of global vat',
            'OXURLIMG' => 'External URL image',
            'OXURLDESC' => 'Text for external URL',
            'OXPARENTID' => 'Parent article id',
            'OXACTIVE' => 'Active',
            'OXACTIVEFROM' => 'Active from specified date',
            'OXACTIVETO' => 'Active to specified date',
            'OXARTNUM' => 'Article number',
            'OXEAN' => 'International Article Number(EAN)',
            'OXDISTEAN' => 'Manufacture International Article Number(Man.EAN)',
            'OXMPN' => 'Manufacture Part Number(MPN)',
            'OXTITLE' => 'Title',
            'OXSHORTDESC' => 'Short description',
            'OXPRICE' => 'Article Price',
            'OXBLFIXEDPRICE' => 'No Promotions(Price Alert)',
            'OXPRICEA' => 'Price A',
            'OXPRICEB' => 'Price B',
            'OXPRICEC' => 'Price C',
            'OXBPRICE' => 'Purchase Price',
            'OXTPRICE' => 'Recommended Retail Price(RRP)',
            'OXUNITNAME' => 'Unit name(kg, g, l, cm etc), used in setting price per quantity unit calculation',
            'OXUNITQUANTITY' => 'Article quantity, used in setting price per quantity unit calculation',
            'OXEXTURL' => 'External URL to other information about the article',
            'OXURLDESC' => 'Text for external URL ',
            'OXRATING' => 'Article rating',
            'OXMINDELTIME' => 'Minimal delivery time(unit is set in oxdeltimeunit)',
            'OXMAXDELTIME' => 'Maximum delivery time(unit is set in oxdeltimeunit)',
            'OXDELTIMEUNIT' => 'Delivery time unit: DAY, WEEK, MONTH',
            'OXUPDATEPRICE' => 'If not 0, oxprice will be updated to this value on oxupdatepricetime date',
            'OXUPDATEPRICEA' => 'If not 0, oxprice will be updated to this value on oxupdatepricetime date',
            'OXUPDATEPRICEB' => 'If not 0, oxprice will be updated to this value on oxupdatepricetime date',
            'OXUPDATEPRICEC' => 'If not 0, oxprice will be updated to this value on oxupdatepricetime date',
            'OXUPDATEPRICETIME' => 'Date, when oxprice[a, b, c] should be updated to oxupdateprice[a, b, c] values',
            'OXISDOWNLOADABLE' => 'Enable download of files for this product'
        );


        //get oxuser-fields dynamically
        $oxProductFields = array();

        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $metaFields = $oDb->metaColumns('oxv_oxarticles_'.$lang);


        foreach ($metaFields as $field) {
            switch ($field->type) {
                case 'int':
                case 'tinyint':
                    $type = 'Integer';
                    break;
                case 'double':
                case 'float':
                    $type = 'Float';
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    $type = 'Date';
                    break;
                default:
                    $type = 'String';
            }
            $oxProductFields[] = array('id' => 'oxarticles.' . $field->name,
                'name' => $field->name,
                'description' => isset($descriptions[$field->name]) ? $descriptions[$field->name] : $field->name,
                'type' => $type);

        }

        $addiditionalFields = array(
            array(
                'id' => 'images',
                'name' => 'Item images',
                'description' => '',
                'type' => 'Array',
            ),
            array(
                'id' => 'link',
                'name' => 'Link to the item',
                'description' => 'Link to the item',
                'type' => 'String',
            ),
            array(
                'id' => 'url',
                'name' => 'Shop URL',
                'description' => 'Shop URL',
                'type' => 'String',
            ),
            array(
                'id' => 'vat',
                'name' => 'Default VAT',
                'description' => 'Default shop VAT',
                'type' => 'Float',
            ),

            array(
                'id' => 'oxshops.OXNAME',
                'name' => 'Shop name',
                'description' => 'Shop name',
                'type' => 'String',
            ),
            array(
                'id' => 'oxartextends.OXLONGDESC',
                'name' => 'Long description',
                'description' => 'Long description',
                'type' => 'String',
            ),
            array(
                'id' => 'oxartextends.OXTAGS',
                'name' => 'Tags',
                'description' => 'Tags',
                'type' => 'String',
            ),

            array(
                'id' => 'oxvendor.OXTITLE',
                'name' => 'Vendor name',
                'description' => 'Vendor name',
                'type' => 'String',
            ),

            array(
                'id' => 'oxmanufacturers.OXTITLE',
                'name' => 'Manufacturer name',
                'description' => 'Manufacturer name',
                'type' => 'String',
            )
        );

        return array_merge($oxProductFields, $addiditionalFields);
    }

    private function createCustomersSelect($fieldIds)
    {
        $select = "SELECT ";
        foreach ($fieldIds as $field) {
            switch ($field) {
                case 'totalorders':
                case 'totalrevenue':
                case 'averagecartsize':
                case 'lastorder':
                    break;
                default:
                    $select .= $field . " as '" . $field . "', ";
                    break;
            }
        }

        return substr($select, 0, -2);
    }

    private function getOrderSelectFields($fieldIds)
    {
        $result = array();
        foreach ($fieldIds as $field) {
            switch ($field) {
                case 'totalorders':
                    $result[] = "count(oxorder.OXID) as '" . $field . "'";
                    break;
                case 'totalrevenue':
                    $result[] = "sum(oxorder.OXTOTALORDERSUM) as '" . $field . "'";
                    break;
                case 'averagecartsize':
                    $result[] = "avg(oxorder.OXTOTALORDERSUM) as '" . $field . "'";
                    break;
                case 'lastorder':
                    $result[] = "max(oxorder.OXORDERDATE) as '" . $field . "'";
                    break;
            }
        }

        return $result;
    }

    private function createProductSelect($attributeIds, $lang)
    {
        $select = "SELECT oxv_oxarticles_$lang.OXID AS 'oxarticles.OXID', ";
        foreach ($attributeIds as $attribute) {
            switch ($attribute) {
                case 'images':
                case 'link';
                case 'vat':
                case 'url':
                case 'oxarticles.OXID':
                    break;
                default:
                    $strings = explode('.', $attribute);
                    $select .= "oxv_$strings[0]_$lang.$strings[1] AS '$attribute', ";
                    break;
            }
        }

        return substr($select, 0, -2);
    }

}
