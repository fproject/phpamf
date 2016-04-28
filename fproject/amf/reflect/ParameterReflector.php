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

use ReflectionParameter;
use fproject\amf\AmfException;

/**
 * Parameter Reflection
 *
 * Decorates a ReflectionParameter to allow setting the parameter type
 *
 */
class ParameterReflector
{
    /**
     * @var ReflectionParameter
     */
    protected $_reflection;

    /**
     * Parameter position
     * @var int
     */
    protected $_position;

    /**
     * Parameter type
     * @var string
     */
    protected $_type;

    /**
     * Parameter description
     * @var string
     */
    protected $_description;

    /**
     * Constructor
     *
     * @param ReflectionParameter $r
     * @param string $type Parameter type
     * @param string $description Parameter description
     */
    public function __construct(ReflectionParameter $r, $type = 'mixed', $description = '')
    {
        $this->_reflection = $r;
        $this->setType($type);
        $this->setDescription($description);
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
     * Retrieve parameter type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set parameter type
     *
     * @param string|null $type
     * @throws AmfException
     */
    public function setType($type)
    {
        if (!is_string($type) && (null !== $type)) {
            throw new AmfException('Invalid parameter type');
        }

        $this->_type = $type;
    }

    /**
     * Retrieve parameter description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Set parameter description
     *
     * @param string|null $description
     * @throws AmfException
     */
    public function setDescription($description)
    {
        if (!is_string($description) && (null !== $description)) {
            throw new AmfException('Invalid parameter description');
        }

        $this->_description = $description;
    }

    /**
     * Set parameter position
     *
     * @param int $index
     * @return void
     */
    public function setPosition($index)
    {
        $this->_position = (int) $index;
    }

    /**
     * Return parameter position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->_position;
    }
}
