<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

require_once __DIR__.'/vendor/autoload.php';

$app = new pygillier\Chert\Application();


/**
 * Error handling
 */
$app->error(function (\Exception $e, $code) use($app) {
    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong. ';
            if($app['debug'])
            	$message.=$e->getMessage();
    }

    return new Response($message);
});


$app->run();
