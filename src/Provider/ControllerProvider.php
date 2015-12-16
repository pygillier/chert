<?php
namespace pygillier\Chert\Provider;

use pygillier\Chert\Exception;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


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
            ->post('/min', "\\pygillier\\Chert\\Provider\\ControllerProvider::minifyAction")
            ->bind('send_url');
        $controllers
            ->get('/status/{key}', "\\pygillier\\Chert\\Provider\\ControllerProvider::statusAction")
            ->bind('status');
		$controllers
            ->get('/done/{hash}', "\\pygillier\\Chert\\Provider\\ControllerProvider::doneAction")
            ->bind('done');
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
		$form = $app['form.factory']->createBuilder('\pygillier\Chert\Form\LinkType')
        	->getForm();
		
        return $app['twig']->render('index.twig', array(
			'form' => $form->createView()
		));
    }
    
    public function minifyAction(Application $app, Request $request)
    {
	    $form = $app['form.factory']->createBuilder('\pygillier\Chert\Form\LinkType')
        	->getForm();
		
		$form->handleRequest($request);
		
		if($form->isValid())
		{
			$data = $form->getData();
			try
			{
				$hash = $app['chert']->minify($data['url']);
				return $app->redirect($app["url_generator"]->generate("done", array('hash' => $hash)));
			}
			catch(\PDOException $pdoe)
			{
				throw new Exception("An error occured while saving. Please try again later.");
			}
		}
		else
		{
			$app['session']->getFlashBag()->add("Provided link is invalid");
			return $app->redirect($app["url_generator"]->generate("home"));
		}
    }
    
    public function statusAction(Application $app, Request $request, $key)
    {
		// Status is available in debug mode only.
        if(!$app['config']['show_status'] || $key != $app['config']['status_key'])
		{
			$app['monolog']->addAlert("Unauthorized access to status page");
			throw new Exception("Nothing to see here.");
		}
            

        $offset = $request->get('page', 0);
        $limit = $app['config']['status_links_per_page'];
        // DB access
        try
        {
			$links = $app['chert']->getListing($offset, $limit);
			
			// Add hashes to liste
			$links = array_map(function($item) use ($app) {
				$item['hash'] = $app['hash_service']->getHash($item['id']);
				return $item;
			}, $links);

			return $app['twig']->render('status.twig', array(
				'count' => $app['chert']->countLinks(),
				'links' => $links,
			));
        }
        catch(\PDOException $err)
        {
			$app['monolog']->addError("Error while retrieving listing (offset: ${offset}, limit: ${limit}) :".$err->getMessage());
            throw new Exception("An error occured while processing the list.");
        }

    }
	
	public function doneAction(Application $app, $hash)
	{
		$link = $app['chert']->expand($hash);
		
		return $app['twig']->render('done.twig', array(
			'link' => $link,
			'hash' => $hash,
		));
	}
    
    public function showAction(Application $app, $hash)
    {
		// Get the original link
		$link = $app['chert']->expand($hash);

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
			throw new \Exception("Unknown id provided");
	}
}
