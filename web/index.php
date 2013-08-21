<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

define('APP_VERSION', '0.1-dev');

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());

// Configuration
if(is_readable(__DIR__.'/../settings.yml'))
	$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__.'/../settings.yml'));
else
	die('settings.yml file is not present. See README.md to create one.');

// Database connection
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $app['config']['database']
));

// Enable debug or not
$app['debug'] = $app['config']['debug'];



/**
 * Homepage
 *
 * Offers a form to submit a new URL
 */
$app->get('/', function() use ($app){
	$action = $app['url_generator']->generate('post');
	$output = <<< EOF
<html>
<head><title>Chert</title></head>
<body>
<form method="POST" action="$action">
<p>Please type an url to save :</p>
<input type="url" required placeholder="http://" name="url"/>
<input type="submit" value="Save"/>
</form>
</body></html>
EOF;
	return $output;
})->bind('homepage');


/**
 * POST an URL
 */
$app->post('/post', function(Request $request) use ($app){
	$url = $request->get('url');

	// Validate given URL
	if( count($app['validator']->validateValue($url, new Assert\Url())) == 0)
	{
		$res = $app['db']->insert('url', array( 'url' => $url));

		// Returns an url with given ID
		$id = $app['db']->lastInsertId();
		$link = $app['url_generator']->generate('show', array('id' => base_convert($id, 16, 36)), true);
		return "Minified url : <a href=\"${link}\">${link}</a>";
	}
	else 
		throw new \Exception("Invalid URL provided: ${url}");
	
})->bind('post');

/**
 * Application status
 */
$app->get('/status', function() use ($app){

	// Status is available in debug mode only.
	if(!$app['config']['show_status'])
		throw new \Exception("Unauthorized access");

	$output = sprintf("Chert / %s", APP_VERSION)."<br/>";

	// DB access
	try
	{
		$sql = "SELECT COUNT(*) AS TOTAL from url";
		$result = $app['db']->fetchAssoc($sql);

		$output .= sprintf("Database (%s) access OK: %s entries", $app['db']->getDatabasePlatform()->getName(), $result['TOTAL']);
	}
	catch(\PDOException $err)
	{
		$output .= '<span style="color: red; font-weight: bold">Database error !</span> '.$err->getMessage();
	}
	return $output;
})->bind('status');

/**
 * Show a link
 */
$app->get('/{id}', function($id) use ($app){
	
	// Get real ID and validate it
	$real_id = base_convert($id, 36, 16);

	if( !is_numeric($real_id))
	{
		throw new Exception("Invalid ID provided (not an int)");
	}
	
	$sql = "SELECT * FROM url WHERE id = ?";
	$link = $app['db']->fetchAssoc($sql, array( $real_id));

	
	if($link['url'])
	{
		if(true === $app['config']['auto_redirect'])
			return $app->redirect($link['url']);
		else
		{
			$output = 'You have requested the following URL: <a href="'.$link['url'].'">'.$link['url'].'</a>. ';
			$output .= 'Click to continue as no redirection will occur.';
			return $output;
		}
	}
	else
		throw new Exception("Unknown id provided");

})->bind("show");

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