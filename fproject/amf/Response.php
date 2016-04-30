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

use fproject\amf\value\MessageHeader;
use fproject\amf\value\MessageBody;
use fproject\amf\parse\OutputStream;
use fproject\amf\parse\Amf0Serializer;

/**
 * Handles converting the PHP object ready for response back into AMF
 *
 */
class Response
{
    /**
     * @var int Object encoding for response
     */
    protected $_objectEncoding = 0;

    /**
     * @var MessageBody[] $_bodies Array of MessageBody objects
     */
    protected $_bodies = [];

    /**
     * Array of MessageHeader objects
     * @var array
     */
    protected $_headers = [];

    /**
     * @var OutputStream
     */
    protected $_outputStream;

    /**
     * Instantiate new output stream and start serialization
     *
     * @return Response
     */
    public function finalize()
    {
        $this->_outputStream = new OutputStream();
        $this->writeMessage($this->_outputStream);
        return $this;
    }

    /**
     * Serialize the PHP data types back into Actionscript and
     * create and AMF stream.
     *
     * @param  OutputStream $stream
     * @return Response
     */
    public function writeMessage(OutputStream $stream)
    {
        $objectEncoding = $this->_objectEncoding;

        //Write encoding to start of stream. Preamble byte is written of two byte Unsigned Short
        $stream->writeByte(0x00);
        $stream->writeByte($objectEncoding);

        // Loop through the AMF Headers that need to be returned.
        $headerCount = count($this->_headers);
        $stream->writeInt($headerCount);
        foreach ($this->getAmfHeaders() as $header) {
            $serializer = new Amf0Serializer($stream);
            $stream->writeUTF($header->name);
            $stream->writeByte($header->mustRead);
            $stream->writeLong(Constants::UNKNOWN_CONTENT_LENGTH);
            if (is_object($header->data)) {
                // Workaround for PHP5 with E_STRICT enabled complaining about
                // "Only variables should be passed by reference"
                $placeholder = null;
                $serializer->writeTypeMarker($placeholder, null, $header->data);
            } else {
                $serializer->writeTypeMarker($header->data);
            }
        }

        // loop through the AMF bodies that need to be returned.
        $bodyCount = count($this->_bodies);
        $stream->writeInt($bodyCount);
        foreach ($this->_bodies as $body) {
            $serializer = new Amf0Serializer($stream);
            $stream->writeUTF($body->getTargetURI());
            $stream->writeUTF($body->getResponseURI());
            $stream->writeLong(Constants::UNKNOWN_CONTENT_LENGTH);
            $bodyData = $body->getData();
            $markerType = ($this->_objectEncoding == Constants::AMF0_OBJECT_ENCODING) ? null : Constants::AMF0_AMF3;
            if (is_object($bodyData)) {
                // Workaround for PHP5 with E_STRICT enabled complaining about
                // "Only variables should be passed by reference"
                $placeholder = null;
                $serializer->writeTypeMarker($placeholder, $markerType, $bodyData);
            } else {
                $serializer->writeTypeMarker($bodyData, $markerType);
            }
        }

        return $this;
    }

    /**
     * Return the output stream content
     *
     * @return string The contents of the output stream
     */
    public function getResponse()
    {
        return $this->_outputStream->getStream();
    }

    /**
     * Return the output stream content
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getResponse();
    }

    /**
     * Add an AMF body to be sent to the Flash Player
     *
     * @param  MessageBody $body
     * @return Response
     */
    public function addAmfBody(MessageBody $body)
    {
        $this->_bodies[] = $body;
        return $this;
    }

    /**
     * Return an array of AMF bodies to be serialized
     *
     * @return MessageBody[]
     */
    public function getAmfBodies()
    {
        return $this->_bodies;
    }

    /**
     * Add an AMF Header to be sent back to the flash player
     *
     * @param  MessageHeader $header
     * @return Response
     */
    public function addAmfHeader(MessageHeader $header)
    {
        $this->_headers[] = $header;
        return $this;
    }

    /**
     * Retrieve attached AMF message headers
     *
     * @return array Array of MessageHeader objects
     */
    public function getAmfHeaders()
    {
        return $this->_headers;
    }

    /**
     * Set the AMF encoding that will be used for serialization
     *
     * @param  int $encoding
     * @return Response
     */
    public function setObjectEncoding($encoding)
    {
        $this->_objectEncoding = $encoding;
        return $this;
    }
}
