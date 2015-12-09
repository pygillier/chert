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
        
        // Debug providers
        if($this['debug'] == true)
        {
            $this->register(new \Whoops\Provider\Silex\WhoopsServiceProvider());
        }
        
        // Controllers
        $this->mount("/", new Provider\ControllerProvider());
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