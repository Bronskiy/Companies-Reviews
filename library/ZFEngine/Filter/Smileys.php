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
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Filter for parse smiles
 *
 * @category   ZFEngine
 * @package    ZFEngine_Filter
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_Filter_Smileys implements Zend_Filter_Interface
{
   
    /**
     * Smiles information
     *
     * @var array
     */
    protected $_smileys = array(
        ':)' => 'emoticon-happy.png',
        ':(' => 'emoticon-unhappy.png',
        ':o' => 'emoticon-surprised.png',
        ':p' => 'emoticon-tongue.png',
        ';)' => 'emoticon-wink.png',
        ':D' => 'emoticon-smile.png',
    );

    /**
     * path to smiles 
     *
     * @var array
     */
    protected $_path = 'images';

    public function __construct($path) {
        $this->_path = $path;
    }

    /**
     * replace symbols with images
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (!is_string($value)) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Value to parse should be a string.');
        }
       
        //Converting smiley images to <img /> tag.
        foreach ($this->_smileys as $smiley => $image) {
            $this->_smileys[$smiley] = '<img src = "' . $this->_path . '/' . $image . '" class="smile" alt = "' . $smiley . '" />';
        }
        $arrayStrings = preg_split('/(<code?>|<\/code>)/i', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        $open = false;
        foreach ($arrayStrings as $key => $item) {
            if ($item == '<code>') {
                $open = true;
            } elseif ($item == '</code>') {
                $open = false;
            }
            if (!$open) {
                $arrayStrings[$key] = str_replace(array_keys($this->_smileys), array_values($this->_smileys), $item);
            }
        }

        return implode('', $arrayStrings);
    }


}
