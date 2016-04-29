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
 * Wrapper class to store an AMF3 flash.utils.ByteArray
 *
 */
class ByteArray
{
    /**
     * @var string ByteString Data
     */
    protected $_data = '';

    /**
     * Create a ByteArray
     *
     * @param  string $data
     */
    public function __construct($data)
    {
        $this->_data = $data;
    }

    /**
     * Return the byte stream
     *
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }
}
