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

use ReflectionFunction;
use fproject\amf\AmfException;
use ReflectionObject;
use ReflectionClass;

/**
 * Reflection for determining method signatures to use with server classes
 *
 */
class ReflectorHelper
{
    /**
     * Perform class reflection to create dispatch signatures
     *
     * Creates a {@link ClassReflector} object for the class or
     * object provided.
     *
     * If extra arguments should be passed to dispatchable methods, these may
     * be provided as an array to $argv.
     *
     * @param string|object $class Class name or object
     * @param array|bool|null $argv Optional arguments to be used during the method call
     * @param string $namespace Optional namespace with which to prefix the
     * method name (used for the signature key). Primarily to avoid collisions,
     * also for XmlRpc namespacing
     * @return ClassReflector
     * @throws AmfException
     */
    public static function reflectClass($class, $argv = false, $namespace = '')
    {
        if (is_object($class)) {
            $reflection = new ReflectionObject($class);
        } elseif (class_exists($class)) {
            $reflection = new ReflectionClass($class);
        } else {
            throw new AmfException('Invalid class or object passed to attachClass()');
        }

        if ($argv && !is_array($argv)) {
            throw new AmfException('Invalid argv argument passed to reflectClass');
        }

        return new ClassReflector($reflection, $namespace, $argv);
    }

    /**
     * Perform function reflection to create dispatch signatures
     *
     * Creates dispatch prototypes for a function. It returns a
     * {@link FunctionReflector} object.
     *
     * If extra arguments should be passed to the dispatchable function, these
     * may be provided as an array to $argv.
     *
     * @param string $function Function name
     * @param array|bool|null $argv Optional arguments to be used during the method call
     * @param string $namespace Optional namespace with which to prefix the
     * function name (used for the signature key). Primarily to avoid
     * collisions, also for XmlRpc namespacing
     * @return FunctionReflector
     * @throws AmfException
     */
    public static function reflectFunction($function, $argv = false, $namespace = '')
    {
        if (!is_string($function) || !function_exists($function)) {
            throw new AmfException('Invalid function "' . $function . '" passed to reflectFunction');
        }


        if ($argv && !is_array($argv)) {
            throw new AmfException('Invalid argv argument passed to reflectClass');
        }

        return new FunctionReflector(new ReflectionFunction($function), $namespace, $argv);
    }
}
