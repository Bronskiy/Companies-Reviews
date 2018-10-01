<?php

/**
 * Configuration loader class
 */
class Main_Service_ConfigsLoader {
    const CONFIG_KEY = "config";

    /**
     * Get configuration
     * @return mixed|null
     */
    public static function getConfig() {
        if (!Zend_Registry::isRegistered(self::CONFIG_KEY)) {
            $configPath = realpath(APPLICATION_PATH . "/configs");
            $dirIterator = new DirectoryIterator($configPath);
            $configs = array();

            foreach ($dirIterator as $fileinfo) {
                if ($fileinfo->isFile() && pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == "ini") {
                    $configs[] = new Zend_Config_Ini($fileinfo->getPathname(), APPLICATION_ENV, true);
                }
            }
            
            if (empty($configs)) {
                return null;
            }
            
            $mainConfig = array_shift($configs);

            foreach ($configs as $config) {
                $mainConfig->merge($config);
            }
            
            Zend_Registry::set(self::CONFIG_KEY, $mainConfig);
        }
        
        return Zend_Registry::get(self::CONFIG_KEY);
    }
}