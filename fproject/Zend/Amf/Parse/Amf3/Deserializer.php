<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Amf
 * @subpackage Parse_Amf3
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Amf_Parse_Deserializer */
require_once 'Zend/Amf/Parse/Deserializer.php';

/** Zend_Xml_Security */
require_once 'Zend/Xml/Security.php';

/** Zend_Amf_Parse_TypeLoader */
require_once 'Zend/Amf/Parse/TypeLoader.php';

/** Zend_Amf_Parse_TypeLoader */
require_once 'Zend/Amf/Value/TraitsInfo.php';

/**
 * Read an AMF3 input stream and convert it into PHP data types.
 *
 * @todo       readObject to handle Typed Objects
 * @todo       readXMLStrimg to be implemented.
 * @todo       Class could be implemented as Factory Class with each data type it's own class.
 * @package    Zend_Amf
 * @subpackage Parse_Amf3
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Parse_Amf3_Deserializer extends Zend_Amf_Parse_Deserializer
{
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
     * An array of TraitsInfo objects
     * @var array
     */
    protected $_traitsTable = [];

    /**
     * Read AMF markers and dispatch for deserialization
     *
     * Checks for AMF marker types and calls the appropriate methods
     * for deserializing those marker types. markers are the data type of
     * the following value.
     *
     * @param  integer $typeMarker
     * @return mixed Whatever the corresponding PHP data type is
     * @throws Zend_Amf_Exception for unidentified marker type
     */
    public function readTypeMarker($typeMarker = null)
    {
        if(null === $typeMarker) {
            $typeMarker = $this->_stream->readByte();
        }

        switch($typeMarker) {
            case Zend_Amf_Constants::AMF3_UNDEFINED:
                return null;
            case Zend_Amf_Constants::AMF3_NULL:
                return null;
            case Zend_Amf_Constants::AMF3_BOOLEAN_FALSE:
                return false;
            case Zend_Amf_Constants::AMF3_BOOLEAN_TRUE:
                return true;
            case Zend_Amf_Constants::AMF3_INTEGER:
                return $this->readInteger();
            case Zend_Amf_Constants::AMF3_NUMBER:
                return $this->_stream->readDouble();
            case Zend_Amf_Constants::AMF3_STRING:
                return $this->readString();
            case Zend_Amf_Constants::AMF3_DATE:
                return $this->readDate();
            case Zend_Amf_Constants::AMF3_ARRAY:
                return $this->readArray();
            case Zend_Amf_Constants::AMF3_OBJECT:
                return $this->readObject();
            case Zend_Amf_Constants::AMF3_XML:
            case Zend_Amf_Constants::AMF3_XMLSTRING:
                return $this->readXmlString();
            case Zend_Amf_Constants::AMF3_BYTEARRAY:
                return $this->readString();
            case Zend_Amf_Constants::AMF3_VECTOR_INT:
            case Zend_Amf_Constants::AMF3_VECTOR_UINT:
            case Zend_Amf_Constants::AMF3_VECTOR_NUMBER:
            case Zend_Amf_Constants::AMF3_VECTOR_OBJECT:
                return $this->readVector($typeMarker);
            case Zend_Amf_Constants::AMF3_DICTIONARY:
            default:
                $this->throwZendException('Unsupported type marker: {0}',[$typeMarker]);
        }
    }

    /**
     * Read and deserialize an integer
     *
     * AMF 3 represents smaller integers with fewer bytes using the most
     * significant bit of each byte. The worst case uses 32-bits
     * to represent a 29-bit number, which is what we would have
     * done with no compression.
     * - 0x00000000 - 0x0000007F : 0xxxxxxx
     * - 0x00000080 - 0x00003FFF : 1xxxxxxx 0xxxxxxx
     * - 0x00004000 - 0x001FFFFF : 1xxxxxxx 1xxxxxxx 0xxxxxxx
     * - 0x00200000 - 0x3FFFFFFF : 1xxxxxxx 1xxxxxxx 1xxxxxxx xxxxxxxx
     * - 0x40000000 - 0xFFFFFFFF : throw range exception
     *
     * 0x04 -> integer type code, followed by up to 4 bytes of data.
     *
     * Parsing integers on OSFlash for the AMF3 integer data format:
     * @link http://osflash.org/amf3/parsing_integers
     * @return int|float|null
     */
    public function readInteger()
    {
        $count        = 1;
        $intReference = $this->_stream->readByte();
        $result       = 0;
        while ((($intReference & 0x80) != 0) && $count < 4) {
            $result       <<= 7;
            $result        |= ($intReference & 0x7f);
            $intReference   = $this->_stream->readByte();
            $count++;
        }
        if ($count < 4) {
            $result <<= 7;
            $result  |= $intReference;
        } else {
            // Use all 8 bits from the 4th byte
            $result <<= 8;
            $result  |= $intReference;

            // Check if the integer should be negative
            if (($result & 0x10000000) != 0) {
                //and extend the sign bit
                $result |= ~0xFFFFFFF;
            }
        }
        return $result;
    }

    /**
     * Read and deserialize a string
     *
     * Strings can be sent as a reference to a previously
     * occurring String by using an index to the implicit string reference table.
     * Strings are encoding using UTF-8 - however the header may either
     * describe a string literal or a string reference.
     *
     * - string = 0x06 string-data
     * - string-data = integer-data [ modified-utf-8 ]
     * - modified-utf-8 = *OCTET
     *
     * @return String
     */
    public function readString()
    {
        $stringReference = $this->readInteger();

        //Check if this is a reference string
        if (($stringReference & 0x01) == 0) {
            // reference string
            $stringReference = $stringReference >> 1;
            if ($stringReference >= count($this->_referenceStrings)) {
                $this->throwZendException('Undefined string reference: {0}',[$stringReference]);
            }
            // reference string found
            return $this->_referenceStrings[$stringReference];
        }

        $length = $stringReference >> 1;
        if ($length) {
            $string = $this->_stream->readBytes($length);
            $this->_referenceStrings[] = $string;
        } else {
            $string = "";
        }
        return $string;
    }

    /**
     * Read and de-serialize a date
     *
     * Data is the number of milliseconds elapsed since the epoch
     * of midnight, 1st Jan 1970 in the UTC time zone.
     * Local time zone information is not sent to flash.
     *
     * - date = 0x08 integer-data [ number-data ]
     *
     * @throws Zend_Amf_Exception
     * @return DateTime date-time object
     *
     * 2014/05/24: Bui Sy Nguyen <nguyenbs@projectkit.net> modified to use
     * date-time string instead of Zend_Date
     */
    public function readDate()
    {
        $dateReference = $this->readInteger();

        $refObj = $this->getReferenceObject($dateReference);
        if($refObj !== false)
            return $refObj;

        //$timestamp = floor($this->_stream->readDouble() / 1000);
        $timestamp = new DateTime();
        $timestamp->setTimestamp(floor($this->_stream->readDouble() / 1000));

        $this->_referenceObjects[] = $timestamp;

        return $timestamp;
    }

    /**
     * Read amf array to PHP array
     *
     * - array = 0x09 integer-data ( [ 1OCTET *amf3-data ] | [OCTET *amf3-data 1] | [ OCTET *amf-data ] )
     *
     * @return array
     */
    public function readArray()
    {
        $arrayReference = $this->readInteger();

        $refObj = $this->getReferenceObject($arrayReference);
        if($refObj !== false)
            return $refObj;

        // Create a holder for the array in the reference list
        $data = [];
        $this->_referenceObjects[] =& $data;
        $key = $this->readString();

        // Iterating for string based keys.
        while ($key != '') {
            $data[$key] = $this->readTypeMarker();
            $key = $this->readString();
        }

        $arrayReference = $arrayReference >>1;

        //We have a dense array
        for ($i=0; $i < $arrayReference; $i++) {
            $data[] = $this->readTypeMarker();
        }

        return $data;
    }

    /**
     * Read an amf Vector to PHP array
     *
     * @return array
     * @throws Zend_Amf_Exception
     */
    public function readVector($type)
    {
        $ref = $this->readInteger();

        $refObj = $this->getReferenceObject($ref);
        if($refObj !== false)
            return $refObj;

        $len = ($ref >> 1);
        $fixed = (bool)$this->_stream->readByte();

        switch ($type)
        {
            case Zend_Amf_Constants::AMF3_VECTOR_INT:
                $eltSize = 4;
                $numberFormat = "ival";
                break;
            case Zend_Amf_Constants::AMF3_VECTOR_UINT:
                $eltSize = 4;
                $numberFormat = "Ival";
                break;
            case Zend_Amf_Constants::AMF3_VECTOR_NUMBER:
                $eltSize = 8;
                $numberFormat = "dval";
                break;
            case Zend_Amf_Constants::AMF3_VECTOR_OBJECT:
                return $this->readObjectVector($len, $fixed);
            default:
                // Unknown vector type tag {type}
                $this->throwZendException('Undefined vector type: {0}',[$type]);
        }
        $bigEndian = self::isSystemBigEndian();

        return $this->readNumericVector($len, $fixed, $eltSize, $numberFormat, $bigEndian);
    }

    /**
     * Read amf Vector.<int> to PHP array
     * @param int $len
     * @param bool $fixed
     * @return array
     * @throws Zend_Amf_Exception
     */
    public function readNumericVector($len, $fixed, $eltSize, $numberFormat,$bigEndian)
    {
        if ($fixed)
        {
            $vector = array_fill(0, $len, 0);
        }
        else
        {
            $vector = [];
        }

        // Create a holder for the array in the reference list
        $this->_referenceObjects[] =& $vector;

        for ($i = 0; $i < $len; $i++)
        {
            $bytes = $this->_stream->readBytes($eltSize);
            if ($bigEndian)
                $bytes = strrev($bytes);
            $array = unpack($numberFormat, $bytes);

            // Unsigned Integers don't work in PHP amazingly enough. If you go into the "upper" region
            // on the Actionscript side, this will come through as a negative without this cast to a float
            // see http://php.net/manual/en/language.types.integer.php
            $value = $array["val"];
            if ($numberFormat === "Ival")
                $value = floatval(sprintf('%u', $value));

            if($fixed)
                $vector[$i] = $value;
            else
                $vector[] = $value;
        }

        return $vector;
    }

    /**
     * Read amf Vector.<Object> to PHP array
     * @param int $len
     * @param bool $fixed
     * @return array
     * @throws Zend_Amf_Exception
     */
    public function readObjectVector($len, $fixed)
    {
        if($len == 0)
            return [];

        $elementClass = $this->readString();

        if ($fixed)
        {
            $vector = array_fill(0, $len, null);
        }
        else
        {
            $vector = [];
        }

        // Create a holder for the array in the reference list
        $this->_referenceObjects[] =& $vector;

        for ($i = 0; $i < $len; $i++)
        {
            $value = $this->readTypeMarker();

            if($fixed)
                $vector[$i] = $value;
            else
                $vector[] = $value;
        }

        return $vector;
    }

    /**
     * Read amf Dictionary to PHP array
     * @return array
     * @throws Zend_Amf_Exception
     */
    public function readDictionary()
    {
        $ref = $this->readInteger();

        $refObj = $this->getReferenceObject($ref);
        if($refObj !== false)
            return $refObj;

        // usingWeakTypes - irrelevant in PHP.
        $this->_stream->readInt();

        // Create a holder for the array in the reference list
        $data = [];
        $this->_referenceObjects[] =& $data;

        $ref = $ref >>1;//Get the length

        //We have a dense array
        for ($i=0; $i < $ref; $i++) {
            $data[] = ['key'=>$this->readTypeMarker(),'value'=>$this->readTypeMarker()];
        }

        return $data;
    }

    /**
     * Read an object from the AMF stream and convert it into a PHP object
     *
     * @return object|array
     */
    public function readObject()
    {
        $ref   = $this->readInteger();

        $refObj = $this->getReferenceObject($ref);
        if($refObj !== false)
            return $refObj;

        $ti = $this->readTraits($ref);
        $className = $ti->getClassName();
        $externalizable = $ti->isExternalizable();

        // Prepare the parameters for createObjectInstance(). Use an array as a holder
        // to simulate two 'by-reference' parameters className and (initially null) proxy
        $instance = $this->createObjectInstance($className);

        $object = $instance['object'];
        // Retrieve any changes to the className and the proxy parameters
        $className = $instance['className'];

        // Add the Object to the reference table
        $this->_referenceObjects[] = $object;

        if ($externalizable)
        {
            $this->readExternalizable($className, $object);
        }
        else
        {
            $len = $ti->length();

            for ($i = 0; $i < $len; $i++)
            {
                $propName = $ti->getProperty($i);
                $value = $this->readTypeMarker();
                $object->$propName = $value;
            }

            if ($ti->isDynamic())
            {
                do
                {
                    $name = $this->readString();
                    if($name != '')
                    {
                        $value = $this->readTypeMarker();
                        $object->$name = $value;
                    }
                } while ($name != '');
            }
        }

        return $object;
    }

    /**
     * @param $className
     * @return array
     * @throws Zend_Amf_Exception
     */
    protected function createObjectInstance($className)
    {
        // We now have the object traits defined in variables. Time to go to work:
        if (!$className){
            // No class name generic object
            $returnObject = new stdClass();
        }
        else
        {
            // Defined object
            // Typed object lookup against registered classname maps
            if ($loader = Zend_Amf_Parse_TypeLoader::loadType($className)) {
                $returnObject = new $loader();
                $className = $loader;
            }
            else
            {
                //user defined typed object
                $this->throwZendException('Typed object not found: {0}',[$className]);
            }
        }
        return ['object' => $returnObject, 'className' => $className];
    }

    // Read Externalizable object such as {ArrayCollection} and {ObjectProxy}
    protected function readExternalizable($className, $object)
    {
        $object->externalizedData = $this->readTypeMarker();
    }

    private function readTraits($ref)
    {
        if (($ref & 3) == 1) // This is a reference
            return $this->_traitsTable[$ref >> 2];

        $externalizable = (($ref & 4) == 4);
        $dynamic = (($ref & 8) == 8);
        $count = ($ref >> 4); /* uint29 */
        $className = $this->readString();

        $ti = new Zend_Amf_Value_TraitsInfo($className, $dynamic, $externalizable, []);

        // Remember Trait Info
        $this->_traitsTable[] = $ti;

        for ($i = 0; $i < $count; $i++)
        {
            $propName = $this->readString();
            $ti->addProperty($propName);
        }

        return $ti;
    }

    /**
     * Convert XML to SimpleXml
     * If user wants DomDocument they can use dom_import_simplexml
     *
     * @return SimpleXMLElement|DOMDocument|bool
     */
    public function readXmlString()
    {
        $xmlReference = $this->readInteger();

        $length = $xmlReference >> 1;
        $string = $this->_stream->readBytes($length);
        return Zend_Xml_Security::scan($string);
    }

    /**
     * @param $message
     * @param array $params
     * @throws Zend_Amf_Exception
     */
    private function throwZendException($message, $params=[])
    {
        require_once 'Zend/Amf/Exception.php';
        for($i=0; $i<count($params); $i++)
        {
            $message = str_replace('{'.$i.'}',$params[$i], $message);
        }
        throw new Zend_Amf_Exception($message);
    }

    private function getReferenceObject($refMarker)
    {
        if (($refMarker & 0x01) == 0) {
            $refMarker = $refMarker >> 1;
            if ($refMarker>=count($this->_referenceObjects)) {
                $this->throwZendException('Undefined object reference: {0}',[$refMarker]);
            }
            return $this->_referenceObjects[$refMarker];
        }
        return false;
    }

    /**
     * Looks if the system is Big Endain or not
     * @return bool
     */
    static private function isSystemBigEndian() {
        $tmp = pack('d', 1); // determine the multi-byte ordering of this machine temporarily pack 1
        return ($tmp == "\0\0\0\0\0\0\360\77");
    }
}
