<?php
class Default_Configs_Acl extends Main_Service_Acl
{
    protected function _setStaticResources()
    {
        parent::_setStaticResources();
        $this->add(new Zend_Acl_Resource('mvc:default:index'));
        $this->add(new Zend_Acl_Resource('mvc:default:static'));
        $this->add(new Zend_Acl_Resource('mvc:default:error'));
    }
    
    protected function _setStaticRules()
    {
        parent::_setStaticRules();
        $this->allow(Users_Model_Role::DEFAULT_ROLE, 'mvc:default:index');
        $this->allow(Users_Model_Role::DEFAULT_ROLE, 'mvc:default:static');
        $this->allow(Users_Model_Role::DEFAULT_ROLE, 'mvc:default:error');
    }
}