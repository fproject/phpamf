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

use fproject\amf\AmfException;
use fproject\amf\value\ByteArray;
use fproject\amf\Constants;
use DateTime;
use DOMDocument;
use SimpleXMLElement;
use fproject\amf\reflect\AmfReflector;

/**
 * Detect PHP object type and convert it to a corresponding AMF3 object type
 *
 */
class Amf3Serializer extends Serializer
{
    /**
     * A constant empty string
     * @var string
     */
    protected $_strEmpty = '';

    /**
     * An array of reference objects per amf body
     * @var array
     */
    protected $_referenceObjects = [];

    /**
     * An array of reference strings per amf body
     * @var array
     */
    protected $_referenceStrings = [];

    /**
     * An array of reference class definitions, indexed by classname
     * @var array
     */
    protected $_referenceDefinitions = [];

    /**
     * Serialize PHP types to AMF3 and write to stream
     *
     * Checks to see if the type was declared and then either
     * auto negotiates the type or use the user defined markerType to
     * serialize the data from php back to AMF3
     *
     * @param  mixed $data
     * @param  int $markerType
     * @param  mixed $extraData The additional data.
     * In the case $data is NULL, this will param be used as 'byval' value for $data.
     * In the case $markerType is a AS3 Vector type (AMF3_VECTOR_INT, AMF3_VECTOR_UINT, AMF3_VECTOR_NUMBER or AMF3_VECTOR_OBJECT),
     * the $extraData will contain AS3 reflection information for writing AS3 Vector.
     * @throws AmfException
     */
    public function writeTypeMarker(&$data, $markerType = null, $extraData = false)
    {
        // Workaround for PHP5 with E_STRICT enabled complaining about "Only
        // variables should be passed by reference"
        if ((null === $data) && ($extraData !== false)) {
            $data = &$dataByVal;
        }
        if (null !== $markerType) {
            // Write the Type Marker to denote the following action script data type
            $this->_stream->writeByte($markerType);

            switch ($markerType) {
                case Constants::AMF3_NULL:
                    break;
                case Constants::AMF3_BOOLEAN_FALSE:
                    break;
                case Constants::AMF3_BOOLEAN_TRUE:
                    break;
                case Constants::AMF3_INTEGER:
                    $this->writeInteger($data);
                    break;
                case Constants::AMF3_NUMBER:
                    $this->_stream->writeDouble($data);
                    break;
                case Constants::AMF3_STRING:
                    $this->writeString($data);
                    break;
                case Constants::AMF3_DATE:
                    $this->writeDate($data);
                    break;
                case Constants::AMF3_ARRAY:
                    $this->writeArray($data);
                    break;
                case Constants::AMF3_OBJECT:
                    $this->writeObject($data);
                    break;
                case Constants::AMF3_BYTEARRAY:
                    $this->writeByteArray($data);
                    break;
                case Constants::AMF3_XMLSTRING;
                    $this->writeXml($data);
                    break;
                case Constants::AMF3_VECTOR_INT:
                case Constants::AMF3_VECTOR_UINT:
                case Constants::AMF3_VECTOR_NUMBER:
                case Constants::AMF3_VECTOR_OBJECT:
                    return $this->writeVector($data, $markerType, $extraData);
                //case Constants::AMF3_DICTIONARY:
                default:
                    throw new AmfException('Unknown Type Marker: ' . $markerType);
            }
        } else {
            // Detect Type Marker
            if (is_resource($data)) {
                $data = TypeLoader::handleResource($data);
            }
            switch (true) {
                case (null === $data):
                    $markerType = Constants::AMF3_NULL;
                    break;
                case (is_bool($data)):
                    if ($data){
                        $markerType = Constants::AMF3_BOOLEAN_TRUE;
                    } else {
                        $markerType = Constants::AMF3_BOOLEAN_FALSE;
                    }
                    break;
                case (is_int($data)):
                    if (($data > 0xFFFFFFF) || ($data < -268435456)) {
                        $markerType = Constants::AMF3_NUMBER;
                    } else {
                        $markerType = Constants::AMF3_INTEGER;
                    }
                    break;
                case (is_float($data)):
                    $markerType = Constants::AMF3_NUMBER;
                    break;
                case (is_string($data)):
                    $markerType = Constants::AMF3_STRING;
                    break;
                case (is_array($data)):
                    $markerType = Constants::AMF3_ARRAY;
                    break;
                case (is_object($data)):
                    // Handle object types.
                    if ($data instanceof DateTime) {
                        $markerType = Constants::AMF3_DATE;
                    } else if ($data instanceof ByteArray) {
                        $markerType = Constants::AMF3_BYTEARRAY;
                    } else if (($data instanceof DOMDocument) || ($data instanceof SimpleXMLElement)) {
                        $markerType = Constants::AMF3_XMLSTRING;
                    } else {
                        $markerType = Constants::AMF3_OBJECT;
                    }
                    break;
                default:
                    throw new AmfException('Unsupported data type: ' . gettype($data));
            }
            $this->writeTypeMarker($data, $markerType);
        }
        return 0;//To avoid PHP warning
    }

    /**
     * Write an AMF3 integer
     *
     * @param float|int $int
     * @return Amf3Serializer
     */
    public function writeInteger($int)
    {
        if (($int & 0xffffff80) == 0) {
            $this->_stream->writeByte($int & 0x7f);
            return $this;
        }

        if (($int & 0xffffc000) == 0 ) {
            $this->_stream->writeByte(($int >> 7 ) | 0x80);
            $this->_stream->writeByte($int & 0x7f);
            return $this;
        }

        if (($int & 0xffe00000) == 0) {
            $this->_stream->writeByte(($int >> 14 ) | 0x80);
            $this->_stream->writeByte(($int >> 7 ) | 0x80);
            $this->_stream->writeByte($int & 0x7f);
            return $this;
        }

        $this->_stream->writeByte(($int >> 22 ) | 0x80);
        $this->_stream->writeByte(($int >> 15 ) | 0x80);
        $this->_stream->writeByte(($int >> 8 ) | 0x80);
        $this->_stream->writeByte($int & 0xff);
        return $this;
    }

    /**
     * Send string to output stream, without trying to reference it.
     * The string is prepended with strlen($string) << 1 | 0x01
     *
     * @param  string $string
     * @return Amf3Serializer
     */
    protected function writeBinaryString(&$string){
        $ref = ($this->_mbStringFunctionsOverloaded ? mb_strlen($string, '8bit') : strlen($string)) << 1 | 0x01;
        $this->writeInteger($ref);
        $this->_stream->writeBytes($string);

        return $this;
    }

    /**
     * Send string to output stream
     *
     * @param  string $string
     * @return Amf3Serializer
     */
    public function writeString(&$string)
    {
        $len = $this->_mbStringFunctionsOverloaded ? mb_strlen($string, '8bit') : strlen($string);
        if(!$len){
            $this->writeInteger(0x01);
            return $this;
        }

        $ref = array_key_exists($string, $this->_referenceStrings) 
             ? $this->_referenceStrings[$string] 
             : false;
        if ($ref === false){
            $this->_referenceStrings[$string] = count($this->_referenceStrings);
            $this->writeBinaryString($string);
        } else {
            $ref <<= 1;
            $this->writeInteger($ref);
        }

        return $this;
    }

    /**
     * Send ByteArray to output stream
     *
     * @param  string|ByteArray $data
     * @return Amf3Serializer
     * @throws AmfException
     */
    public function writeByteArray(&$data)
    {
        if ($this->writeObjectReference($data)) {
            return $this;
        }

        if (is_string($data)) {
            //nothing to do
        } else if ($data instanceof ByteArray) {
            $data = $data->getData();
        } else {
            throw new AmfException('Invalid ByteArray specified; must be a string or fproject\amf\value\ByteArray');
        }

        $this->writeBinaryString($data);

        return $this;
    }

    /**
     * Send xml to output stream
     *
     * @param  DOMDocument|SimpleXMLElement $xml
     * @return Amf3Serializer
     * @throws AmfException
     */
    public function writeXml($xml)
    {
        if ($this->writeObjectReference($xml)) {
            return $this;
        }

        if(is_string($xml)) {
            //nothing to do
        } else if ($xml instanceof DOMDocument) {
            $xml = $xml->saveXML();
        } else if ($xml instanceof SimpleXMLElement) {
            $xml = $xml->asXML();
        } else {
            throw new AmfException('Invalid xml specified; must be a DOMDocument or SimpleXMLElement');
        }

        $this->writeBinaryString($xml);

        return $this;
    }

    /**
     * Convert DateTime to AMF date
     *
     * @param  DateTime $date
     * @return Amf3Serializer
     * @throws AmfException
     */
    public function writeDate($date)
    {
        if ($this->writeObjectReference($date)) {
            return $this;
        }

        if ($date instanceof DateTime) {
            $dateString = $date->format('U') * 1000;
        } /*elseif ($date instanceof Zend_Date) {
            $dateString = $date->toString('U') * 1000;
        }*/ else {
            throw new AmfException('Invalid date specified; must be a DateTime object');
        }

        $this->writeInteger(0x01);
        // write time to stream minus milliseconds
        $this->_stream->writeDouble($dateString);
        return $this;
    }

    /**
     * Write a PHP array back to the amf output stream
     *
     * @param array $array
     * @return Amf3Serializer
     */
    public function writeArray(&$array)
    {
        // arrays aren't reference here but still counted
        $this->_referenceObjects[] = $array;

        // have to seperate mixed from numberic keys.
        $numeric = [];
        $string  = [];
        foreach ($array as $key => &$value) {
            if (is_int($key)) {
                $numeric[] = $value;
            } else {
                $string[$key] = $value;
            }
        }

        // write the preamble id of the array
        $length = count($numeric);
        $id     = ($length << 1) | 0x01;
        $this->writeInteger($id);

        //Write the mixed type array to the output stream
        foreach($string as $key => &$value) {
            $this->writeString($key)
                 ->writeTypeMarker($value);
        }
        $this->writeString($this->_strEmpty);

        // Write the numeric array to ouput stream
        foreach($numeric as &$value) {
            $this->writeTypeMarker($value);
        }
        return $this;
    }

    /**
     * Write a PHP array to the amf output stream as AS3 vector
     *
     * @param array $array
     * @param $markerType
     * @param $vectorInfo
     * @return Amf3Serializer
     */
    public function writeVector(&$array, $markerType, $vectorInfo)
    {
        // Arrays aren't reference here but still counted
        $this->_referenceObjects[] = $array;

        $len = count($array);

        $ref = $len * 2 + 1;
        $this->writeInteger($ref);

        $this->_stream->writeByte($vectorInfo['fixed']);

        switch ($markerType)
        {
            case Constants::AMF3_VECTOR_INT:
                $numberFormat = "i";
                break;
            case Constants::AMF3_VECTOR_UINT:
                $numberFormat = "I";
                break;
            case Constants::AMF3_VECTOR_NUMBER:
                $numberFormat = "d";
                break;
            case Constants::AMF3_VECTOR_OBJECT:
                return $this->writeObjectVector($array, $len, $vectorInfo['elementType']);
            default:
                // Unknown vector type tag {type}
                $this->throwAmfException('Undefined vector type: {0}',[$markerType]);
        }

        $this->writeNumericVector($array, $len, $numberFormat);

        return $this;
    }

    /**
     * Write AS3 Vector.<int> from PHP array
     * @param array $array
     * @param int $len
     * @param $numberFormat
     */
    public function writeNumericVector($array, $len, $numberFormat)
    {
        for ($i = 0; $i < $len; $i++) {
            $bytes = pack($numberFormat, $array[$i]);
            if (!$this->_stream->isBigEndian()) {
                $bytes = strrev($bytes);
            }
            $this->_stream->writeBytes($bytes);
        }
    }

    /**
     * Write AS3 Vector.<int> from PHP array
     * @param array $array
     * @param int $len
     * @param string $elementType
     * @return $this
     */
    public function writeObjectVector($array, $len, $elementType)
    {
        if ($elementType == "String" || $elementType == "Boolean")
        {
            $this->_stream->writeByte(0x01);
            if($elementType == "String")
                $markerType = Constants::AMF3_STRING;
            else
                $markerType = null;
        }
        else
        {
            $this->writeString($elementType);
            $markerType = Constants::AMF3_OBJECT;
        }

        for ($i = 0; $i < $len; $i++) {
            if($elementType == "Boolean")
                $markerType = boolval($array[$i]) ? Constants::AMF3_BOOLEAN_TRUE : Constants::AMF3_BOOLEAN_FALSE;
            $this->writeTypeMarker($array[$i], $markerType);
        }
        return $this;
    }

    /**
     * Check if the given object is in the reference table, write the reference if it exists,
     * otherwise add the object to the reference table
     *
     * @param mixed $object object reference to check for reference
     * @param mixed $objectByVal object to check for reference
     * @return Boolean true, if the reference was written, false otherwise
     */
    protected function writeObjectReference(&$object, $objectByVal = false)
    {
        // Workaround for PHP5 with E_STRICT enabled complaining about "Only
        // variables should be passed by reference"
        if ((null === $object) && ($objectByVal !== false)) {
            $object = &$objectByVal;
        }

        $hash = spl_object_hash($object);
        $ref = array_key_exists($hash, $this->_referenceObjects)  ? $this->_referenceObjects[$hash] : false;

        // quickly handle object references
        if ($ref !== false) {
            $ref <<= 1;
            $this->writeInteger($ref);
            return true;
        }
        $this->_referenceObjects[$hash] = count($this->_referenceObjects);
        return false;
    }

    /**
     * Write object to output stream
     *
     * @param mixed $object
     * @return Amf3Serializer
     * @throws AmfException
     */
    public function writeObject($object)
    {
        if($this->writeObjectReference($object)) {
            return $this;
        }

        //Check to see if the object is a typed object and we need to change
        switch (true) {
             // the return class mapped name back to actionscript class name.
            case ($className = TypeLoader::getMappedClassName(get_class($object))):
                break;

            // Check to see if the user has defined an explicit Action Script type.
            case isset($object->_explicitType):
                $className = $object->_explicitType;
                break;

            // Check if user has defined a method for accessing the Action Script type
            case method_exists($object, 'getASClassName'):
                $className = $object->getASClassName();
                break;

            // No return class name is set make it a generic object
            case ($object instanceof \stdClass):
                $className = '';
                break;

             // By default, use object's class name
            default:
                $className = get_class($object);
                break;
        }

        //check to see, if we have a corresponding definition
        if(array_key_exists($className, $this->_referenceDefinitions))
        {
            $traitsInfo    = $this->_referenceDefinitions[$className]['id'];
            $encoding      = $this->_referenceDefinitions[$className]['encoding'];
            $propertyNames = $this->_referenceDefinitions[$className]['propertyNames'];

            if(array_key_exists('reflectProperties',$this->_referenceDefinitions[$className]))
                $reflectProperties = $this->_referenceDefinitions[$className]['reflectProperties'];

            $traitsInfo = ($traitsInfo << 2) | 0x01;

            $writeTraits = false;
        }
        else
        {
            $propertyNames = [];
            $reflectProperties = null;

            if($className == '')
            {
                //if there is no className, we interpret the class as dynamic without any sealed members
                $encoding = Constants::ET_DYNAMIC;
            }
            else
            {
                $encoding = Constants::ET_PROPLIST;
                foreach($object as $key => $value) {
                    if( $key[0] != "_") {
                        $propertyNames[] = $key;
                    }
                    if(is_array($value) && is_null($reflectProperties))
                    {
                        $reflector = new AmfReflector($object);
                        $reflectProperties = $reflector->annotations;
                    }
                }
            }

            $this->_referenceDefinitions[$className] = [
                        'id'               => count($this->_referenceDefinitions),
                        'encoding'         => $encoding,
                        'propertyNames'    => $propertyNames,
                    ];

            if(!empty($reflectProperties))
                $this->_referenceDefinitions[$className]['reflectProperties'] = $reflectProperties;

            $traitsInfo = Constants::AMF3_OBJECT_ENCODING;
            $traitsInfo |= $encoding << 2;
            $traitsInfo |= (count($propertyNames) << 4);

            $writeTraits = true;
        }

        $this->writeInteger($traitsInfo);

        if($writeTraits){
            $this->writeString($className);
            foreach ($propertyNames as $key) {
                $this->writeString($key);
            }
        }

        try
        {
            switch($encoding) {
                case Constants::ET_PROPLIST:
                    //Write the sealed values to the output stream.
                    foreach ($propertyNames as $key)
                    {
                        $markerType = null;
                        $extraData = false;
                        if(!empty($reflectProperties) && !is_null($object->$key))
                        {
                            if(array_key_exists($key, $reflectProperties))
                            {
                                if($reflectProperties[$key]['isVector'])
                                {
                                    $markerType = $reflectProperties[$key]['vectorElementType'];
                                    $extraData = [
                                        'elementType' => $reflectProperties[$key]['typeName'],
                                        'fixed' => $reflectProperties[$key]['isFixedVector'],
                                    ];
                                }
                            }
                        }

                        $this->writeTypeMarker($object->$key, $markerType, $extraData);
                    }
                    break;
                case Constants::ET_DYNAMIC:
                    //Write the sealed values to the output stream.
                    foreach ($propertyNames as $key) {
                        $this->writeTypeMarker($object->$key);
                    }

                    //Write remaining properties
                    foreach($object as $key => $value){
                        if(!in_array($key,$propertyNames) && $key[0] != "_"){
                            $this->writeString($key);
                            $this->writeTypeMarker($value);
                        }
                    }

                    //Write an empty string to end the dynamic part
                    $this->writeString($this->_strEmpty);
                    break;
                case Constants::ET_EXTERNAL:
                    throw new AmfException('External Object Encoding not implemented');
                    break;
                default:
                    throw new AmfException('Unknown Object Encoding type: ' . $encoding);
            }
        }
        catch (\Exception $e)
        {
            throw new AmfException('Unable to writeObject output: ' . $e->getMessage(), 0, $e);
        }

        return $this;
    }
}
