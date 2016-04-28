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

/**
 * AMF Authenticator
 * @package fproject\amf\auth
 */
class Auth
{
    /**
     * Singleton instance
     *
     * @var Auth
     */
    protected static $_instance = null;

    /**
     * Persistent storage handler
     *
     * @var AuthStorageInterface
     */
    protected $_storage = null;

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     */
    protected function __construct()
    {}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}

    /**
     * Returns an instance of Auth
     *
     * Singleton pattern implementation
     *
     * @return Auth Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return AuthStorageInterface
     */
    public function getStorage()
    {
        if (null === $this->_storage) {
            $this->setStorage(new \fproject\amf\auth\AuthSessionStorage());
        }

        return $this->_storage;
    }

    /**
     * Sets the persistent storage handler
     *
     * @param  AuthStorageInterface $storage
     * @return Auth Provides a fluent interface
     */
    public function setStorage(AuthStorageInterface $storage)
    {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  AuthAdapterInterface $adapter
     * @return \fproject\amf\auth\AuthResult
     */
    public function authenticate(AuthAdapterInterface $adapter)
    {
        $result = $adapter->authenticate();

        /**
         * ZF-7546 - prevent multiple successive calls from storing inconsistent results
         * Ensure storage has clean state
         */
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $this->getStorage()->write($result->getIdentity());
        }

        return $result;
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasIdentity()
    {
        return !$this->getStorage()->isEmpty();
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getIdentity()
    {
        $storage = $this->getStorage();

        if ($storage->isEmpty()) {
            return null;
        }

        return $storage->read();
    }

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public function clearIdentity()
    {
        $this->getStorage()->clear();
    }
}
