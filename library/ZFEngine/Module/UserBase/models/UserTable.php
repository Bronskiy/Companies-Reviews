<?php
/**
 */
class ZFEngine_Module_UserBase_Model_UserTable extends Doctrine_Table
{

    /**
     * Все пользователи
     *
     * @return Doctrine_Query
     */
    public function findAllAsQuery()
    {
        return $this->createQuery('u');
    }

   /**
     * get users from database which have role in $roles
     *
     *  @param  array   $roles
     *  @return Doctrine_Collection
     */
    public function getAllUsersAssociateWithRoles($roles)
    {
        $query = $this->createQuery()
                ->select('DISTINCT *')
                ->from('Users_Model_User u')
                ->whereIn('u.role', $roles);
        return $query->execute();
    }

    /**
     * get all roles from database
     *
     * @return  Doctrine_Collection
     */
    public function getAllRoles()
    {
        $query = $this->createQuery()
                        ->select('DISTINCT(role) role')
                        ->from('Users_Model_User u');
        return $query->execute();
    }

    
}