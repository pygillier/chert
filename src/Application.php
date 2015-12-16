<?php

namespace pygillier\Chert;

use Igorw\Silex\ConfigServiceProvider;
use Igorw\Silex\YamlConfigDriver;
use Monolog\Logger;
use Silex\Application as BaseApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Whoops\Provider\Silex\WhoopsServiceProvider;

class Application extends BaseApplication
{

    public function __construct()
    {
        // super()
        parent::__construct();

        // Load configuration
        try 
        {
            $this->register(new ConfigServiceProvider(
                        __DIR__.'/../app/settings.yml',
                        array(
                            'base_dir' => realpath(__DIR__.'/../')
                        ),
                        new YamlConfigDriver(),
                        'config'
            ));
        }
        catch(\InvalidArgumentException $e)
        {
            throw new \Exception("Configuration file app/settings.yml is not available and/or readable.");
        }
        
        
        $this['debug'] = $this['config']['debug'];
        
        $this->initProviders();
        
        // Debug providers
        if($this['debug'] === true)
        {
            $this->register(new WhoopsServiceProvider());
            $this->register(new \Sorien\Provider\PimpleDumpProvider());
        }
        
        // Controllers
        $this->mount("/", new Provider\ControllerProvider());
        $this->mount("/v1", new Provider\ApiProvider());
        
        // Error handler
        $this->error(function (\Exception $e, $code) 
                     {
                        switch ($code) {
                                case 404:
                                $message = 'The requested page could not be found.';
                                break;
                                default:
                                $message = $e->getMessage();
                        }
                        return $this['twig']->render('error.twig', array(
                            'message' => $message,
                        ));
                    });
    }
    
    private function initProviders()
    {
        $this->register(new UrlGeneratorServiceProvider());
        
        // Forms
        $this->register(new ValidatorServiceProvider());
        $this->register(new TranslationServiceProvider(), array(
            'translator.messages' => array(),
        ));
        $this->register(new FormServiceProvider());
        
        // Doctrine
        $this->register(new DoctrineServiceProvider(), array(
            'db.options' => $this['config']['database']
        ));
        // Twig
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__.'/../views',
            'twig.options'    => array(
                'cache' => $this['config']['twig_cache'] ?__DIR__ . '/../app/cache': false,
            ),
        ));
        
        // Monolog
        $this->register(new MonologServiceProvider(), array(
            'monolog.logfile'   => __DIR__.'/../app/logs/service.log',
            'monolog.level'     => Logger::INFO,
            'monolog.name'      => 'chert'
        ));
        
        // Services
        $this['hash_service'] = $this->share(function($this){
           return new Service\HashService($this['config']['use_simple_cipher']); 
        });

        $this['chert'] = $this->share(function($this){
            return new Service\ChertMinifyService($this['db'], $this['hash_service']);
        });
    }
}
