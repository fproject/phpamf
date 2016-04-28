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

namespace fproject\amf\reflect;

use ReflectionClass;
use fproject\amf\AmfException;


/**
 * Class/Object reflection
 *
 * Proxies calls to a ReflectionClass object, and decorates getMethods() by
 * creating its own list of {@link MethodReflector}s.
 *
 */
class ClassReflector extends AbstractReflector
{
    /**
     * Optional configuration parameters; accessible via {@link __get} and
     * {@link __set()}
     * @var array
     */
    protected $_config = [];

    /**
     * Array of {@link MethodReflector}s
     * @var array
     */
    protected $_methods = [];

    /**
     * Namespace
     * @var string
     */
    protected $_namespace = null;

    /**
     * ReflectionClass object
     * @var ReflectionClass
     */
    protected $_reflection;

    /**
     * Constructor
     *
     * Create array of dispatchable methods, each a
     * {@link MethodReflector}. Sets reflection object property.
     *
     * @param ReflectionClass $reflection
     * @param string $namespace
     * @param mixed $argv
     * @throws AmfException
     */
    public function __construct(ReflectionClass $reflection, $namespace = null, $argv = false)
    {
        $this->_reflection = $reflection;
        $this->setNamespace($namespace);

        foreach ($reflection->getMethods() as $method) {
            // Don't aggregate magic methods
            if ('__' == substr($method->getName(), 0, 2)) {
                continue;
            }

            if ($method->isPublic()) {
                // Get signatures and description
                $this->_methods[] = new MethodReflector($this, $method, $this->getNamespace(), $argv);
            }
        }
    }

    /**
     * Proxy reflection calls
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws AmfException
     */
    public function __call($method, $args)
    {
        if (method_exists($this->_reflection, $method)) {
            return call_user_func_array(array($this->_reflection, $method), $args);
        }

        throw new AmfException('Invalid reflection method');
    }

    /**
     * Retrieve configuration parameters
     *
     * Values are retrieved by key from {@link $_config}. Returns null if no
     * value found.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }

        return null;
    }

    /**
     * Set configuration parameters
     *
     * Values are stored by $key in {@link $_config}.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->_config[$key] = $value;
    }

    /**
     * Return array of dispatchable {@link MethodReflector}s.
     *
     * @access public
     * @return array
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * Get namespace for this class
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Set namespace for this class
     *
     * @param string $namespace
     * @throws AmfException
     */
    public function setNamespace($namespace)
    {
        if (empty($namespace)) {
            $this->_namespace = '';
            return;
        }

        if (!is_string($namespace) || !preg_match('/[a-z0-9_\.]+/i', $namespace)) {
            throw new AmfException('Invalid namespace');
        }

        $this->_namespace = $namespace;
    }

    /**
     * Wakeup from serialization
     *
     * Reflection needs explicit instantiation to work correctly. Re-instantiate
     * reflection object on wakeup.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->_reflection = new ReflectionClass($this->getName());
    }
}
