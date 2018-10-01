<?php 
/**
 * Translation plugin
 * 
 * Плагин для создания мультиязычности на сайте. 
 * 
 * Метод routeStartup используется для декорирования стандартных объектов маршрутов 
 *    от Zend с целью корректного распознавания языков в url без
 *    необходимости переписывания стандартных объектов маршрутов.
 *    Также мультиязычный декоратор корректно формирует
 *    мультиязычные url
 * Метод routeShutdown инициализирует объект Zend_Translate который используется
 *    для переводов при валидации и остального содержимого сайта
 *
 * @author domencom
 */
class Main_Controller_Plugin_Translation extends Zend_Controller_Plugin_Abstract
{
		
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        // force default routes if needed
        $routes = $router->addDefaultRoutes()->getRoutes();

        // rewrite each route with it's decorated copy
        foreach ($routes as $name => $route) {
            $router->addRoute($name, new Main_Controller_Router_Route_MultilingualDecorator($route));
        }
    }
	
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        Main_Translate_Array::regTranslation($request);
    }
}