<?php

/**
 * ZFEngine_Module_Pages_Model_Base_Page
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @property integer $id
 * @property string $title
 * @property string $alias
 * @property string $content
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class ZFEngine_Module_Pages_Model_Base_Page extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('pages');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => true,
             'primary' => true,
             'autoincrement' => true,
             'length' => '4',
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '255',
             ));
        $this->hasColumn('alias', 'string', 32, array(
             'type' => 'string',
             'unique' => true,
             'notnull' => true,
             'length' => '32',
             ));
        $this->hasColumn('content', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $i18n0 = new Doctrine_Template_I18n(array(
             'fields' =>
             array(
              0 => 'title',
              1 => 'content',
             ),
             ));
        $this->actAs($i18n0);
    }
}