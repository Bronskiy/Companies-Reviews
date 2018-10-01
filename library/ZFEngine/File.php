<?php

/**
 * Класc для работы с файлами и именами файлов
 *
 * @category   ZFEngine
 * @package    ZFEngine_File
 * @subpackage Adapter
 * @author     Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZFEngine_File
{
    /**
     * Добавляет суффикс к имени файла
     *
     * @param string $filename
     * @param string|boolean $suffix
     */
    static public function addSuffixToFileName($filename, $suffix)
    {
        if (strlen($filename)) {
            $pathInfo = pathinfo($filename);

            $diraname = '';
            if ($pathInfo['dirname'] != '.') {
                $diraname = $pathInfo['dirname'] . DIRECTORY_SEPARATOR;
            }

            $filename = $pathInfo ['filename'] . $suffix
                        . '.' . $pathInfo['extension'];

            return $diraname . $filename;
        } else {
            return false;
        }
    }

    /**
     * Добавляет преффикс к имени файла
     *
     * @param string $filename
     * @param string|boolean $prefix
     */
    static public function addPrefixToFileName($filename, $prefix)
    {
        if (strlen($filename)) {
            $pathInfo = pathinfo($filename);

            $diraname = '';
            if ($pathInfo['dirname'] != '.') {
                $diraname = $pathInfo['dirname'] . DIRECTORY_SEPARATOR;
            }

            $filename = $prefix . $pathInfo ['filename']
                        . '.' . $pathInfo['extension'];

            $end = microtime();

            return $diraname . $filename;
        } else {
            return false;
        }
    }

    /**
     * Создает файл с случайным именем
     *
     * @param string $dir
     * @param string $extension
     * @param integer $length
     * @throws ZFEngine_Exception
     * @return string
     */
    static public function createFileWithUniqueName($directory, $extension = '', $length = 5)
    {
        $filename = '';
        $n = 0;

        while($n < 5) {
            $filename = $directory . DIRECTORY_SEPARATOR
                        . self::generateRandomFileName($length, $extension);
            if (! file_exists($filename)) {
                break;
            }

            $length++;
            $n++;
        }

        if (! $filename)  {
            require_once 'ZFEngine/Exeption.php';
            throw new ZFEngine_Exception('Не удалось создать файл с случайным именем
                                    в директории %s', $directory);
        }

        return $filename;
    }

    /**
     * Генерирует случайное имя файла
     *
     * @param integer $length
     * @param string $extension
     * @return string
     */
    static public function generateRandomFileName($length, $extension = '')
    {
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        $char_list .= "1234567890";

        $name = null;
        for($i = 0; $i < $length; $i++) {
            $name .= substr($char_list,(rand()%(strlen($char_list))), 1);
        }

        return ($extension) ? "{$name}.{$extension}" : $name;
    }
}