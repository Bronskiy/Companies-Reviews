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
 * @package    ZFEngine_Filter
 * @subpackage File
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend/Filter/Interface.php */
require_once 'Zend/Filter/Interface.php';

/**
 * Renames file assigning it a unique name
 *
 * @category   ZFEngine
 * @package    ZFEngine_Filter
 * @subpackage File
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Filter_File_SetUniqueName implements Zend_Filter_Interface
{
    /**
     * Filter Options
     * @var array
     */
    private $_options;

    /**
     * Constructor
     *
     * @param array|string|Zend_Config $options     Options
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();

            if ($options['length'] < 5) {
                require_once 'Zend/Filter/Exception.php';
                throw new Zend_Filter_Exception('The minimum length of five characters');
            }
        } elseif (is_string($options)) {
            $options = array(
                'targetDir' => $options,
                'nameLength' => 10
            );
        } elseif (!is_array($options)) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }
        
        $this->_options = $options;
    }

    /**
     * Filter
     * 
     * @param  string $fileSource   Full path to the source file
     * @return string   The path to a new file
     */
    public function filter($fileSource)
    {
        if (! file_exists($fileSource)) {
            return $fileSource;
        }

        $pathInfo = pathinfo($fileSource);
        if (isset($this->_options['targetDir'])) {
            $targetDir = $this->_options['targetDir'];
        } else {
            $targetDir = $pathInfo['dirname'];
        }

        // Генерируем уникальное имя файла
        $fileTarget = ZFEngine_File::createFileWithUniqueName($targetDir,
                                                         strtolower($pathInfo['extension']),
                                                         $this->_options['nameLength']);

        $result = rename($fileSource, $fileTarget);

        if ($result === true) {
            return $fileTarget;
        }
        
        require_once 'Zend/Filter/Exception.php';
        throw new Zend_Filter_Exception(sprintf("File '%s' could not be 
                                        create. An error occured while
                                        processing the file.", $fileTarget));
    }
}