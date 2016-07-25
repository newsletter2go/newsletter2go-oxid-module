<?php
/**
 * Metadata version
 * @created 21.04.2015
 */

$sMetadataVersion = '3.0.09';


/**
 * Module information
 */
$aModule = array(
    'id'           => 'Newsletter2Go',
    'title'        => 'Newsletter2Go',
    'description'  => array(
        'de' => 'Modul für die Anbindung an Newsletter2Go. Es ermöglicht Empfänger-Synchronisation sowie das einfache Einfügen von Artikel-Informationen in enstprechende Newsletter-Vorlagen.',
        'en' => 'Module to synchronize newsletter recipients with Newsletter2Go'
    ),
    'thumbnail'    => 'picture.png',

    'version'      => '3.0.09',
    'author'       => 'Newsletter2Go',
    'url'          => 'https://www.newsletter2go.de',
    'email'        => 'info@newsletter2go.de',
    'lang'         => 'de',
    'extend'       => array(),
    'files' => array(
        'nl2goApi' => 'nl2go/nl2go/controllers/nl2goapi.php',
        'nl2goModel' => 'nl2go/nl2go/models/nl2gomodel.php',
    ),
    'settings' => array(
        array('group' => 'nl2go_credentials',     'name' => 'nl2goUserName',   'type' => 'str',   'value' => 'oxid'),
        array('group' => 'nl2go_credentials',     'name' => 'nl2goApiKey',     'type' => 'str',   'value' => md5(uniqid())),
    )
);

