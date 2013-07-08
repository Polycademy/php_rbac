<?php

namespace RBAC\Test;


use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Subject\Subject;

trait RoleManagerTestTrait
{
    public function testPermissionFetchById()
    {
        $this->assertEquals(1, $this->rm->permissionFetchById(1)->permission_id);
    }


    public function testPermissionFetchByInvalidId()
    {
        $this->assertFalse($this->rm->permissionFetchById(-1));
    }

    public function testPermissionFetchByIdDBErr()
    {
        $this->assertFalse($this->getMockManager()->permissionFetchById(1));
    }

    public function testPermissionFetch()
    {
        $count_pre = $this->getConnection()->getRowCount("auth_permission");
        $this->assertEquals($count_pre, sizeof($this->rm->permissionFetch()));
    }

    public function testPermissionFetchDBErr()
    {
        $this->assertEquals([], $this->getMockManager()->permissionFetch());
    }

    public function testPermissionSave()
    {
        $count_pre = $this->getConnection()->getRowCount("auth_permission");
        $perm = Permission::create("test_perm", "description text");
        $this->assertTrue($this->rm->permissionSave($perm));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_permission"));
        $this->assertTrue($perm->permission_id > 0);
        $perm->name = "test_perm2";
        $this->assertTrue($this->rm->permissionSave($perm));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_permission"));
        $perm_fetched = $this->rm->permissionFetchById($perm->permission_id);
        $this->assertEquals($perm_fetched->permission_id, $perm->permission_id);
    }

    public function testPermissionSaveDBErr()
    {
        $this->assertFalse($this->getMockManager()->permissionSave($this->generatePerm()));
    }

    public function testPermissionDelete()
    {
        $perm = Permission::create("test_perm", "description text");
        $this->assertTrue($this->rm->permissionSave($perm));
        $count_pre = $this->getConnection()->getRowCount("auth_permission");
        $this->assertTrue($this->rm->permissionDelete($perm));
        $this->assertEquals($count_pre - 1, $this->getConnection()->getRowCount("auth_permission"));
    }

    public function testPermissionDeleteDBErr()
    {
        $this->assertFalse($this->getMockManager()->permissionDelete($this->generatePerm()));
    }

    /**
     * @expectedException \RBAC\Exception\ValidationError
     */
    public function testPermissionDeleteInvalidId()
    {
        $this->rm->permissionDelete(new Permission());
    }

    public function testRoleSave()
    {
        $count_pre = $this->getConnection()->getRowCount("auth_role");
        $role = Role::create("test_role");
        $read_perm = $this->rm->permissionFetchById(1);
        $write_perm = $this->rm->permissionFetchById(2);
        $role->addPermission($read_perm);
        $role->addPermission($write_perm);
        $this->assertEquals(2, sizeof($role->getPermissions()));
        $this->assertTrue($this->rm->roleSave($role));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_role"));
        $role->name = "new_name";
        $this->assertTrue($this->rm->roleSave($role));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_role"));
    }

    public function testRoleSaveDBErr()
    {
        $this->assertFalse($this->getMockManager()->roleSave(Role::create("test_role")));
    }

    public function testRoleDelete()
    {
        $role = Role::create("test_role");
        $role->addPermission($this->rm->permissionFetchById(1));
        $this->assertTrue($this->rm->roleSave($role));
        $count_pre = $this->getConnection()->getRowCount("auth_role");
        $this->assertTrue($this->rm->roleDelete($role));
        $this->assertEquals($count_pre - 1, $this->getConnection()->getRowCount("auth_role"));
        $this->assertFalse($this->rm->roleFetchByName("admin_read"));
    }

    /**
     * @expectedException \RBAC\Exception\ValidationError
     */
    public function testRoleDeleteInvalidRole()
    {
        $this->getMockManager()->roleDelete(Role::create("invalid_role"));
    }

    public function testRoleDeleteDBErr()
    {
        $this->assertFalse($this->getMockManager()->roleDelete($this->generateRole()));
    }

    /**
     * @expectedException \RBAC\Exception\ValidationError
     */
    public function testRolePermissionAddInvalidPerm()
    {
        $this->rm->rolePermissionAdd($this->generateRole(), Permission::create("blah"));
    }

    /**
     * @expectedException \RBAC\Exception\ValidationError
     */
    public function testRolePermissionAddInvalidRole()
    {
        $this->rm->rolePermissionAdd(Role::create("blah"), $this->generatePerm());
    }

    public function testRolePermissionAddDBErr()
    {
        $this->assertFalse($this->getMockManager()->rolePermissionAdd($this->generateRole(), $this->generatePerm()));
    }

    public function testRoleFetch()
    {
        $roles = $this->getConnection()->getRowCount("auth_role");
        $this->assertEquals($roles, sizeof($this->rm->roleFetch()));
    }

    public function testRoleFetchDBErr()
    {
        $this->assertEquals([], $this->getMockManager()->roleFetch());
    }

    public function testRoleFetchByName()
    {
        $name = "admin";
        $admin = $this->rm->roleFetchByName($name);
        $this->assertEquals($name, $admin->name);
    }

    public function testRoleFetchByNameDBErr()
    {
        $this->assertFalse($this->getMockManager()->roleFetchByName("admin"));
    }

    public function testRoleFetchById()
    {
        $role = $this->rm->roleFetchById(1);
        $this->assertEquals(1, $role->role_id);

        $roles = $this->rm->roleFetchById([1, 2]);
        $this->assertEquals(2, sizeof($roles));
        foreach ($roles as $role) {
            $this->assertTrue(sizeof($role->getPermissions()) > 0);
            foreach ($role->getPermissions() as $permission) {
                $this->assertTrue($role->hasPermission($permission->name));
            }
        }
    }

    public function testRoleFetchByIdDBErr()
    {
        $this->assertEquals(false, $this->getMockManager()->roleFetchById(1));
        $this->assertEquals([], $this->getMockManager()->roleFetchById([1, 2]));
        $this->assertEquals(false, $this->getMockManager()->roleFetchById(null));
        $this->assertEquals([], $this->getMockManager()->roleFetchById([]));
    }

    public function testloadSubjectRoles()
    {
        $admin = new Subject(1);
        $this->assertEquals(0, sizeof($admin->getRoleSet()->getRoles()));
        $this->rm->loadSubjectRoles($admin);
        $this->assertEquals(1, sizeof($admin->getRoleSet()->getRoles()));
    }

    public function testRoleFetchSubjectRolesDBErr()
    {
        $this->assertEquals([], $this->getMockManager()->roleFetchSubjectRoles(new Subject(1)));
    }

    public function testRoleAddSubject()
    {
        $role = $this->rm->roleFetchById(1);
        $this->assertTrue($this->rm->roleSave($role));
        $subject = new Subject(99);
        $initial_role_count = sizeof($subject->getRoleSet()->getRoles());
        $this->assertTrue($this->rm->roleAddSubject($role, $subject));
        $this->assertEquals($initial_role_count + 1, sizeof($subject->getRoleSet()->getRoles()));
    }

    public function testRoleAddSubjectDBErr()
    {
        $this->assertFalse($this->getMockManager()->roleAddSubject($this->generateRole(), new Subject(99)));
    }

    /**
     * @expectedException \RBAC\Exception\ValidationError
     */
    public function testRoleAddSubjectIdInvalidId()
    {
        $this->rm->roleAddSubjectId($this->generateRole(), null);
    }

    public function testRoleAddSubjectIdDBErr()
    {
        $this->assertFalse($this->getMockManager()->roleAddSubjectId($this->generateRole(), 1));
    }

    public function testPermissionFetchByRole()
    {
        $this->assertEquals([], $this->getMockManager()->permissionFetchByRole($this->generateRole()));
    }

    public function testRoleFetchSubjectRolesEmpty()
    {
        $this->assertEquals([], $this->rm->roleFetchSubjectRoles(new Subject(999999)));
    }
}
