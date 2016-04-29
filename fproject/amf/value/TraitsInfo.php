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

namespace fproject\amf\value;

/**
 * TraitsInfo used for serialize-deserialize AMF data 
 *
 */
class TraitsInfo
{
    /**
     * @var string Class name
     */
    protected $_className;

    /**
     * @var bool Whether or not this is a dynamic class
     */
    protected $_dynamic;

    /**
     * @var bool Whether or not the class is externalizable
     */
    protected $_externalizable;

    /**
     * @var array Class properties
     */
    protected $_properties;

    /**
     * Used to keep track of all class traits of an AMF3 object
     *
     * @param  string $className
     * @param  boolean $dynamic
     * @param  boolean $externalizable
     * @param  array $properties
     */
    public function __construct($className, $dynamic=false, $externalizable=false, $properties=null)
    {
        $this->_className      = $className;
        $this->_dynamic        = $dynamic;
        $this->_externalizable = $externalizable;
        $this->_properties     = $properties;
    }

    /**
     * Test if the class is a dynamic class
     *
     * @return boolean
     */
    public function isDynamic()
    {
        return $this->_dynamic;
    }

    /**
     * Test if class is externalizable
     *
     * @return boolean
     */
    public function isExternalizable()
    {
        return $this->_externalizable;
    }

    /**
     * Return the number of properties in the class
     *
     * @return int
     */
    public function length()
    {
        return count($this->_properties);
    }

    /**
     * Return the class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->_className;
    }

    /**
     * Add an additional property
     *
     * @param  string $name
     * @return TraitsInfo
     */
    public function addProperty($name)
    {
        $this->_properties[] = $name;
        return $this;
    }

    /**
     * Add all properties of the class.
     *
     * @param  array $props
     * @return TraitsInfo
     */
    public function addAllProperties(array $props)
    {
        $this->_properties = $props;
        return $this;
    }

    /**
     * Get the property at a given index
     *
     * @param  int $index
     * @return string
     */
    public function getProperty($index)
    {
        return $this->_properties[(int) $index];
    }

    /**
     * Return all properties of the class.
     *
     * @return array
     */
    public function getAllProperties()
    {
        return $this->_properties;
    }
}
