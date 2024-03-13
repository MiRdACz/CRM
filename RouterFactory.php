<?php

namespace App\Router;
use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory
{

    public static function createRouter(): RouteList
	{
        $router = new RouteList;
	$router->addRoute('glamping-mapa','Homepage:glampingMapa');        
        $router->addRoute('web/odhlaseni', 'Homepage:out');                
        $router->addRoute('web/obnova-hesla', 'Homepage:lostpassword');
	$router->addRoute('admin/<action>','Admin:default');
        $router->addRoute('user/<action>[/<id>]','User:default');

        $router->addRoute('travel/<action>','Travel:default');
	$router->addRoute('glamp/<action>','Glamp:default');
        $router->addRoute('events/<action>','Events:default');
        $router->addRoute('marketing/<action>','Marketing:default');
        $router->addRoute('office/<action>','Office:default');
        $router->addRoute('grafika/<action>','Grafika:default');
        
        $router->addRoute('bprUser', 'Homepage:bprUser');
        $router->addRoute('[<url>]', 'Homepage:default');
        $router->addRoute('web/prihlaseni', 'Homepage:sign');


		return $router;
	}
}
