<?php
namespace pygillier\Chert\Provider;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers
            ->get('/', "\\pygillier\\Chert\\Provider\\ControllerProvider::indexAction")
            ->bind('home');
        $controllers
            ->post('/minify', "\\pygillier\\Chert\\Provider\\ControllerProvider::minifyAction")
            ->bind('send_url');
        $controllers
            ->get('/status', "\\pygillier\\Chert\\Provider\\ControllerProvider::statusAction")
            ->bind('status');
        $controllers
            ->get('/{hash}', "\\pygillier\\Chert\\Provider\\ControllerProvider::showAction")
            ->bind('show');

        return $controllers;
    }
    
    /** 
     * Homepage
     */
    public function indexAction(Application $app) 
    {
        return $app['twig']->render('index.twig', array(
            'name' => "Plop",
        ));
    }
    
    public function minifyAction(Application $app, Request $request)
    {
	    $url = $request->get('url');

    	// Validate given URL
    	if( count($app['validator']->validateValue($url, new Assert\Url())) == 0)
	    {
		    $res = $app['db']->insert('url', array( 'url' => $url));

		    // Returns an url with given ID
		    $id = $app['db']->lastInsertId();
		    $link = $app['url_generator']->generate('show', array('hash' => base_convert($id, 16, 36)), true);
		    return "Minified url : <a href=\"${link}\">${link}</a>";
	    }
	    else 
		    throw new \Exception("Invalid URL provided: ${url}");
    }
    
    public function statusAction(Application $app)
    {
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
    }
    
    public function showAction(Application $app, $hash)
    {
        // Get real ID and validate it
	$real_id = base_convert($hash, 36, 16);

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
            return $app['twig']->render('show.twig', array(
                'link' => $link['url'],
            ));
		}
	}
	else
		throw new Exception("Unknown id provided");
    }
}