<?php 
/**
 *  System configuration class
 *  
 *  Loading in main bootstrap
 *  
 */
class Main_Service_Config
{
    /**
     * Closed constructor
     */
    private function __construct() { }

    /**
     *  Метод предназначен для включения/отключения возможности
     *  генерации исключений в течение процесса диспетчеризации
     *  всего лишь обертка над методом Front контроллера
     *
     *  @param bool
     *  @return void
     */
    public static function throwExceptions($bThrow = true)
    {
        Zend_Controller_Front::getInstance()->throwExceptions($bThrow);
    }

    /**
     * Метод регистрации плагинов.
     *
     * АХТУНГ должен вызываться после метода автолоадера registerNamespace()
     */
    public static function registerPlugins()
    {
        $controller = Zend_Controller_Front::getInstance();
        $controller->registerPlugin(new Main_Controller_Plugin_Translation(), 2);
        $controller->registerPlugin(new Main_Controller_Plugin_AuthWatcher(), 4);
    }
}
