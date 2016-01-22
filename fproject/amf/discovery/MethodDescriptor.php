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

namespace fproject\amf\discovery;

/**
 * Contains all collected information about a service method.
 *
 */
class MethodDescriptor {

    /**
     * name
     * @var string 
     */
    public $name;

    /**
     * 
     * @var ParameterDescriptor[] array of ParameterDescriptor
     */
    public $parameters;

    /**
     *
     * @var string method level comment
     */
    public $comment;

    /**
     * return type
     * @var string 
     */
    public $returnType;

    /**
     * constructor
     * @param string $name
     * @param array $parameters
     * @param string $comment
     * @param string $returnType 
     */
    public function __construct($name, array $parameters, $comment, $returnType) {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->comment = $comment;
        $this->returnType = $returnType;
    }

}

?>
