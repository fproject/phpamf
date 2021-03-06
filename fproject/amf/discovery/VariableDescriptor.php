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
 * Contains all collected information about a service method parameter
 *
 */
class VariableDescriptor {

    /**
     * Name
     * @var string
     */
    public $name;

    /**
     * This can be gathered in 2 manners: commentary tag analysis and type hinting analysis. For starters only the second method is used
     *
     * @var String
     */
    public $type;

    /**
     * Comment
     * @var string
     */
    public $comment;

    /**
     * constructor
     * @param String $name
     * @param String $type
     */
    public function __construct($name, $type=null) {
        $this->name = $name;
        $this->type = $type;
    }

    public function parseTypeFromComment()
    {
        $comment = $this->comment;
        //get rid of phpdoc formatting
        $comment = str_replace('/**', '', $comment);
        $comment = str_replace('*/', '', $comment);
        $comment = str_replace('*', '', $comment);
        $exploded = explode('@', $comment);

        foreach ($exploded as $value) {
            if (strtolower(substr($value, 0, 3)) == 'var') {
                $words = explode(' ', $value);
                $type = trim($words[1]);
                break;
            }
        }

        if(!empty($type))
        {
            $this->type = $type;
        }
    }
}

?>
