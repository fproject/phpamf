<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Amf
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend_Xml_Security */
require_once 'Zend/Xml/Security.php';

use fproject\amf\auth\AuthResult;

/**
 * This class implements authentication against XML file with roles for Flex Builder.
 *
 * @package    Zend_Amf
 * @subpackage Adobe
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Adobe_Auth extends \fproject\amf\auth\AuthAbstract
{

    /**
     * ACL for authorization
     *
     * @var \fproject\amf\acl\Acl
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
        $this->_acl = new \fproject\amf\acl\Acl();
        $xml = Zend_Xml_Security::scanFile($rolefile);
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
     * @return \fproject\amf\acl\Acl
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * Perform authentication
     *
     * @throws \fproject\amf\AmfException
     * @return AuthResult
     * @see AuthAdapterInterface#authenticate()
     */
    public function authenticate()
    {
        if (empty($this->_username) ||
            empty($this->_password)) {
            throw new \fproject\amf\AmfException('Username/password should be set');
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

        $id = new stdClass();
        $id->role = $user["role"];
        $id->name = $this->_username;
        return new AuthResult(AuthResult::SUCCESS, $id);
    }
}
