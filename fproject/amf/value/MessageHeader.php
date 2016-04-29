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
 * Message Headers provide context for the processing of the
 * the AMF Packet and all subsequent Messages.
 *
 * Multiple Message Headers may be included within an AMF Packet.
 *
 */
class MessageHeader
{
    /**
     * Name of the header
     *
     * @var string
     */
    public $name;

    /**
     * Flag if the data has to be parsed on return
     *
     * @var boolean
     */
    public $mustRead;

    /**
     * Length of the data field
     *
     * @var int
     */
    public $length;

    /**
     * Data sent with the header name
     *
     * @var mixed
     */
    public $data;

    /**
     * Used to create and store AMF Header data.
     *
     * @param String $name
     * @param Boolean $mustRead
     * @param $data
     * @param integer $length
     */
    public function __construct($name, $mustRead, $data, $length=null)
    {
        $this->name     = $name;
        $this->mustRead = (bool) $mustRead;
        $this->data     = $data;
        if (null !== $length) {
            $this->length = (int) $length;
        }
    }
}
