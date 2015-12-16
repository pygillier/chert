<?php
/**
 * Created by PhpStorm.
 * User: Pierre-Yves
 * Date: 12/12/2015
 * Time: 21:38
 */

namespace pygillier\Chert\Provider;


use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers
            ->post('/url', "\\pygillier\\Chert\\Provider\\ApiProvider::shortenAction")
            ->bind('api.shorten');

        $controllers
            ->get('/url', "\\pygillier\\Chert\\Provider\\ApiProvider::expandAction")
            ->bind('api.expand');

        // JSON Content-type
        $controllers->before(function (Request $request) use($app) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json'))
            {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
            else
            {
                // No JSON content-type, throw an error
                $error = array('error' => array(
                    'code' => 412,
                    'message' => "Content-type is not application/json"
                ));
                return $app->json($error,412);
            }
        });

        return $controllers;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function shortenAction(Application $app, Request $request)
    {
        $longUrl = $request->request->get('longUrl', false);

        if(false === $longUrl)
        {
            $error = array('error' => array(
                'code' => 400,
                'message' => "longUrl not provided"
            ));
            return $app->json($error, 400);
        }

        try
        {
            $hash = $app['chert']->minify($longUrl);
            $payload = array(
                'kind'      => "urlshortener#url",
                'id'        => $app['url_generator']->generate(
                    'show',
                    array('hash' => $hash),
                    \Symfony\Component\Routing\Generator\UrlGenerator::ABSOLUTE_URL),
                'longUrl'   => $longUrl
            );
        }
        catch(\Exception $e)
        {
            $payload = array('error' => array(
                'code' => 404,
                'message' => "An error occured during minification",
            ));
        }
        finally {
            return $app->json($payload, 201);
        }

    }

    /**
     * Returns a longUrl
     *
     * Given the shortURL, returns the associated longUrl
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function expandAction(Application $app, Request $request)
    {
        $shortUrl = $request->get('shortUrl', null);

        if(null === $shortUrl)
        {
            $error = array('error' => array(
                'code' => 400,
                'message' => "shortUrl not provided"
            ));
            return $app->json($error, 400);
        }

        // Extract hash part
        $hash = substr($shortUrl, strrpos($shortUrl, "/")+1);

        try
        {
            $link = $app['chert']->expand($hash);
            $payload = array(
                'kind'      => "urlshortener#url",
                'id'        => $shortUrl,
                'longUrl'   => $link['url'],
                'status'    => 'OK',
            );
        }
        catch(\Exception $e)
        {
            $payload = array('error' => array(
                'code' => 404,
                'message' => "URL not found"
            ));
        }
        finally
        {
            return $app->json($payload, 404);
        }
    }
}
