<?php
/**
 * ZFEngine
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://zfengine.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zfengine.com so we can send you a copy immediately.
 *
 * @category   ZFEngine
 * @package    ZFEngine_Doctrine
 * @subpackage Task
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * ZFEngine asbtract Doctrine Task
 *
 * @category   ZFEngine
 * @package    ZFEngine_Doctrine
 * @subpackage Task
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
abstract class ZFEngine_Doctrine_Task_Abstract extends Doctrine_Task
{
    /**
     * Копирование yaml-файлов проекта в указанную директорию
     * 
     * @param string $to path to copy file
     * @return void
     */
    protected function copyShema($to)
    {
        // @todo translate comments

        $modulesPath = $this->getArgument('modules_path');
        // Если папки нет - создаем
        if (!is_dir($to)) {
            mkdir($to);
        }
        
        // Пробегаемся по директории с модулями
        foreach (scandir($modulesPath) as $directory) {
            if ($directory[0] != '.') {
                // Первая буква модуля - заглавная
                $moduleName = ucwords($directory);

                if (is_dir($modulesPath . $directory . '/models/')) {
                    $schemaPath = $modulesPath . $directory . '/configs/doctrine/schema/';
                    if (!is_dir($schemaPath)) {
                        continue;
                    }
                    $count = 1;
                    // Пробегаемся по списку моделей
                    foreach (scandir($schemaPath) as $file) {
                        $fullPath = $schemaPath . $file;
                        // Если это файл, а не папка
                        if ($file[0] != '.' && !is_dir($fullPath)) {
                            copy($fullPath, $to . $moduleName . '_Model_' . $file);
                            $this->_copyExtendsSchema($fullPath, $to);
                        }
                   }
                }
            }
        }
        
    }

    /**
     * Рекурсивно подгружает унаследованые файлы схем
     * @param string $fullPath
     * @param string $to
     */
    protected function _copyExtendsSchema($fullPath, $to)
    {
        $fileContents = file_get_contents($fullPath);
        // Если есть наследование - подтягиваем файл
        if (preg_match('/extends:(.*)/', $fileContents, $matches)) {
            // Достаем имя файла
            $ymlName = trim(($matches[1]));
            // если файл из модуля, который находится в ZFEngine тогда формируем другой путь
            if (strpos($ymlName, 'ZFEngine') !== FALSE) {
                // Путь к библиотеке
                $currentPath = realpath(__FILE__);
                $pathForModule = substr($currentPath, 0, strpos($currentPath, 'ZFEngine'));
                // Путь к унаследованому файлу
                $ymlPath = realpath($pathForModule . str_replace('_', '/', str_replace('Model', 'configs/doctrine/schema', $ymlName)) . '.yml');
            } else {
                $pathForModule = APPLICATION_PATH . '/modules/';
                // Путь к унаследованому файлу
                $parts = explode('_', $ymlName);
                $parts[0] = strtolower($parts[0]);
                $modulePath = implode('/', $parts);
                $ymlPath = realpath($pathForModule . str_replace('Model', 'configs/doctrine/schema', $modulePath) . '.yml');
            }
            // Если есть - подгружаем
            if (file_exists($ymlPath)) {
                copy($ymlPath, $to . $ymlName . '.yml');
            }

            // рекурсивно вызываем этот же метод
            $this->_copyExtendsSchema($ymlPath, $to);
        }
    }

    /**
     * Models loading
     * @param string $modulesPath
     */
    protected function loadModels($modulesPath)
    {
        // Список модулей
        $modulesDirectories = scandir($modulesPath);

        $modelsDirectories = array();
        // Пробегаемся по директории с модулями
        foreach ($modulesDirectories as $directory) {
            if ($directory[0] != '.') {
                // Первая буква модуля - заглавная
                $moduleName = ucwords($directory);
                $modelsPath = $modulesPath . $directory . '/models/';

                // Если есть такая папка
                if (is_dir($modelsPath)) {
                    // Пробегаемся по списку моделей
                    foreach (scandir($modelsPath) as $file) {
                        // Если это файл, а не папка
                        if ($file[0] != '.' && !is_dir($modelsPath . $file)) {
                            // Грузим модель
                            Doctrine_Core::loadModel($moduleName . '_Model_' . substr($file, 0, strlen($file)-4));
                        }
                   }
                }
            }
        }
    }

        /**
     * Удаляет файлы в указанной директории
     *
     * @param string $path path to directory
     * @return void
     */
    protected function clearDirectory($path)
    {
        // @todo translate comments
        
        if (is_dir($path)) {
            // Пробегаемся по списку файлов
            foreach (scandir($path) as $file) {
                // Если файл не начинается с точки
                if ($file[0] != '.') {
                    unlink($path . $file);
                }
            }
        }
    }

}