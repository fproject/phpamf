<?php
require_once 'Zend/Amf/Parse/TypeLoader.php';
require_once 'ContactElt.php';
require_once 'Container.php';
//require_once 'Zend/Amf/Parse/Amf3/Serializer';
//require_once 'Zend/Amf/Parse/OutputStream';

class SerializerTest extends PHPUnit_Framework_TestCase
{
    /** @var  Zend_Amf_Parse_Serializer $_serializer */
    protected $_serializer;

    public function setUp()
    {
        date_default_timezone_set('America/Chicago');
        Zend_Amf_Parse_TypeLoader::resetMap();
    }

    public function tearDown()
    {
        unset($this->_serializer);
    }

    public function testWriteTypedObjectVector()
    {
        $data = [
            new ContactElt(["id"=>10,"firstname"=>"First name 1","lastname"=>"Last name 1","email"=>"email1@email.com","mobile"=>"0912345678"]),
            new ContactElt(["id"=>15,"firstname"=>"First name 2","lastname"=>"Last name 2","email"=>"email2@email.com","mobile"=>"0912345679"]),
            new ContactElt(["id"=>17,"firstname"=>"First name 3","lastname"=>"Last name 3","email"=>"email2@email.com","mobile"=>"0912345680"]),
        ];

        $container = new Container();
        $container->data = $data;

        $outputStream = new Zend_Amf_Parse_OutputStream();
        $serializer = new Zend_Amf_Parse_Amf3_Serializer($outputStream);
        $serializer->writeTypeMarker($container);
        // Load the expected binary.
        $mockFile = file_get_contents(dirname(__FILE__) .'/Parse/mock/amf3TypedVector.bin');

        // Check that the serialized binary data matches the expected serialized value
        $this->assertEquals($mockFile, $outputStream->getStream());
    }
}