<?php

/**
 * Users_Model_Base_Mailer
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $email
 * @property string $subject
 * @property string $body
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class ZFEngine_Module_UserBase_Model_Base_Mailer extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('mailer');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => true,
             'primary' => true,
             'autoincrement' => true,
             'length' => '4',
             ));
        $this->hasColumn('email', 'string', 128, array(
             'type' => 'string',
             'notnull' => true,
             'email' => true,
             'length' => '128',
             ));
        $this->hasColumn('subject', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '255',
             ));
        $this->hasColumn('body', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}