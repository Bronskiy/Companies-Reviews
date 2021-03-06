<?php

/**
 * Users_Model_UserBase
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class ZFEngine_Module_UserBase_Model_User extends ZFEngine_Module_UserBase_Model_Base_User implements Zend_Acl_Resource_Interface
{

      /**
     * Administrator role name
     */
    const ROLE_ADMINISTRATOR    = 'administrator';

     /**
     * Moderator role name
     */
    const ROLE_MODERATOR       = 'moderator';
    
    /**
     * Member role name
     */
    const ROLE_MEMBER       = 'member';

    /**
     * Guest role name
     */
    const ROLE_GUEST        = 'guest';

    /**
     * Minimum username length
     */
    const MIN_USERNAME_LENGTH = 3;

    /**
     * Maximum username length
     */
    const MAX_USERNAME_LENGTH = 16;

    /**
     * Minimum password length
     */
    const MIN_PASSWORD_LENGTH = 4;


    /**
     * Возвращате список локализованых ролей
     *
     * @return array
     */
    public static function getRoleOptions()
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        $role = array(
            self::ROLE_MEMBER => $view->translate("Пользователь"),
            self::ROLE_MODERATOR => $view->translate("Модератор"),
            self::ROLE_ADMINISTRATOR => $view->translate("Администратор")
        );
        return $role;
    }

    /**
     * Return user resource id
     *
     * @return string
     */
    public function getResourceId()
    {
        if ($this->id == @Zend_Auth::getInstance()->getIdentity()->id) {
            return 'object:users:user:my';
        } else {
            return 'object:users:user:foreign';
        }
    }

    /**
     * Set password
     *
     * @param string $password
     * @return void
     */
    public function setPassword($password)
    {
        if (strlen($password)) {
            $passwordSalt = self::generateSalt();
            $passwordHash = md5(md5($password) . $passwordSalt);
            $this->_set('password_hash', $passwordHash);
            $this->_set('password_salt', $passwordSalt);
        }
    }


    /**
     * Set code for reset password
     *
     * @param string $passwordResetCode
     */
    public function setPasswordResetCode($passwordResetCode)
    {
        $this->_set('password_reset_code', $passwordResetCode);
        if (strlen($passwordResetCode)) {
            $this->_set('password_reset_code_created_at', new Doctrine_Expression('NOW()'));
        }
    }

    /**
     * Generate restore code
     *
     * @return string
     */
    public static function generateRestoreCode()
    {
        return substr(md5(mktime() + rand(0, 100)), -8);
    }

    /**
     * Generate salt
     *
     * @return string
     */
    public static function generateSalt()
    {
        $salt = '';
        $length = rand(5, 8); // salt length
        for($i=0; $i<$length; $i++) {
             $salt .= chr(rand(33, 126)); // symbol from ASCII-table
        }

        return $salt;
    }


}