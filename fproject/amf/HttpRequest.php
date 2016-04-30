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

namespace fproject\amf;

/**
 * AMF Request object -- Request via HTTP
 *
 * Extends {@link Request} to accept a request via HTTP. Request is
 * built at construction time using a raw POST; if no data is available, the
 * request is declared a fault.
 *
 */
class HttpRequest extends Request
{
    /**
     * Raw AMF request
     * @var string
     */
    protected $_rawRequest;

    /**
     * Constructor
     *
     * Attempts to read from php://input to get raw POST request; if an error
     * occurs in doing so, or if the AMF body is invalid, the request is declared a
     * fault.
     *
     */
    public function __construct()
    {
        // php://input allows you to read raw POST data. It is a less memory
        // intensive alternative to $HTTP_RAW_POST_DATA and does not need any
        // special php.ini directives
        $amfRequest = file_get_contents('php://input');

        // Check to make sure that we have data on the input stream.
        if ($amfRequest != '') {
            $this->_rawRequest = $amfRequest;
            $this->initialize($amfRequest);
        } else {
            echo '<p>Zend Amf Endpoint</p>' ;
        }
    }

    /**
     * Retrieve raw AMF Request
     *
     * @return string
     */
    public function getRawRequest()
    {
        return $this->_rawRequest;
    }
}
