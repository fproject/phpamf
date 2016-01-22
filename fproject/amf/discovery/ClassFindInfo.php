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
 * The information necessary for a service router to be able to load a class' file and instanciate it
 * could be extended later with namespaces when they become mainstream
 *
 */
class ClassFindInfo {

    /**
     * the absolute path to the file containing the class definition
     * @var String
     */
    public $absolutePath;

    /**
     * the name of the class.
     * @var String
     */
    public $className;

    /**
     * constructor
     * @param String $absolutePath
     * @param String $className
     */
    public function __construct($absolutePath, $className) {
        $this->absolutePath = $absolutePath;
        $this->className = $className;
    }

}

?>
