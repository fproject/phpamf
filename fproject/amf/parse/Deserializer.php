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

namespace fproject\amf\parse;

/**
 * Abstract cass that all deserializer must implement.
 *
 * Logic for deserialization of the AMF envelop is based on resources supplied
 * by Adobe Blaze DS. For and example of deserialization please review the BlazeDS
 * source tree.
 *
 */
abstract class Deserializer extends AbstractParser
{
    /**
     * The raw string that represents the AMF request.
     *
     * @var InputStream
     */
    protected $_stream;

    /**
     * Constructor
     *
     * @param  InputStream $stream
     */
    public function __construct(InputStream $stream)
    {
        $this->_stream = $stream;
    }

    /**
     * Checks for AMF marker types and calls the appropriate methods
     * for deserializing those marker types. Markers are the data type of
     * the following value.
     *
     * @param int $markerType
     * @return mixed Whatever the data type is of the marker in php
     */
    public abstract function readTypeMarker($markerType = null);
}
