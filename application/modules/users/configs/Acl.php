<?php

/**
 * Users module ACL
 */
class Users_Configs_Acl extends Main_Service_Acl {
    /**
     * Resources
     */
    protected function _setStaticResources() {
        parent::_setStaticResources();
        $this->add(new Zend_Acl_Resource("mvc:users:index"));
    }

    /**
     * Rules
     */
    protected function _setStaticRules() {
        parent::_setStaticRules();
        
        $this->allow(Users_Model_Role::MEMBER_ROLE, "mvc:users:index");
        $this->allow(Users_Model_Role::DEFAULT_ROLE, "mvc:users:index", "change-password");
        
        $loggedIn = array(
            Users_Model_Role::MEMBER_ROLE,
            Users_Model_Role::ADMIN_ROLE,
            Users_Model_Role::SUBADMIN_ROLE
        );
        
        $this->deny($loggedIn, "mvc:users:index", "signup");
        $this->deny($loggedIn, "mvc:users:index", "login");
        $this->deny($loggedIn, "mvc:users:index", "restore");
        $this->deny($loggedIn, "mvc:users:index", "change-password");
    }
}