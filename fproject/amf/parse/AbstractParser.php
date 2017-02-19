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

class AbstractParser
{
    /**
     * Throw Parser Exception
     * @param $message
     * @param array $params
     * @throws \fproject\amf\AmfException
     */
    protected function throwZendException($message, $params=[])
    {
        for($i=0; $i<count($params); $i++)
        {
            $message = str_replace('{'.$i.'}',$params[$i], $message);
        }
        throw new \fproject\amf\AmfException($message);
    }
}