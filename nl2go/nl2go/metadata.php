<?php
/**
 * Metadata version
 * @created 21.04.2015
 */

$sMetadataVersion = '4.0.00';


/**
 * Module information
 */
$aModule = array(
    'id'           => 'Newsletter2Go',
    'title'        => 'Newsletter2Go',
    'description'  => array(
        'de' => 'E-Mail Marketing-Integration, mit der Sie einfach Ihre Kontakte synchronisieren und Produkt-Newsletter versenden können',
        'en' => 'Adds email marketing functionality to your E-commerce platform. Easily synchronize your contacts and send product newsletters.'
    ),
    'thumbnail'    => 'picture.png',
    'version'      => '4.0.00',
    'author'       => 'Newsletter2Go',
    'url'          => 'https://www.newsletter2go.de',
    'email'        => 'info@newsletter2go.de',
    'lang'         => 'de',
    'extend'      => array(
        'module_config' => 'nl2go/nl2go/controllers/admin/n2goSettings'
    ),
    'blocks' => array(
        array('template' => 'module_config.tpl', 'block'=>'admin_module_config_form', 'file'=>'views/admin/block/module_config.tpl'),
    ),
    'files' => array(
        'nl2goApi' => 'nl2go/nl2go/controllers/nl2goapi.php',
        'nl2goModel' => 'nl2go/nl2go/models/nl2gomodel.php',
    ),
    'settings' => array(
        array('group' => 'nl2go_credentials',     'name' => 'nl2goUserName',   'type' => 'str',   'value' => 'oxid'),
        array('group' => 'nl2go_credentials',     'name' => 'nl2goApiKey',     'type' => 'str',   'value' => md5(uniqid()))
    )
);

