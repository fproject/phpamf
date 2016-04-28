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

/**
 * Return value reflection
 *
 * Stores the return value type and description
 *
 */
class ReturnValue
{
    /**
     * Return value type
     * @var string
     */
    protected $_type;

    /**
     * Return value description
     * @var string
     */
    protected $_description;

    /**
     * Constructor
     *
     * @param string $type Return value type
     * @param string $description Return value type
     */
    public function __construct($type = 'mixed', $description = '')
    {
        $this->setType($type);
        $this->setDescription($description);
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
}
