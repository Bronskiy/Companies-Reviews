<?php

/**
 * ACL base class
 */
class Main_Service_Acl extends Zend_Acl {
    const REGISTRY_NAME = "Zend_Acl";

    /**
     * Constructor
     */
    public final function __construct() {
        $this->_setStaticRoles();
        $this->_setStaticResources();
        $this->_setStaticRules();
    }
    
    /**
     * Adding new resorce only if it doesnt already added
     * @param Zend_Acl_Resource_Interface $resource
     * @param type $parent 
     */
    public function add(Zend_Acl_Resource_Interface $resource, $parent = null) {
        if (!$this->has($resource)) {
            parent::add($resource, $parent);
        }
    }

    /**
     * Roles
     */
    protected function _setStaticRoles() {
        $this->addRole(new Zend_Acl_Role(Users_Model_Role::DEFAULT_ROLE))
            ->addRole(new Zend_Acl_Role(Users_Model_Role::MEMBER_ROLE), Users_Model_Role::DEFAULT_ROLE)
            ->addRole(new Zend_Acl_Role(Users_Model_Role::ADMIN_ROLE))
            ->addRole(new Zend_Acl_Role(Users_Model_Role::SUBADMIN_ROLE), Users_Model_Role::ADMIN_ROLE);
    }

    /**
     * Resources
     */
    protected function _setStaticResources() {
        $this->add(new Zend_Acl_Resource("mvc:users:index"));
        $this->add(new Zend_Acl_Resource("mvc:users:error"));
    }

    /**
     * Rules
     */
    protected function _setStaticRules() {
        $this->allow(Users_Model_Role::DEFAULT_ROLE, "mvc:users:error");
        $this->allow(Users_Model_Role::DEFAULT_ROLE, "mvc:users:index", "signup");
        $this->allow(Users_Model_Role::DEFAULT_ROLE, "mvc:users:index", "restore");
        $this->allow(Users_Model_Role::DEFAULT_ROLE, "mvc:users:index", "login");
        $this->allow(Users_Model_Role::DEFAULT_ROLE, "mvc:users:index", "activate");

        // admin is allowed to do anything
        $this->allow(Users_Model_Role::ADMIN_ROLE);
        $this->allow(Users_Model_Role::SUBADMIN_ROLE);
    }
}