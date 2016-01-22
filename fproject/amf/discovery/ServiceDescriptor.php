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
 * Contains all collected information about a service. This information will be used by the generator. 
 *
 */
class ServiceDescriptor {
    /**
     *name
     * @var string
     */
     public $name;
    /**
     *  methods
     * @var MethodDescriptor[] an array of MethodDescriptor
     */
    public $methods; 
    
    /**
     * class level comment
     * @var string 
     */
    public $comment;

    /**
     * constructor
     * @param string $name
     * @param MethodDescriptor[] $methods
     * @param string $comment
     */
    public function __construct($name, array $methods, $comment) {
        $this->name = $name;
        $this->methods = $methods;
        $this->comment = $comment;
    }
}

?>
