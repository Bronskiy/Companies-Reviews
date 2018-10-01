<?php

/**
 * ACL rules for companies module
 * Class Companies_Configs_Acl
 */
class Companies_Configs_Acl extends Main_Service_Acl {
    /**
     * Resources
     */
    protected function _setStaticResources() {
        parent::_setStaticResources();
        $this->add(new Zend_Acl_Resource("mvc:companies:business"));
        $this->add(new Zend_Acl_Resource("mvc:companies:admin"));
        $this->add(new Zend_Acl_Resource("mvc:companies:index"));
        $this->add(new Zend_Acl_Resource("mvc:companies:service"));
    }

    /**
     * Rules
     */
    protected function _setStaticRules() {
        parent::_setStaticRules();
        $this->allow(Users_Model_Role::DEFAULT_ROLE, "mvc:companies:index");
        $this->allow(Users_Model_Role::MEMBER_ROLE, "mvc:companies:business");
        $this->allow(Users_Model_Role::MEMBER_ROLE, "mvc:companies:service");
        $this->allow(Users_Model_Role::DEFAULT_ROLE, "mvc:companies:service");

        // admin-specific limitations
        $this->deny(Users_Model_Role::ADMIN_ROLE, "mvc:companies:business");
        $this->deny(Users_Model_Role::SUBADMIN_ROLE, "mvc:companies:business");
        $this->deny(Users_Model_Role::SUBADMIN_ROLE, "mvc:companies:admin", "company-payments");
        $this->deny(Users_Model_Role::SUBADMIN_ROLE, "mvc:companies:admin", "company-billing");
        $this->deny(Users_Model_Role::SUBADMIN_ROLE, "mvc:companies:admin", "payments");

        // limit listing verification page for signed in users
        $this->deny(Users_Model_Role::MEMBER_ROLE, "mvc:companies:index", "verify");
        $this->deny(Users_Model_Role::ADMIN_ROLE, "mvc:companies:index", "verify");
        $this->deny(Users_Model_Role::SUBADMIN_ROLE, "mvc:companies:index", "verify");
    }
}