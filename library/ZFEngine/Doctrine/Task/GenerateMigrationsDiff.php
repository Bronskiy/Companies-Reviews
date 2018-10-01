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
 * Generate migration classes from a generated difference between models and yaml schema files
 *
 * @category   ZFEngine
 * @package    ZFEngine_Doctrine
 * @subpackage Task
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Doctrine_Task_GenerateMigrationsDiff extends ZFEngine_Doctrine_Task_Abstract
{
    /**
     * Task description
     * @var string
     */
    public $description = 'Generate migration classes from a generated difference between your models and yaml schema files';

    /**
     * Task required arguments
     * @var array
     */
    public $requiredArguments    =   array(
        'migrations_path' => 'Specify the path to your migration classes folder.',
        'temp_schema_path' => 'Path to temp shema files',
        'old_schema_path' => 'Path to oldest shema files',
        'modules_path' => 'Path to modules directories'
    );

    /**
     * Task name
     * @var string
     */
    public $taskName = 'zfengine-generate-migrations-diff';

    /**
     * Execute task
     *
     * @return void
     */
    public function execute()
    {
        // @todo translate comments

        $migrationsPath = $this->getArgument('migrations_path');
        $oldSchemaPath = $this->getArgument('old_schema_path');
        $tempSchemaPath = $this->getArgument('temp_schema_path');

        // Копируем новые yaml-файлы во временную папку
        $this->copyShema($tempSchemaPath);

        // Генерируем миграцию
        $migration = new Doctrine_Migration($migrationsPath);
        $diff = new Doctrine_Migration_Diff($oldSchemaPath, $tempSchemaPath, $migration);
        $changes = $diff->generateMigrationClasses();

        $numChanges = count($changes, true) - count($changes);

        if (is_dir($tempSchemaPath)) {
            //Чистим после себя временную папку
            $this->clearDirectory($tempSchemaPath);
        }
        // Если изменений нет - бросаем exception
        if (!$numChanges) {
            throw new Doctrine_Task_Exception('Could not generate migration classes from difference');
        // Иначе - уведомляем об успешной генерации и обновляем yaml-файлы до актуального состояния
        } else {
            $this->notify('Generated migration classes successfully from difference');
            
            // Удаляем файлы 
            $this->clearDirectory($oldSchemaPath);
            // Создаем новые
            $this->copyShema($oldSchemaPath);
            $this->notify('Shema files copied');
        }
    }
}
