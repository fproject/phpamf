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
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Amf/Request.php';
require_once 'Zend/Amf/Parse/TypeLoader.php';
require_once 'Contact.php';

use fproject\amf\value\messaging\CommandMessage;
use fproject\amf\value\messaging\RemotingMessage;
use fproject\amf\value\MessageHeader;
use fproject\amf\value\MessageBody;

/**
 * Test case for Zend_Amf_Request
 *
 * @category   Zend
 * @package    Zend_Amf
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Amf
 */
class Zend_Amf_RequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * Zend_Amf_Request object
     * @var Zend_Amf_Request
     */
    protected $_request;

    /**
     * Setup environment
     */
    public function setUp()
    {
        date_default_timezone_set("America/Chicago");
        Zend_Amf_Parse_TypeLoader::resetMap();
        $this->_request = new Zend_Amf_Request();
    }

    /**
     * Teardown environment
     */
    public function tearDown()
    {
        unset($this->_request);
    }

    /**
     * ActionScript undef to PHP null
     *
     */
    public function testAmf3RemoteObjectUndefParameterDeserializedToNativePhpNull()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/undefAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recievedpbs
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('returnUndefined', $message->operation);
        $this->assertEquals('RoundTrip', $message->source);
        $data = $message->body;
        // Make sure that we are dealing with a PHP null
        $this->assertTrue(is_null($data[0]));
    }

    /**
     * ActionScript String to PHP String
     *
     */
    public function testAmf3RemoteObjectStringParameterDeserializedToNativePhpString()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/stringAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('returnString', $message->operation);
        $this->assertEquals('RoundTrip', $message->source);
        $data = $message->body;
        // Make sure that we are dealing with a PHP string
        $this->assertTrue(is_string($data[0]));
        // Make sure that the string was deserialized properly and check its value
        $this->assertEquals('abcdefghijklmpqrstuvwxyz', $data[0]);
    }

    /**
     * ActionScript Array to Php Array
     *
     */
    public function testAmf3RemoteObjectArrayParameterDeserializedToNativePhpArray()
    {
         $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/arrayAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('returnArray', $message->operation);
        $this->assertEquals('RoundTrip', $message->source);
        $data = $message->body;
        // Make sure that we are dealing with a PHP array
        $this->assertTrue(is_array($data[0]));
        // Make sure that the array was deserialized properly and check its value
        $this->assertEquals('a', $data[0][0]);
        $this->assertEquals('g', $data[0][6]);
    }

    /**
     * ActionScript Numnber to PHP float
     *
     */
    public function testAmf3NumberParameterDeserializedToNativePhpFloat()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/numberAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('returnNumber', $message->operation);
        $this->assertEquals('RoundTrip', $message->source);
        $data = $message->body;
        // Make sure that we are dealing with a PHP float
        $this->assertTrue(is_float($data[0]));
        // Make sure that the float was deserialized properly and check its value
        $this->assertEquals(31.57, $data[0]);
    }

    /**
     * ActionScript Date to Php DateTime
     *
     */
    public function testAmf3DateParameterDeserializedToNativeDateTime()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/dateAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('returnDate', $message->operation);
        $this->assertEquals('RoundTrip', $message->source);
        $data = $message->body;
        // Make sure that the array was deserialized properly and check its value
        $this->assertEquals(1978, $data[0]->format('Y'));

    }

    /**
     * ActionScript Date to Php DateTime
     *
     */
    public function testComplexTypedObjectParameterDeserializedToPhp()
    {
        require_once "WorkCalendar.php";
        Zend_Amf_Parse_TypeLoader::$classMap['net.fproject.calendar.WorkCalendar'] = 'WorkCalendar';

        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/complexTypedObjectAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        /** @var MessageBody[] $bodies */
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        /** @var RemotingMessage $message */
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('save', $message->operation);
        $this->assertEquals('SampleService', $message->source);
        $data = $message->body;
        $this->assertTrue(is_array($data), 'Deserialized body must be an array');
        $data = $data[0];
        $this->assertInstanceOf('WorkCalendar', $data, "must be an instance of WorkCalendar");
    }

    /**
     * ActionScript Date to Php DateTime
     *
     */
    public function testComplexTypedObjectParameterDeserializedToPhpUsingPSR0()
    {
        Zend_Amf_Parse_TypeLoader::$classMap['net.fproject.calendar.WorkCalendar'] = 'fproject\calendar\WorkCalendar';
        Zend_Amf_Parse_TypeLoader::$classMap['net.fproject.calendar.Period'] = 'fproject\calendar\Period';
        Zend_Amf_Parse_TypeLoader::$classMap['net.fproject.calendar.WorkShift'] = 'fproject\calendar\WorkShift';
        Zend_Amf_Parse_TypeLoader::$classMap['net.fproject.calendar.WeekDay'] = 'fproject\calendar\WeekDay';

        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/complexTypedObjectAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        /** @var MessageBody[] $bodies */
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        /** @var RemotingMessage $message */
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('save', $message->operation);
        $this->assertEquals('SampleService', $message->source);
        $data = $message->body;
        $this->assertTrue(is_array($data), 'Deserialized body must be an array');
        /** @var fproject\calendar\WorkCalendar $data */
        $data = $data[0];
        $this->assertInstanceOf('fproject\calendar\WorkCalendar', $data, "must be instance of fproject\\calendar\\WorkCalendar");
        $this->assertInstanceOf('fproject\calendar\WorkShift', $data->defaultWorkShifts[0], "must be instance of fproject\\calendar\\WorkShift");
        $this->assertInstanceOf('fproject\calendar\WeekDay', $data->weekDays[0], "must be instance of fproject\\calendar\\WeekDay");
        $this->assertInstanceOf('fproject\calendar\WorkShift', $data->weekDays[2]->workShifts[0], "must be instance of fproject\\calendar\\WorkShift");
    }

    /**
     * ActionScript mx.messaging.messages.CommandMessage to CommandMessage
     *
     */
    public function testAmf3CommandMessageRequest()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/amf3CommandMessageRequest.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        /** @var MessageBody[] $bodies */
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        /** @var RemotingMessage $message */
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof CommandMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals(5, $message->operation);
    }

    /**
     * ActionScript Vector.<Contact> to PHP Contact[] array
     *
     */
    public function testTypedObjectVectorParameterDeserializedToPHPArray()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/typedObjectVectorAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        /** @var MessageBody[] $bodies */
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        /** @var RemotingMessage $message */
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('save', $message->operation);
        $this->assertEquals('ContactService', $message->source);
        $data = $message->body;
        // Make sure that the array was deserialized properly and check its value
        $this->assertTrue(is_array($data), 'Deserialized body must be an array');
        $data = $data[0];
        $this->assertTrue(is_array($data), 'Deserialized vector data must be an array');
        foreach($data as $element)
        {
            $this->assertInstanceOf('Contact', $element, 'Deserialized vector element must be an instance of Contact');
        }
    }

    /**
     * ActionScript Vector.<int> to PHP int[] array
     *
     */
    public function testIntVectorParameterDeserializedToPHPArray()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/intVectorAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        /** @var MessageBody[] $bodies */
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        /** @var RemotingMessage $message */
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('save', $message->operation);
        $this->assertEquals('ContactService', $message->source);
        $data = $message->body;
        // Make sure that the array was deserialized properly and check its value
        $this->assertTrue(is_array($data), 'Deserialized body must be an array');
        $data = $data[0];
        $this->assertTrue(is_array($data), 'Deserialized vector data must be an array');
        foreach($data as $element)
        {
            $this->assertTrue(is_int($element), 'Deserialized vector element must be an int');
        }
    }

    /**
     * ActionScript Vector.<uint> to PHP float[] array
     *
     */
    public function testUIntVectorParameterDeserializedToPHPArray()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/uintVectorAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        /** @var MessageBody[] $bodies */
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        /** @var RemotingMessage $message */
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('save', $message->operation);
        $this->assertEquals('ContactService', $message->source);
        $data = $message->body;
        // Make sure that the array was deserialized properly and check its value
        $this->assertTrue(is_array($data), 'Deserialized body must be an array');
        $data = $data[0];
        $this->assertTrue(is_array($data), 'Deserialized vector data must be an array');
        foreach($data as $element)
        {
            $this->assertTrue(is_float($element) || ctype_digit((string)$element), 'Deserialized vector element must be an unsigned int');
        }
    }

    /**
     * ActionScript Vector.<Number> to PHP float[] array
     *
     */
    public function testNumberVectorParameterDeserializedToPHPArray()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/numberVectorAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        /** @var MessageBody[] $bodies */
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        /** @var RemotingMessage $message */
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('save', $message->operation);
        $this->assertEquals('ContactService', $message->source);
        $data = $message->body;
        // Make sure that the array was deserialized properly and check its value
        $this->assertTrue(is_array($data), 'Deserialized body must be an array');
        $data = $data[0];
        $this->assertTrue(is_array($data), 'Deserialized vector data must be an array');
        foreach($data as $element)
        {
            $this->assertTrue(is_null($element) || is_float($element), 'Deserialized vector element must be a float');
        }

    }

    /**
     * Try and read in the largest Amf Integer to PHP int
     *
     */
    public function testAmf3LargeIntParameterDeserializedToNativePhpInt()
    {
         $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/largeIntAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('returnInt', $message->operation);
        $this->assertEquals('RoundTrip', $message->source);
        $data = $message->body;
        // Make sure that we are dealing with a PHP array
        $this->assertTrue(is_int($data[0]));
        // Make sure that the array was deserialized properly and check its value
        $this->assertEquals(268435455, $data[0]);
    }

    /**
     * Read boolean true and convert it to php boolean true
     *
     */
    public function testAmf3BoolTrueParameterDeserializedToNativePhpBool()
    {
         $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/boolTrueAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('returnBool', $message->operation);
        $this->assertEquals('RoundTrip', $message->source);
        $data = $message->body;
        // Make sure that we are dealing with a PHP array
        $this->assertTrue(is_bool($data[0]));
        // Make sure that the Bool was deserialized properly and check its value
        $this->assertEquals(true, $data[0]);
    }

    /**
     * Convert boolean false to php boolean false.
     *
     */
    public function testAmf3BoolFalseParameterDeserializedToNativePhpBool()
    {
         $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/boolFalseAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('returnBool', $message->operation);
        $this->assertEquals('RoundTrip', $message->source);
        $data = $message->body;
        // Make sure that we are dealing with a PHP array
        $this->assertTrue(is_bool($data[0]));
        // Make sure that the Bool was deserialized properly and check its value
        $this->assertEquals(false, $data[0]);
    }

    public function testAmf3XmlParameterDeserializedToNativePhpSimpleXml()
    {
         $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/xmlAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure the encoding type is properly set.
        $this->assertEquals(0x03, $this->_request->getObjectEncoding());
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $message = $bodies[0]->getData();
        $this->assertTrue($message instanceof RemotingMessage);
        // Make sure that our endpoint is properly set.
        $this->assertEquals('returnXml', $message->operation);
        $this->assertEquals('RoundTrip', $message->source);
        $data = $message->body;
        // Make sure that we are dealing with a PHP simpleXml element
        $this->assertTrue($data[0] instanceof SimpleXMLElement);
        // Make sure that the xml was deserialized properly and check its value
        $this->assertEquals('hello', (string) $data[0]->p);
    }

    public function testAmf3ByteArrayDeserializedToNativePhpString()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/byteArrayAmf3Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $requestBody = $this->_request->getAmfBodies();
        $this->assertTrue($requestBody[0] instanceof MessageBody);
        $data = $requestBody[0]->getData();
        // Make sure that we are dealing with a PHP string
        $this->assertTrue(is_string($data[0]));
        // Make sure that the string was deserialized properly and check its value
        $byteArray = file_get_contents(dirname(__FILE__) .'/Request/bytearray.bin');
        $this->assertEquals($byteArray, $data[0]);
    }

    /**
     * Actionscript String to PHP String
     *
     */
    public function testAmf0StringParameterDeserializedToNativePhpString()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/stringAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $requestBody = $this->_request->getAmfBodies();
        $this->assertTrue($requestBody[0] instanceof MessageBody);
        $this->assertEquals('RoundTrip.returnString', $requestBody[0]->getTargetURI());
        $data = $requestBody[0]->getData();
        // Make sure that we are dealing with a PHP string
        $this->assertTrue(is_string($data[0]));
        // Make sure that the string was deserialized properly and check its value
        $this->assertEquals('abcdefghijklmpqrstuvwxyz', $data[0]);
    }

    /**
     * ActionScript Object to PHP Object for Amf0
     *
     */
    public function testAmf0ObjectParameterDeserializedToNativePhpObject()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/objectAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that we are dealing with a PHP string
        // Make sure that the string was deserialized properly and check its value
        $this->assertEquals('foo', $data[0]->a);
        $this->assertEquals('bar', $data[0]->b);
    }

    /**
     * Test to make sure that a generic object as the first paramater does not crash
     * @group ZF-5346
     */
    public function testAmf0ObjectFirstParameter()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/objectFirstParamRequest.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that we are dealing with a PHP string
        // Make sure that the string was deserialized properly and check its value
        $this->assertEquals('foo', $data[0]->a);
        $this->assertEquals('bar', $data[0]->b);
        $this->assertEquals(1234, $data[1]);
    }

    /**
     * ActionScript Mixed Array to PHP Object for Amf0
     *
     */
    public function testAmf0MixedArrayParameterDeserializedToNativePhpObject()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/mixedArrayAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that the string was deserialized properly and check its value
        $this->assertTrue(array_key_exists(1, $data[0]));
        $this->assertEquals('two', $data[0]->two);
    }

    /**
     * ActionScript Numnber to PHP float
     *
     */
    public function testAmf0NumberParameterDeserializedToNativePhpFloat()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/numberAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that the string was deserialized properly and check its value
        $this->assertTrue(is_float($data[0]));
        $this->assertEquals(31.57, $data[0]);
    }

    /**
     * ActionScript Date to PHP DateTime
     *
     */
    public function testAmf0DateParameterDeserializedToNativePhpDateTime()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/dateAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that the string was deserialized properly and check its value
        $this->assertEquals('Oct', $data[0]->format('M'));
        $this->assertEquals(1978, $data[0]->format('Y'));
    }

    /**
     * ActionScript Integer to PHP int
     *
     */
    public function testAmf0IntParameterDeserializedToNativePhpint()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/intAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that the string was deserialized properly and check its value
        $this->assertEquals(268435456, $data[0]);
    }

    /**
     * Convert an Amf0 boolean true to php boolean
     *
     */
    public function testAmf0BoolTrueParameterDeserializedToNativePhpBool()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/boolTrueAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that the string was deserialized properly and check its value
        $this->assertTrue(is_bool($data[0]));
        $this->assertEquals(true, $data[0]);
    }

/**
     * Convert an Amf0 boolean false to php boolean
     *
     */
    public function testAmf0BoolFalseParameterDeserializedToNativePhpBool()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/boolFalseAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that the string was deserialized properly and check its value
        $this->assertTrue(is_bool($data[0]));
        $this->assertEquals(false, $data[0]);
    }

    public function testAmf0NullDeserializedToNativePhpNull()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/nullAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that the string was deserialized properly and check its value
        $this->assertTrue(is_null($data[0]));
    }

   public function testAmf0UndefinedDeserializedToNativePhpNull()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/undefinedAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that the string was deserialized properly and check its value
        $this->assertTrue(is_null($data[0]));
    }

    public function testAmf0XmlParameterDeserializedToNativePhpSimpleXml()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/xmlAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that we are dealing with a PHP simpleXml element
        $this->assertTrue($data[0] instanceof SimpleXMLElement);
        // Make sure that the xml was deserialized properly and check its value
        $this->assertEquals('hello', (string) $data[0]->p);
    }

    public function testAmf0ReferenceDeserialized()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/referenceAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that we are dealing with a PHP a number
        // Make sure that the xml was deserialized properly and check its value
        $this->assertEquals('foo', (string) $data[0]->a);
    }

    public function testAmf0TypedObjecDeserializedToNativePHPObject()
    {
        Zend_Amf_Parse_TypeLoader::setMapping("ContactVO","Contact");
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/typedObjectAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(0 , sizeof($this->_request->getAmfHeaders()));
        // Make sure that the message body was set after deserialization
        $this->assertEquals(1, sizeof($this->_request->getAmfBodies()));
        $bodies = $this->_request->getAmfBodies();
        $this->assertTrue($bodies[0] instanceof MessageBody);
        $data = $bodies[0]->getData();
        // Make sure that we are dealing with a PHP simpleXml element
        $this->assertTrue($data[0] instanceof Contact);
        // Make sure that the xml was deserialized properly and check its value
        $this->assertEquals('arnold', (string) $data[0]->lastname);
    }

    public function testAmf0TypedObjecDeserializedToNativePHPObjectUnknownType()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/bogusTypedObjectAmf0Request.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);

        $requestBodies = $this->_request->getAmfBodies();
        $messageBody   = reset($requestBodies);
        $data          = $messageBody->getData();
        $dataObject    = reset($data);

        $this->assertEquals('stdClass', get_class($dataObject));
    }

    /**
     * Test Amf0 credentials sent to the server
     *
     */
    public function testAmf0CredentialsInHeader()
    {
        $myRequest = file_get_contents(dirname(__FILE__) .'/Request/mock/credentialsheaderAmf0.bin');
        // send the mock object request to be deserialized
        $this->_request->initialize($myRequest);
        // Make sure that no headers where recieved
        $this->assertEquals(1 , sizeof($this->_request->getAmfHeaders()));
        $requestHeaders = $this->_request->getAmfHeaders();
        $this->assertTrue($requestHeaders[0] instanceof MessageHeader);
        $this->assertEquals('Credentials', $requestHeaders[0]->name);
        $this->assertFalse($requestHeaders[0]->mustRead);
        $data = $requestHeaders[0]->data;
        // Check the resulting header
        $this->assertEquals('admin', $data->userid);
        $this->assertEquals('pw123', $data->password);
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Amf_RequestTest::main') {
    Zend_Amf_RequestTest::main();
}

