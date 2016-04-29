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

namespace fproject\amf\auth;

use fproject\common\utils\XmlSecurity;
use fproject\amf\AmfException;
use fproject\amf\acl\Acl;

/**
 * This class implements authentication against XML file with roles for Flex Builder.
 *
 */
class XmlAuth extends AuthAbstract
{

    /**
     * ACL for authorization
     *
     * @var Acl
     */
    protected $_acl;

    /**
     * Username/password array
     *
     * @var array
     */
    protected $_users = [];

    /**
     * Create auth adapter
     *
     * @param string $rolefile File containing XML with users and roles
     */
    public function __construct($rolefile)
    {
        $this->_acl = new Acl();
        $xml = XmlSecurity::scanFile($rolefile);
        /*
        Roles file format:
         <roles>
           <role id=”admin”>
                <user name=”user1” password=”pwd”/>
            </role>
           <role id=”hr”>
                <user name=”user2” password=”pwd2”/>
            </role>
        </roles>
        */
        foreach($xml->role as $role) {
            $this->_acl->addRole(new \fproject\amf\acl\Role((string)$role["id"]));
            foreach($role->user as $user) {
                $this->_users[(string)$user["name"]] = array("password" => (string)$user["password"],
                                                             "role" => (string)$role["id"]);
            }
        }
    }

    /**
     * Get ACL with roles from XML file
     *
     * @return Acl
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * Perform authentication
     *
     * @throws AmfException
     * @return AuthResult
     * @see AuthAdapterInterface#authenticate()
     */
    public function authenticate()
    {
        if (empty($this->_username) ||
            empty($this->_password)) {
            throw new AmfException('Username/password should be set');
        }

        if(!isset($this->_users[$this->_username])) {
            return new AuthResult(AuthResult::FAILURE_IDENTITY_NOT_FOUND,
                null,
                array('Username not found')
                );
        }

        $user = $this->_users[$this->_username];
        if($user["password"] != $this->_password) {
            return new AuthResult(AuthResult::FAILURE_CREDENTIAL_INVALID,
                null,
                array('Authentication failed')
                );
        }

        $id = new \stdClass();
        $id->role = $user["role"];
        $id->name = $this->_username;
        return new AuthResult(AuthResult::SUCCESS, $id);
    }
}
