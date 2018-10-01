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
 * @package    ZFEngine_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Excel Reader */
require_once 'vendor/excel_reader2.php';

/**
 * Wrapper for Excel Reader
 *
 * @category   ZFEngine
 * @package    ZFEngine_Data
 * @subpackage Spreadsheet
 * @copyright  Copyright (c) 2009-2010 Stepan Tanasiychuk (http://stfalcon.com)
 * @license    http://zfengine.com/license/new-bsd     New BSD License
 */

class ZFEngine_Data_Spreadsheet_Excel
{
    /**
     * Excel spreadsheet
     */
    protected $spreadsheet;

    /**
     * Constructor creates object with excel spreadsheet
     *
     * @param string $excelFilename
     *
     * @return void
     */
    public function __construct($excelFilename)
    {
        $this->spreadsheet = new Spreadsheet_Excel_Reader($excelFilename);
    }

    /**
     * get count of column of spreadsheet
     *
     * @return integer
     */
    public function getColumnCount()
    {
        return $this->spreadsheet->colcount();
    }

    /**
     * get count of row of spreadsheet
     *
     * @return integer
     */
    public function getRowCount()
    {
        return $this->spreadsheet->rowcount();
    }

    /**
     * Transform excel spreadsheet to array
     *
     * @return array
     */
    public function toArray()
    {
        $rowcount = $this->spreadsheet->rowcount();
        $colcount = $this->spreadsheet->colcount();
        $array = array();
        for($row = 1; $row <= $rowcount; $row++) {
            for($col = 1; $col <= $colcount; $col++) {
                    $array[$row][$col] = $this->spreadsheet->val($row,$col);
            }
        }

        return $array;
    }
}

