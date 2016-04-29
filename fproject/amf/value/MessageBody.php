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
 * An AMF Message contains information about the actual individual
 * transaction that is to be performed. It specifies the remote
 * operation that is to be performed; a local (client) operation
 * to be invoked upon success; and, the data to be used in the
 * operation.
 * <p/>
 * This Message structure defines how a local client would
 * invoke a method/operation on a remote server. Additionally,
 * the response from the Server is structured identically.
 *
 */
class MessageBody
{
    /**
     * A string describing which operation, function, or method
     * is to be remotley invoked.
     * @var string
     */
    protected $_targetUri = "";

    /**
     * Universal Resource Identifier that uniquely targets the originator's
     * Object that should receive the server's response. The server will
     * use this path specification to target the "OnResult()" or "onStatus()"
     * handlers within the client. For Flash, it specifies an ActionScript
     * Object path only. The NetResponse object pointed to by the Response Uri
     * contains the connection state information. Passing/specifying this
     * provides a convenient mechanism for the client/server to share access
     * to an object that is managing the state of the shared connection.
     *
     * Since the server will use this field in the event of an error,
     * this field is required even if a successful server request would
     * not be expected to return a value to the client.
     *
     * @var string
     */
    protected $_responseUri = "";

    /**
     * Contains the actual data associated with the operation. It contains
     * the client's parameter data that is passed to the server's operation/method.
     * When serializing a root level data type or a parameter list array, no
     * name field is included. That is, the data is anonomously represented
     * as "Type Marker"/"Value" pairs. When serializing member data, the data is
     * represented as a series of "Name"/"Type"/"Value" combinations.
     *
     * For server generated responses, it may contain any ActionScript
     * data/objects that the server was expected to provide.
     *
     * @var string|\fproject\amf\value\messaging\AbstractMessage $_data
     */
    protected $_data;

    /**
     * Constructor
     *
     * @param  string $targetUri
     * @param  string $responseUri
     * @param  string $data
     */
    public function __construct($targetUri, $responseUri, $data)
    {
        $this->setTargetURI($targetUri);
        $this->setResponseURI($responseUri);
        $this->setData($data);
    }

    /**
     * Retrieve target Uri
     *
     * @return string
     */
    public function getTargetURI()
    {
        return $this->_targetUri;
    }

    /**
     * Set target Uri
     *
     * @param  string $targetUri
     * @return MessageBody
     */
    public function setTargetURI($targetUri)
    {
        if (null === $targetUri) {
            $targetUri = '';
        }
        $this->_targetUri = (string) $targetUri;
        return $this;
    }

    /**
     * Get target Uri
     *
     * @return string
     */
    public function getResponseURI()
    {
        return $this->_responseUri;
    }

    /**
     * Set response Uri
     *
     * @param  string $responseUri
     * @return MessageBody
     */
    public function setResponseURI($responseUri)
    {
        if (null === $responseUri) {
            $responseUri = '';
        }
        $this->_responseUri = $responseUri;
        return $this;
    }

    /**
     * Retrieve response data
     *
     * @return string|\fproject\amf\value\messaging\AbstractMessage
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Set response data
     *
     * @param  mixed $data
     * @return MessageBody
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Set reply method
     *
     * @param  string $methodName
     * @return MessageBody
     */
    public function setReplyMethod($methodName)
    {
        if (!preg_match('#^[/?]#', $methodName)) {
            $this->_targetUri = rtrim($this->_targetUri, '/') . '/';
        }
        $this->_targetUri = $this->_targetUri . $methodName;
        return $this;
    }
}
