<?php
        
        $f3 = require_once '../system/base.php';
        
        $f3->set('DEBUG',1);
                if ((float)PCRE_VERSION < 7.9)
                        trigger_error('PCRE version is out of date');
        
        // Load configuration
        $f3->config('config/config.ini');
        
        //Auto load classes
        $f3->set('AUTOLOAD', __dir__.'/; controller/; model/; library/; vendor/');
        
        //Application logging
        $logger = new \Log($f3->get('LOGFILE'));
        \Registry::set('logger', $logger);
        
        //Database connection
        if($f3->get('DATABASE'))
        {
                if ($f3->get('db.driver') == 'sqlite')
                {
                        $dsn = $f3->get('db.dsn');
                        $dsn = substr($dsn, 0, strpos($dsn, '/')) . realpath('../') . substr($dsn, strpos($dsn, '/'));
                        $db = new \DB\SQL($dsn);   
                }
                else
                {
                        $f3->set('db.dsn', sprintf("%s:host=%s;port=%d;dbname=%s",
                            $f3->get('db.driver'), $f3->get('db.hostname'), $f3->get('db.port'), $f3->get('db.name'))
                        );
                        
                        $db = new \DB\SQL($f3->get('db.dsn'), $f3->get('db.username'), $f3->get('db.password'));
                }        
                \Registry::set('db', $db);
        }
        
        // log script execution time if debugging
        if ($f3->get('DEBUG') || $f3->get('application.ENVIRONMENT') == 'development') {
            
            //Log database transactions if level 3
            if ($f3->get('DEBUG') == 3 && $f3->get('DATABASE'))
                $logger->write(\Registry::get('db')->log());

            $execution_time = round(microtime(true) - $f3->get('TIME'), 3);
            $logger->write('Script executed in ' . $execution_time . ' seconds using ' . round(memory_get_usage() / 1024 / 1024, 2) . '/' . round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB memory/peak');
        }
        
        //Load routes
        $f3->config('config/routes.ini');
        
        $f3->run();
