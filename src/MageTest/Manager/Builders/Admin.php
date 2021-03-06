<?php

namespace MageTest\Manager\Builders;

/**
 * Class Admin
 *
 * @package MageTest\Manager\Builders
 */
class Admin extends AbstractBuilder implements BuilderInterface
{
    /**
     * @return false|\Mage_Core_Model_Abstract
     */
    public function build()
    {
        $this->model->addData($this->attributes);
        $this->saveModel($this->model);
        $this->addAdminRole();
        return $this->model;
    }

    /**
     *  Save admin user role
     */
    private function addAdminRole()
    {
        $role = \Mage::getModel("admin/role");
        $role->setParentId(1);
        $role->setTreeLevel(1);
        $role->setRoleType('U');
        $role->setUserId($this->model->getId());
        $this->saveModel($role);
    }
}