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
 * @package    ZFEngine_File
 * @subpackage Transfer_Adapter
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * File transport adapter from Base64
 *
 * @category   ZFEngine
 * @package    ZFEngine_File
 * @subpackage Transfer_Adapter
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */
class ZFEngine_File_Transfer_Adapter_HttpBase64 extends Zend_File_Transfer_Adapter_Http
{
    /**
     * Base64-хеш файла
     *
     * @var string
     */
    protected $_file;
    /**
     * Constructor for Http File Transfers
     *
     * @param array $options OPTIONAL Options to set
     */
    public function __construct()
    {
        if (ini_get('file_uploads') == false) {
            // require_once 'Zend/File/Transfer/Exception.php';
            throw new Zend_File_Transfer_Exception('File uploads are not allowed in your php config!');
        }


        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set file for Http Base64 adapter
     *
     * @param  string $file
     * @param  string $name
     * @return boolean
     */
    public function setFile($file, $name)
    {
        $this->_file = $file;
        $this->_files = $this->_prepareFiles($name);
        $f = fopen($this->_files[$name]['tmp_name'], 'w');
        fwrite($f, base64_decode($this->_file));
        fclose($f);

        $this->addValidator('IsImage');
        $this->addValidator('Upload', false, $this->_files);
        
        return true;
    }

    /**
     * Receive the file from the client (Upload)
     *
     * @param  string|array $files (Optional) Files to receive
     * @return bool
     */
    public function receive($files = null)
    {
        if (!$this->isValid($files) || empty($this->_file)) {
            return false;
        }

        $check = $this->_getFiles($files);
        foreach ($check as $file => $content) {
            if (!$content['received']) {
                $directory   = '';
                $destination = $this->getDestination($file);
                if ($destination !== null) {
                    $directory = $destination . DIRECTORY_SEPARATOR;
                }

                $filename = $directory . $content['name'];
                $rename   = $this->getFilter('Rename');
                if ($rename !== null) {
                    $tmp = $rename->getNewName($content['tmp_name']);
                    if ($tmp != $content['tmp_name']) {
                        $filename = $tmp;
                    }

                    if (dirname($filename) == '.') {
                        $filename = $directory . $filename;
                    }

                    $key = array_search(get_class($rename), $this->_files[$file]['filters']);
                    unset($this->_files[$file]['filters'][$key]);
                }

                // Should never return false when it's tested by the upload validator
                $f = fopen($filename, 'w');
                fwrite($f, base64_decode($this->_file));
                fclose($f);
                
                if ($rename !== null) {
                    $this->_files[$file]['destination'] = dirname($filename);
                    $this->_files[$file]['name']        = basename($filename);
                }

                $this->_files[$file]['tmp_name'] = $filename;
                $this->_files[$file]['received'] = true;
            }

            if (!$content['filtered']) {
                if (!$this->_filter($file)) {
                    $this->_files[$file]['filtered'] = false;
                    return false;
                }

                $this->_files[$file]['filtered'] = true;
            }
        }

        return true;
    }



    /**
     * Prepare the emulated $_FILES array to match the internal syntax of one file per entry
     *
     * @return array
     */
    protected function _prepareFiles($name)
    {
        $result = array();
        $result[$name]['name']      = 'some.jpg';
        $result[$name]['type']      = '';
        $result[$name]['tmp_name']  = '/tmp/'.time().'.jpg';
        $result[$name]['error']     = 4;
        $result[$name]['size']      = 0;
        $result[$name]['options']   = $this->_options;
        $result[$name]['validated'] = false;
        $result[$name]['received']  = false;
        $result[$name]['filtered']  = false;

        return $result;
    }
}
