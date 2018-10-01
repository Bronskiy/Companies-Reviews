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
 * Generate Doctrine_Record definitions from a Yaml schema file for modules
 *
 * @category   ZFEngine
 * @package    ZFEngine_Doctrine
 * @subpackage Task
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Doctrine_Task_GenerateModelsYaml extends ZFEngine_Doctrine_Task_Abstract
{
    /**
     * Task description
     * @var string
     */
    public $description = 'Generates your Doctrine_Record definitions from a Yaml schema file for modules';

    /**
     * Task optional arguments
     * @var array
     */
    public $optionalArguments = array(
        'modules_path' => 'Path to modules directories',
        'module_yaml_schemas_directory' => 'Directory name for YAML schemes of module',
        'module_models_directory' => 'Directory name for models of module',
        'generate_models_options' => 'Array of options for generating models',
    );

    /**
     * Task name
     * @var string
     */
    public $taskName = 'zfengine-generate-models-yaml';

    /**
     * Execute task
     * 
     * @return void
     */
    public function execute()
    {
        // @todo: divided execute() into several methods
        
        $modulesPath = realpath($this->getArgument('modules_path'));
        $moduleSchemasDirectory = $this->getArgument('module_yaml_schemas_directory',
                                                     '/configs/doctrine/schema');
        $moduleModelsDirectory = $this->getArgument('module_models_directory',
                                                    '/models');

        // list all modules
        $modules = array();
        // list all directories of schemas
        $schemasDirectories = array();

        // loop through of directories of modules
        foreach (scandir($modulesPath) as $directory) {
            $moduleSchemaPath = $modulesPath . DIRECTORY_SEPARATOR
                              . $directory . $moduleSchemasDirectory;
            if ($directory[0] != '.' && is_dir($moduleSchemaPath)) {
                $moduleName = ucwords($directory);
                $moduleModelsPath = $modulesPath . DIRECTORY_SEPARATOR
                                  . $directory . $moduleModelsDirectory;
                $modules[] = array(
                    // module name
                    'name' => $moduleName,
                    // module paths
                    'paths' => array(
                        'schema' => $moduleSchemaPath,
                        'models' => $moduleModelsPath
                    )
                );

                $schemasDirectories[] = $moduleSchemaPath;
            }
        }

        // generate models options
        $options = $this->getArgument('generate_models_options', array());

        // loop through of modules list
        foreach ($modules as $module) {
            // list generated models
            $allowedModels = array();

            // if folder with the database schema is not exist - skip this module
            if (!is_dir($module['paths']['schema'])) {
                continue;
            }

            foreach (scandir($module['paths']['schema']) as $filename) {
                if ($filename[0] != '.') {
                    $allowedModels[] = $module['name'] . '_Model_'
                                     . pathinfo($filename, PATHINFO_FILENAME);
                }
            }

            // if list generated models is empty - skip this module
            if (empty($allowedModels)) {
                continue;
            }

            $import = new Doctrine_Import_Schema();
            // set import options
            foreach ($options as $option => $value) {
                $import->setOption($option, $value);
            }

            // generate models
            $import->importSchema($schemasDirectories, 'yml', 
                $module['paths']['models'], $allowedModels
            );

            // processing files in accordance with Zend Framework naming standarts
            
            // directory of models for the current module
            $modelsFiles = scandir($module['paths']['models']);
            // loop through of generated models
            foreach ($modelsFiles as $filename) {
                if ($filename[0] != '.') {
                    $baseClassesDirectoryPath = $module['paths']['models']
                                              . DIRECTORY_SEPARATOR
                                              . $import->getOption('baseClassesDirectory');

                    if ($filename == $import->getOption('baseClassesDirectory')
                        && is_dir($baseClassesDirectoryPath))
                    {
                        // if it's the directory of base models
                        
                        // loop through of generated base models
                        foreach (scandir($baseClassesDirectoryPath) as $baseModelFileName) {
                            if ($baseModelFileName[0] != '.') {

                                $oldClassNamePreffix = $import->getOption('baseClassPrefix')
                                                     . $module['name'] . '_Model_';
                                // if it's not processed file
                                if (strpos($baseModelFileName, $oldClassNamePreffix) !== false) {
                                    // cut short name: BaseDefault_Model_Entity.php => Entity
                                    $newFileName = substr(
                                        $baseModelFileName,
                                        strlen($oldClassNamePreffix)
                                    );
                                    $newClassNamePreffix = $module['name'] . '_Model_'
                                                         . $import->getOption('baseClassesDirectory')
                                                         . '_';

                                    // rename file and replace class name
                                    $this->_renameFileAndReplaceClassName(
                                        $baseClassesDirectoryPath,
                                        $baseModelFileName, $newFileName,
                                        $oldClassNamePreffix, $newClassNamePreffix
                                    );
                                }
                            }
                        }
                    } else {
                        // if it's not processed file

                        $oldClassNamePreffix = $module['name'] . '_Model_';
                        if (strpos($filename, $oldClassNamePreffix) !== false) {
                            // cut short name: Default_Model_Entity.php => Entity
                            $newFileName = substr(
                                $filename,
                                strlen($oldClassNamePreffix)
                            );// . $import->getOption('suffix');

                            if (!in_array($newFileName, $modelsFiles)) {
                                // if it's file is not exist
                                
                                $newClassNamePreffix = $module['name'] . '_Model_'
                                                     . $import->getOption('baseClassesDirectory')
                                                     . '_';
                                $oldClassNamePreffix = $import->getOption('baseClassPrefix')
                                                     . $oldClassNamePreffix;

                                // rename file and replace extended base class name
                                $this->_renameFileAndReplaceClassName(
                                    $module['paths']['models'],
                                    $filename, $newFileName,
                                    $oldClassNamePreffix, $newClassNamePreffix
                                );
                            } else { 
                                // delete files generated doctrine
                                $filePath = $module['paths']['models']
                                          . DIRECTORY_SEPARATOR . $filename;
                                if (file_exists($filePath)) {
                                    unlink($filePath);
                                }
                            }
                        }
                    }
                }
            }

            $this->notify(sprintf('Generated models for module "%s" successfully',
                $module['name']
            ));
        }

        $this->notify('Generated models finished');
    }

    /**
     * Rename file and replace class name
     *
     * @param string $path
     * @param string $oldFileName
     * @param string $newFileName
     * @param string $oldClassName
     * @param string $newClassName
     * @return boolean
     */
    private function _renameFileAndReplaceClassName($path,
        $oldFileName, $newFileName,
        $oldClassName, $newClassName
    ) {
        $oldFilePath = $path . DIRECTORY_SEPARATOR . $oldFileName;
        $newFilePath = $path . DIRECTORY_SEPARATOR . $newFileName;

        // rename file
        rename($oldFilePath, $newFilePath);

        // replace class name into a file
        $fileBody = str_replace($oldClassName, $newClassName,
            file_get_contents($newFilePath)
        );

        return (bool) file_put_contents($newFilePath, $fileBody);
    }
}
