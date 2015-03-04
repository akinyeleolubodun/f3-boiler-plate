<?php

 class Welcome extends ControllerBase
 {
    public function __construct()
    {
    
    }
 
    public function index($f3)
    {
        $classes = array(
                            'Base'=>
                                    array(
                                            'hash',
                                            'json',
                                            'session'
                                    ),
                            'Cache'=>
                                    array(
                                            'apc',
                                            'memcache',
                                            'wincache',
                                            'xcache'
                                    ),
                            'DB\SQL'=>
                                    array(
                                            'pdo',
                                            'pdo_dblib',
                                            'pdo_mssql',
                                            'pdo_mysql',
                                            'pdo_odbc',
                                            'pdo_pgsql',
                                            'pdo_sqlite',
                                            'pdo_sqlsrv'
                                    ),
                            'DB\Jig'=>
                                    array('json'),
                            'DB\Mongo'=>
                                    array(
                                            'json',
                                            'mongo'
                                    ),
                            'Auth'=>
                                    array('ldap','pdo'),
                            'Bcrypt'=>
                                    array(
                                            'mcrypt',
                                            'openssl'
                                    ),
                            'Image'=>
                                    array('gd'),
                            'Lexicon'=>
                                    array('iconv'),
                            'SMTP'=>
                                    array('openssl'),
                            'Web'=>
                                    array('curl','openssl','simplexml'),
                            'Web\Geo'=>
                                    array('geoip','json'),
                            'Web\OpenID'=>
                                    array('json','simplexml'),
                            'Web\Pingback'=>
                                    array('dom','xmlrpc')
                        );
        
        $f3->set('classes', $classes);
        $f3->set('content', 'welcome.htm');
        echo View::instance()->render('layout.htm');
    }
 }