<?php

namespace pygillier\Chert;

use Silex\Application as BaseApplication;

class Application extends BaseApplication
{

    public function __construct()
    {
        // super()
        parent::__construct();
        
        // Load configuration
        $this->register(new \Igorw\Silex\ConfigServiceProvider(
                        __DIR__.'/../app/settings.yml',
                        array(
                            'base_dir' => realpath(__DIR__.'/../')
                        ),
                        new \Igorw\Silex\YamlConfigDriver(),
                        'config'
            ));
        
        $this['debug'] = $this['config']['debug'];
        
        $this->initProviders();
        
        // Services
        $this['hash_service'] = $this->share(function($this){
           return new Service\HashService(); 
        });

        $this['chert'] = $this->share(function($this){
            return new Service\ChertMinifyService($this['db'], $this['hash_service']);
        });
        
        // Debug providers
        if($this['debug'] === true)
        {
            $this->register(new \Whoops\Provider\Silex\WhoopsServiceProvider());
        }
        
        // Controllers
        $this->mount("/", new Provider\ControllerProvider());
        
        // Error handler
        $this->error(function (\Exception $e, $code) 
                     {
                        switch ($code) {
                                case 404:
                                $message = 'The requested page could not be found.';
                                break;
                                default:
                                $message = 'We are sorry, but something went terribly wrong. ';
                        }
                        return new \Symfony\Component\HttpFoundation\Response($message);
                    });
    }
    
    private function initProviders()
    {
        $this->register(new \Silex\Provider\UrlGeneratorServiceProvider());
        $this->register(new \Silex\Provider\ValidatorServiceProvider());
        $this->register(new \Silex\Provider\FormServiceProvider());
        $this->register(new \Silex\Provider\DoctrineServiceProvider(), array(
            'db.options' => $this['config']['database']
        ));
        $this->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => __DIR__.'/../views',
            'twig.options'    => array(
                'cache' => __DIR__ . '/../app/cache',
            ),
        ));
        
        // Global variables
        $this['twig']->addGlobal("app_title", $this['config']['name']);
    }
}
