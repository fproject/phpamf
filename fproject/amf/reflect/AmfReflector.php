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

namespace fproject\amf\reflect;

class AmfReflector extends \ReflectionObject
{
    const ANNOTATION_AS3_TYPE = "@as3type";

    public $annotations;

    public function __construct ($argument)
    {
        parent::__construct($argument);
        $this->parseAmfAnnotations();
    }

    protected function parseAmfAnnotations()
    {
        $this->annotations = [];
        $refProps = $this->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach($refProps as $refProp)
        {
            $doc = $refProp->getDocComment();
            if($doc !== false)
            {
                $pos = strpos($doc, self::ANNOTATION_AS3_TYPE);
                if($pos !== false)
                {
                    $l = strlen(self::ANNOTATION_AS3_TYPE);
                    $endPos = strpos($doc, "\n", $pos + $l);
                    if($endPos === false)
                        $endPos = strlen($doc);
                    $s = trim(substr($doc, $pos + $l, $endPos - $pos));
                    if(!empty($s))
                    {
                        $annotation = $this->parseAS3TypeAnnotation($s);
                        if($annotation !== false)
                            $this->annotations[$refProp->name] = $annotation;
                    }
                }
            }
        }
    }

    protected function parseAS3TypeAnnotation($s)
    {
        $isVector = (strcmp(substr($s, 0, 6), "Vector")  == 0);
        if($isVector)
        {
            $pos = strpos($s, "<");
            $endPos = strpos($s, ">");
            if($pos === false || $endPos === false || ($pos + 2 > $endPos))
                return false;
            $fixed = ($endPos < strlen($s) && strtolower(trim(substr($s, $endPos + 1, 7))) == "(fixed)");
            $s = substr($s, $pos + 1, $endPos - $pos - 1);
        }
        $type = [
            'isVector' => $isVector,
            'typeName' => $s,
        ];
        if($isVector)
        {
            $type['vectorElementType'] = $this->getVectorElementType($s);
            /** @var bool $fixed */
            $type['isFixedVector'] = $fixed;
        }
        return $type;
    }

    /**
     * @param $type
     * @return int
     */
    protected function getVectorElementType($type)
    {
        switch($type) {
            case "int":
                return \Zend_Amf_Constants::AMF3_VECTOR_INT;
            case "uint":
                return \Zend_Amf_Constants::AMF3_VECTOR_UINT;
            case "Number":
                return \Zend_Amf_Constants::AMF3_VECTOR_NUMBER;
            default:
                return \Zend_Amf_Constants::AMF3_VECTOR_OBJECT;
        }
    }
}