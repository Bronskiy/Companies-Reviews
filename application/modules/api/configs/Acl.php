<?php

/**
 * ACL config
 */
class Api_Configs_Acl extends Main_Service_Acl
{
    /**
     * Set static resources
     */
    protected function _setStaticResources()
    {
        parent::_setStaticResources();
        $this->add(new Zend_Acl_Resource('mvc:api:companies'));
        $this->add(new Zend_Acl_Resource('mvc:api:reviews'));
    }

    /**
     * Set static rules
     */
    protected function _setStaticRules()
    {
        parent::_setStaticRules();
        $this->allow(Users_Model_Role::DEFAULT_ROLE, 'mvc:api:companies');
        $this->allow(Users_Model_Role::DEFAULT_ROLE, 'mvc:api:reviews');
    }
}