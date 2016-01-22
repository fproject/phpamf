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

namespace fproject\amf\session;

abstract class SessionAbstract
{
    /**
     * Whether or not session permits writing (modification of $_SESSION[])
     *
     * @var bool
     */
    protected static $_writable = false;

    /**
     * Whether or not session permits reading (reading data in $_SESSION[])
     *
     * @var bool
     */
    protected static $_readable = false;

    /**
     * Since expiring data is handled at startup to avoid __destruct difficulties,
     * the data that will be expiring at end of this request is held here
     *
     * @var array
     */
    protected static $_expiringData = [];


    /**
     * Error message thrown when an action requires modification,
     * but current Zend_Session has been marked as read-only.
     */
    const _THROW_NOT_WRITABLE_MSG = 'Zend_Session is currently marked as read-only.';


    /**
     * Error message thrown when an action requires reading session data,
     * but current Zend_Session is not marked as readable.
     */
    const _THROW_NOT_READABLE_MSG = 'Zend_Session is not marked as readable.';


    /**
     * namespaceIsset() - check to see if a namespace or a variable within a namespace is set
     *
     * @param  string $namespace
     * @param  string $name
     * @return bool
     * @throws SessionException
     */
    protected static function _namespaceIsset($namespace, $name = null)
    {
        if (self::$_readable === false) {
            throw new SessionException(self::_THROW_NOT_READABLE_MSG);
        }

        if ($name === null) {
            return ( isset($_SESSION[$namespace]) || isset(self::$_expiringData[$namespace]) );
        } else {
            return ( isset($_SESSION[$namespace][$name]) || isset(self::$_expiringData[$namespace][$name]) );
        }
    }


    /**
     * namespaceUnset() - unset a namespace or a variable within a namespace
     *
     * @param  string $namespace
     * @param  string $name
     * @throws SessionException
     */
    protected static function _namespaceUnset($namespace, $name = null)
    {
        if (self::$_writable === false) {
            throw new SessionException(self::_THROW_NOT_WRITABLE_MSG);
        }

        $name = (string) $name;

        // check to see if the api wanted to remove a var from a namespace or a namespace
        if ($name === '') {
            unset($_SESSION[$namespace]);
            unset(self::$_expiringData[$namespace]);
        } else {
            unset($_SESSION[$namespace][$name]);
            unset(self::$_expiringData[$namespace][$name]);
        }

        // if we remove the last value, remove namespace.
        if (empty($_SESSION[$namespace])) {
            unset($_SESSION[$namespace]);
        }
    }


    /**
     * namespaceGet() - Get $name variable from $namespace, returning by reference.
     *
     * @param  string $namespace
     * @param  string $name
     * @return mixed
     * @throws SessionException
     */
    protected static function & _namespaceGet($namespace, $name = null)
    {
        if (self::$_readable === false) {
            throw new SessionException(self::_THROW_NOT_READABLE_MSG);
        }

        if ($name === null) {
            if (isset($_SESSION[$namespace])) { // check session first for data requested
                return $_SESSION[$namespace];
            } elseif (isset(self::$_expiringData[$namespace])) { // check expiring data for data reqeusted
                return self::$_expiringData[$namespace];
            } else {
                return $_SESSION[$namespace]; // satisfy return by reference
            }
        } else {
            if (isset($_SESSION[$namespace][$name])) { // check session first
                return $_SESSION[$namespace][$name];
            } elseif (isset(self::$_expiringData[$namespace][$name])) { // check expiring data
                return self::$_expiringData[$namespace][$name];
            } else {
                return $_SESSION[$namespace][$name]; // satisfy return by reference
            }
        }
    }


    /**
     * namespaceGetAll() - Get an array containing $namespace, including expiring data.
     *
     * @param string $namespace
     * @return mixed
     */
    protected static function _namespaceGetAll($namespace)
    {
        $currentData  = (isset($_SESSION[$namespace]) && is_array($_SESSION[$namespace])) ?
            $_SESSION[$namespace] : array();
        $expiringData = (isset(self::$_expiringData[$namespace]) && is_array(self::$_expiringData[$namespace])) ?
            self::$_expiringData[$namespace] : array();
        return array_merge($currentData, $expiringData);
    }
}
