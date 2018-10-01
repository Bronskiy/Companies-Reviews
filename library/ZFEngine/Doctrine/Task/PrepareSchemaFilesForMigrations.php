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
 * Updated yaml-files
 *
 * @category   ZFEngine
 * @package    ZFEngine_Doctrine
 * @subpackage Task
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Doctrine_Task_PrepareSchemaFilesForMigrations extends ZFEngine_Doctrine_Task_Abstract
{
    /**
     * Task description
     * @var string
     */
    public $description = 'Updated yaml-files';

    /**
     * Task required arguments
     * @var array
     */
    public $requiredArguments = array(
        'old_schema_path' => 'Path to oldest shema files',
        'modules_path' => 'Path to modules directories'
    );

    /**
     * Task name
     * @var string
     */
    public $taskName = 'zfengine-prepare-schema-files-for-migrations';

    /**
     * Execute task
     *
     * @return void
     */
    public function execute()
    {
        // @todo translate comments
        
        $oldSchemaPath = $this->getArgument('old_schema_path');
        // Удаляем файлы
        $this->clearDirectory($oldSchemaPath);
        // Обновляем
        $this->copyShema($oldSchemaPath);

        $this->notify('Shema files copied');
    }
}
