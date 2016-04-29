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
 * Base abstract class for all AMF serializers.
 *
 */
abstract class Serializer extends AbstractParser
{
    /**
     * Reference to the current output stream being constructed
     *
     * @var string
     */
    protected $_stream;

    /**
     * str* functions overloaded using mbstring.func_overload
     *
     * @var bool
     */
    protected $_mbStringFunctionsOverloaded;

    /**
     * Constructor
     *
     * @param  OutputStream $stream
     */
    public function __construct(OutputStream $stream)
    {
        $this->_stream = $stream;
        $this->_mbStringFunctionsOverloaded = function_exists('mb_strlen') && (ini_get('mbstring.func_overload') !== '') && ((int)ini_get('mbstring.func_overload') & 2);
    }

    /**
     * Find the PHP object type and convert it into an AMF object type
     *
     * @param  mixed $content
     * @param  int $markerType
     * @param  mixed $contentByVal
     * @return void
     */
    public abstract function writeTypeMarker(&$content, $markerType = null, $contentByVal = false);
}
