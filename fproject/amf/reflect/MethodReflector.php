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

use fproject\amf\AmfException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Method Reflection
 *
 */
class MethodReflector extends AbstractFunctionReflector
{
    /**
     * Parent class name
     * @var string
     */
    protected $_class;

    /**
     * Parent class reflection
     * @var ClassReflector
     */
    protected $_classReflection;

    /**
     * Constructor
     *
     * @param ClassReflector $class
     * @param ReflectionMethod $r
     * @param string $namespace
     * @param array $argv
     * @throws AmfException
     */
    public function __construct(ClassReflector $class, ReflectionMethod $r, $namespace = null, $argv = array())
    {
        $this->_classReflection = $class;
        $this->_reflection      = $r;

        $classNamespace = $class->getNamespace();

        // Determine namespace
        if (!empty($namespace)) {
            $this->setNamespace($namespace);
        } elseif (!empty($classNamespace)) {
            $this->setNamespace($classNamespace);
        }

        // Determine arguments
        if (is_array($argv)) {
            $this->_argv = $argv;
        }

        // If method call, need to store some info on the class
        $this->_class = $class->getName();

        // Perform some introspection
        $this->_reflect();
    }

    /**
     * Return the reflection for the class that defines this method
     *
     * @return ClassReflector
     */
    public function getDeclaringClass()
    {
        return $this->_classReflection;
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
        $this->_classReflection = new ClassReflector(new ReflectionClass($this->_class), $this->getNamespace(), $this->getInvokeArguments());
        $this->_reflection = new ReflectionMethod($this->_classReflection->getName(), $this->getName());
    }

}
