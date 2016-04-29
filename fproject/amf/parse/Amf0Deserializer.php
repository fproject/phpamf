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

use fproject\common\utils\XmlSecurity;
use fproject\amf\value\messaging\ArrayCollection;
use fproject\amf\Constants;

/**
 * Read an AMF0 input stream and convert it into PHP data types
 *
 * @todo       Implement Typed Object Class Mapping
 * @todo       Class could be implemented as Factory Class with each data type it's own class
 */
class Amf0Deserializer extends Deserializer
{
    /**
     * An array of objects used for recursively deserializing an object.
     * @var array
     */
    protected $_reference = [];

    /**
     * If AMF3 serialization occurs, update to AMF0 0x03
     *
     * @var int
     */
    protected $_objectEncoding = Constants::AMF0_OBJECT_ENCODING;

    /**
     * Read AMF markers and dispatch for deserialization
     *
     * Checks for AMF marker types and calls the appropriate methods
     * for deserializing those marker types. Markers are the data type of
     * the following value.
     *
     * @param  integer $typeMarker
     * @return mixed whatever the data type is of the marker in php
     * @throws \fproject\amf\AmfException for invalid type
     */
    public function readTypeMarker($typeMarker = null)
    {
        if ($typeMarker === null) {
            $typeMarker = $this->_stream->readByte();
        }

        switch($typeMarker) {
            // number
            case Constants::AMF0_NUMBER:
                return $this->_stream->readDouble();

            // boolean
            case Constants::AMF0_BOOLEAN:
                return (boolean) $this->_stream->readByte();

            // string
            case Constants::AMF0_STRING:
                return $this->_stream->readUTF();

            // object
            case Constants::AMF0_OBJECT:
                return $this->readObject();

            // null
            case Constants::AMF0_NULL:
                return null;

            // undefined
            case Constants::AMF0_UNDEFINED:
                return null;

            // Circular references are returned here
            case Constants::AMF0_REFERENCE:
                return $this->readReference();

            // mixed array with numeric and string keys
            case Constants::AMF0_MIXEDARRAY:
                return $this->readMixedArray();

            // array
            case Constants::AMF0_ARRAY:
                return $this->readArray();

            // date
            case Constants::AMF0_DATE:
                return $this->readDate();

            // longString  strlen(string) > 2^16
            case Constants::AMF0_LONGSTRING:
                return $this->_stream->readLongUTF();

            //internal AS object,  not supported
            case Constants::AMF0_UNSUPPORTED:
                return null;

            // XML
            case Constants::AMF0_XML:
                return $this->readXmlString();

            // typed object ie Custom Class
            case Constants::AMF0_TYPEDOBJECT:
                return $this->readTypedObject();

            //AMF3-specific
            case Constants::AMF0_AMF3:
                return $this->readAmf3TypeMarker();

            default:
                throw new \fproject\amf\AmfException('Unsupported marker type: ' . $typeMarker);
        }
    }

    /**
     * Read AMF objects and convert to PHP objects
     *
     * Read the name value pair objects form the php message and convert them to
     * a php object class.
     *
     * Called when the marker type is 3.
     *
     * @param  array|null $object
     * @return object
     */
    public function readObject($object = null)
    {
        if ($object === null) {
            $object = [];
        }

        while (true) {
            $key        = $this->_stream->readUTF();
            $typeMarker = $this->_stream->readByte();
            if ($typeMarker != Constants::AMF0_OBJECTTERM ){
                //Recursivly call readTypeMarker to get the types of properties in the object
                $object[$key] = $this->readTypeMarker($typeMarker);
            } else {
                //encountered AMF object terminator
                break;
            }
        }
        $this->_reference[] = $object;
        return (object) $object;
    }

    /**
     * Read reference objects
     *
     * Used to gain access to the private array of reference objects.
     * Called when marker type is 7.
     *
     * @return object
     * @throws \fproject\amf\AmfException for invalid reference keys
     */
    public function readReference()
    {
        $key = $this->_stream->readInt();
        if (!array_key_exists($key, $this->_reference)) {
            throw new \fproject\amf\AmfException('Invalid reference key: '. $key);
        }
        return $this->_reference[$key];
    }

    /**
     * Reads an array with numeric and string indexes.
     *
     * Called when marker type is 8
     *
     * @todo   As of Flash Player 9 there is not support for mixed typed arrays
     *         so we handle this as an object. With the introduction of vectors
     *         in Flash Player 10 this may need to be reconsidered.
     * @return array
     */
    public function readMixedArray()
    {
        $length = $this->_stream->readLong();
        return $this->readObject();
    }

    /**
     * Converts numerically indexed actiosncript arrays into php arrays.
     *
     * Called when marker type is 10
     *
     * @return array
     */
    public function readArray()
    {
        $length = $this->_stream->readLong();
        $array = [];
        while ($length--) {
            $array[] = $this->readTypeMarker();
        }
        return $array;
    }

    /**
     * Convert AS Date to date-time string
     *
     * 2014/05/24: Bui Sy Nguyen <nguyenbs@projectkit.net> modified to
     * use date-time string instead of Zend_Date
     * @return DateTime the date-time object
     */
    public function readDate()
    {
        // get the unix time stamp. Not sure why ActionScript does not use
        // milliseconds
        //$timestamp = floor($this->_stream->readDouble() / 1000);
        $timestamp = new DateTime();
        $timestamp->setTimestamp(floor($this->_stream->readDouble() / 1000));

        // The timezone offset is never returned to the server; it is always 0,
        // so read and ignore.
        $offset = $this->_stream->readInt();

        //require_once 'Zend/Date.php';
        //$date   = new Zend_Date($timestamp);
        //return $date;

        //return $timestamp->format('Y-m-d H:i:s');
        return $timestamp;
    }

    /**
     * Convert XML to SimpleXml
     * If user wants DomDocument they can use dom_import_simplexml
     *
     * @return SimpleXMLElement|DomDocument|boolean
     */
    public function readXmlString()
    {
        $string = $this->_stream->readLongUTF();
        return XmlSecurity::scan($string); //simplexml_load_string($string);
    }

    /**
     * Read Class that is to be mapped to a server class.
     *
     * Commonly used for Value Objects on the server
     *
     * @todo   implement Typed Class mapping
     * @return object|array
     * @throws \fproject\amf\AmfException if unable to load type
     */
    public function readTypedObject()
    {
        // get the remote class name
        $className = $this->_stream->readUTF();
        $loader = TypeLoader::loadType($className);
        $returnObject = new $loader();
        $properties = get_object_vars($this->readObject());
        foreach($properties as $key=>$value) {
            if($key) {
                $returnObject->$key = $value;
            }
        }
        if($returnObject instanceof ArrayCollection) {
            $returnObject = get_object_vars($returnObject);
        }
        return $returnObject;
    }

    /**
     * AMF3 data type encountered load AMF3 Deserializer to handle
     * type markers.
     *
     * @return string
     */
    public function readAmf3TypeMarker()
    {
        $deserializer = new Amf3Deserializer($this->_stream);
        $this->_objectEncoding = Constants::AMF3_OBJECT_ENCODING;
        return $deserializer->readTypeMarker();
    }

    /**
     * Return the object encoding to check if an AMF3 object
     * is going to be return.
     *
     * @return int
     */
    public function getObjectEncoding()
    {
        return $this->_objectEncoding;
    }
}
