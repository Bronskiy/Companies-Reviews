<?php

/**
 * Companies_Model_Base_VideoStream
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $video_id
 * @property string $type
 * @property enum $status
 * @property integer $is_source
 * @property Companies_Model_CompanyVideo $Video
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Companies_Model_Base_VideoStream extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('video_streams');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'unsigned' => true,
             'autoincrement' => true,
             'length' => '4',
             ));
        $this->hasColumn('video_id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => true,
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('type', 'string', 1000, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '1000',
             ));
        $this->hasColumn('status', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'not_processed',
              1 => 'processing',
              2 => 'error',
              3 => 'processed',
             ),
             'notnull' => true,
             'default' => 'not_processed',
             ));
        $this->hasColumn('is_source', 'integer', 1, array(
             'type' => 'integer',
             'unsigned' => true,
             'notnull' => true,
             'default' => 1,
             'length' => '1',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Companies_Model_CompanyVideo as Video', array(
             'local' => 'video_id',
             'foreign' => 'id',
             'onDelete' => 'cascade',
             'onUpdate' => 'cascade'));
    }
}