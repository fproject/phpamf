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
 * Method/Function prototypes
 *
 * Contains accessors for the return value and all method arguments.
 *
 */
class Prototype
{
    /**
     * Constructor
     *
     * @param ReturnValue $return
     * @param array $params
     * @throws AmfException
     */
    public function __construct(ReturnValue $return, $params = null)
    {
        $this->_return = $return;

        if (!is_array($params) && (null !== $params)) {
            throw new AmfException('Invalid parameters');
        }

        if (is_array($params)) {
            foreach ($params as $param) {
                if (!$param instanceof ParameterReflector) {
                    throw new AmfException('One or more params are invalid');
                }
            }
        }

        $this->_params = $params;
    }

    /**
     * Retrieve return type
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->_return->getType();
    }

    /**
     * Retrieve the return value object
     *
     * @access public
     * @return ReturnValue
     */
    public function getReturnValue()
    {
        return $this->_return;
    }

    /**
     * Retrieve method parameters
     *
     * @return ParameterReflector[] Array of {@link ParameterReflector}s
     */
    public function getParameters()
    {
        return $this->_params;
    }
}
