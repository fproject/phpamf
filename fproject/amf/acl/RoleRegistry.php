<?php
///////////////////////////////////////////////////////////////////////////////
//
// © Copyright f-project.net 2010-present.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
///////////////////////////////////////////////////////////////////////////////

namespace fproject\amf\acl;
use fproject\amf\AmfException;

class RoleRegistry
{
    /**
     * Internal Role registry data storage
     *
     * @var array
     */
    protected $_roles = [];

    /**
     * Adds a Role having an identifier unique to the registry
     *
     * The $parents parameter may be a reference to, or the string identifier for,
     * a Role existing in the registry, or $parents may be passed as an array of
     * these - mixing string identifiers and objects is ok - to indicate the Roles
     * from which the newly added Role will directly inherit.
     *
     * In order to resolve potential ambiguities with conflicting rules inherited
     * from different parents, the most recently added parent takes precedence over
     * parents that were previously added. In other words, the first parent added
     * will have the least priority, and the last parent added will have the
     * highest priority.
     *
     * @param  RoleInterface              $role
     * @param  RoleInterface|string|array $parents
     * @throws AmfException
     * @return RoleRegistry Provides a fluent interface
     */
    public function add(RoleInterface $role, $parents = null)
    {
        $roleId = $role->getRoleId();

        if ($this->has($roleId)) {
            throw new AmfException("Role id '$roleId' already exists in the registry");
        }

        $roleParents = [];

        if (null !== $parents) {
            if (!is_array($parents)) {
                $parents = array($parents);
            }
            foreach ($parents as $parent) {
                try {
                    if ($parent instanceof RoleInterface) {
                        $roleParentId = $parent->getRoleId();
                    } else {
                        $roleParentId = $parent;
                    }
                    $roleParent = $this->get($roleParentId);
                } catch (AmfException $e) {
                    /** @var mixed $roleParentId */
                    throw new AmfException("Parent Role id '$roleParentId' does not exist", 0, $e);
                }
                $roleParents[$roleParentId] = $roleParent;
                $this->_roles[$roleParentId]['children'][$roleId] = $role;
            }
        }

        $this->_roles[$roleId] = array(
            'instance' => $role,
            'parents'  => $roleParents,
            'children' => array()
            );

        return $this;
    }

    /**
     * Returns the identified Role
     *
     * The $role parameter can either be a Role or a Role identifier.
     *
     * @param  RoleInterface|string $role
     * @throws AmfException
     * @return RoleInterface
     */
    public function get($role)
    {
        if ($role instanceof RoleInterface) {
            $roleId = $role->getRoleId();
        } else {
            $roleId = (string) $role;
        }

        if (!$this->has($role)) {
            throw new AmfException("Role '$roleId' not found");
        }

        return $this->_roles[$roleId]['instance'];
    }

    /**
     * Returns true if and only if the Role exists in the registry
     *
     * The $role parameter can either be a Role or a Role identifier.
     *
     * @param  RoleInterface|string $role
     * @return boolean
     */
    public function has($role)
    {
        if ($role instanceof RoleInterface) {
            $roleId = $role->getRoleId();
        } else {
            $roleId = (string) $role;
        }

        return isset($this->_roles[$roleId]);
    }

    /**
     * Returns an array of an existing Role's parents
     *
     * The array keys are the identifiers of the parent Roles, and the values are
     * the parent Role instances. The parent Roles are ordered in this array by
     * ascending priority. The highest priority parent Role, last in the array,
     * corresponds with the parent Role most recently added.
     *
     * If the Role does not have any parents, then an empty array is returned.
     *
     * @param  RoleInterface|string $role
     * @uses   RoleRegistry::get()
     * @return array
     */
    public function getParents($role)
    {
        $roleId = $this->get($role)->getRoleId();

        return $this->_roles[$roleId]['parents'];
    }

    /**
     * Returns true if and only if $role inherits from $inherit
     *
     * Both parameters may be either a Role or a Role identifier. If
     * $onlyParents is true, then $role must inherit directly from
     * $inherit in order to return true. By default, this method looks
     * through the entire inheritance DAG to determine whether $role
     * inherits from $inherit through its ancestor Roles.
     *
     * @param  RoleInterface|string $role
     * @param  RoleInterface|string $inherit
     * @param  boolean                        $onlyParents
     * @throws AmfException
     * @return boolean
     */
    public function inherits($role, $inherit, $onlyParents = false)
    {
        try {
            $roleId     = $this->get($role)->getRoleId();
            $inheritId = $this->get($inherit)->getRoleId();
        } catch (AmfException $e) {
            throw new AmfException($e->getMessage(), $e->getCode(), $e);
        }

        $inherits = isset($this->_roles[$roleId]['parents'][$inheritId]);

        if ($inherits || $onlyParents) {
            return $inherits;
        }

        foreach ($this->_roles[$roleId]['parents'] as $parentId => $parent) {
            if ($this->inherits($parentId, $inheritId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes the Role from the registry
     *
     * The $role parameter can either be a Role or a Role identifier.
     *
     * @param  RoleInterface|string $role
     * @throws AmfException
     * @return RoleRegistry Provides a fluent interface
     */
    public function remove($role)
    {
        try {
            $roleId = $this->get($role)->getRoleId();
        } catch (AmfException $e) {
            throw new AmfException($e->getMessage(), $e->getCode(), $e);
        }

        foreach ($this->_roles[$roleId]['children'] as $childId => $child) {
            unset($this->_roles[$childId]['parents'][$roleId]);
        }
        foreach ($this->_roles[$roleId]['parents'] as $parentId => $parent) {
            unset($this->_roles[$parentId]['children'][$roleId]);
        }

        unset($this->_roles[$roleId]);

        return $this;
    }

    /**
     * Removes all Roles from the registry
     *
     * @return RoleRegistry Provides a fluent interface
     */
    public function removeAll()
    {
        $this->_roles = [];

        return $this;
    }

    public function getRoles()
    {
        return $this->_roles;
    }

}
