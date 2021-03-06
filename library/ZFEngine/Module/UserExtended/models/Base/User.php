<?php

/**
 * ZFEngine_Module_UserExtended_Model_Base_User
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property boolean $activated
 * @property string $activation_code
 * @property boolean $registered
 * @property string $registration_ip
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class ZFEngine_Module_UserExtended_Model_Base_User extends ZFEngine_Module_UserBase_Model_User
{
    public function setTableDefinition()
    {
        parent::setTableDefinition();
        $this->setTableName('users');
        $this->hasColumn('activated', 'boolean', null, array(
             'type' => 'boolean',
             ));
        $this->hasColumn('activation_code', 'string', 8, array(
             'type' => 'string',
             'length' => '8',
             ));
        $this->hasColumn('registered', 'boolean', null, array(
             'type' => 'boolean',
             ));
        $this->hasColumn('registration_ip', 'string', 15, array(
             'type' => 'string',
             'ip' => true,
             'length' => '15',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}