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
 * @package    ZFEngine_Model
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Abstract service for model which use database
 *
 * @category   ZFEngine
 * @package    ZFEngine_Model
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
abstract class ZFEngine_Model_Service_Database_Abstract extends ZFEngine_Model_Service_Abstract
{
    /**
     * @var string
     */
    protected $_modelName = null;

    /**
     * @var Doctrine_Record_Abstract
     */
    protected $_model = null;

    /**
     * Get model object
     * 
     * @param boolean $new get new clean object
     * @return Doctrine_Record
     */
    public function getModel($new = false)
    {
        if ($this->_model === null || $new ) {
            $this->setModel(new $this->_modelName);
        }

        return $this->_model;
    }

    /**
     * Check of model object exist
     *
     * @return boolean
     */
    public function existModel()
    {
        return ($this->_model !== null) ? true : false ;
    }

    /**
     * Set model
     *
     * @param Doctrine_Record_Abstract $model
     * @return ZFEngine_Model_Service_Database_Abstract
     */
    public function setModel(Doctrine_Record_Abstract $model)
    {

        $this->_model = $model;
        return $this;
    }
    
    /**
     * Get model name
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->_modelName;
    }
    
    /**
     * Return model mapper
     *
     * @return Doctrine_Table
     */
    public function getMapper()
    {
        return Doctrine_Core::getTable($this->_modelName);
    }


}
