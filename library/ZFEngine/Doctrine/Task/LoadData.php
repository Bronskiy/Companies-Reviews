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
 * Generate migration classes for an existing set of models
 *
 * @category   ZFEngine
 * @package    ZFEngine_Doctrine
 * @subpackage Task
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Doctrine_Task_LoadData extends ZFEngine_Doctrine_Task_Abstract
{
    /**
     * Task description
     * @var string
     */
    public $description = 'Load data from a yaml data fixture file.';

    /**
     * Task required arguments
     * @var array
     */
    public $requiredArguments    =   array(
        'data_fixtures_path' => 'Specify path to write the yaml data fixtures file to.',
        'modules_path' => 'Path to modules directories'
    );

    public $optionalArguments    =   array('append' =>  'Whether or not to append the data');

    /**
     * Task name
     * @var string
     */
    public $taskName = 'zfengine-load-data';
    
    /**
     * Execute task
     *
     * @return void
     */
    public function execute()
    {
        // @todo translate comments

        $modulesPath = $this->getArgument('modules_path');
        $this->loadModels($modulesPath);
        $this->notify('Models loaded...');

        Doctrine_Core::loadData($this->getArgument('data_fixtures_path'), $this->getArgument('append', false));
        $this->notify('Fixtures added');
    }
}