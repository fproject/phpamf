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

use fproject\amf\value\MessageBody;
use fproject\amf\parse\TypeLoader;
use fproject\amf\Request;
use fproject\amf\Server;

/**
 * @category   Zend
 * @package    Zend_Amf
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Amf
 */
class Zend_Amf_ResourceTest extends PHPUnit_Framework_TestCase
{

    /**
     * Enter description here...
     *
     * @var Server
     */
    protected $_server;

    public function setUp()
    {
        $this->_server = new Server();
        $this->_server->setProduction(false);
        TypeLoader::resetMap();
    }

    protected function tearDown()
    {
        unset($this->_server);
    }

    protected function _callService($method, $class = 'Zend_Amf_Resource_testclass')
    {
        $request = new Request();
        $request->setObjectEncoding(0x03);
        $this->_server->setClass($class);
        $newBody = new MessageBody("$class.$method","/1",array("test"));
        $request->addAmfBody($newBody);
        $this->_server->handle($request);
        $response = $this->_server->getResponse();
        return $response;
    }

    public function testFile()
    {
        $resp = $this->_callService("returnFile");
        $this->assertContains("test data", $resp->getResponse());
    }

    /**
     * Defining new unknown resource type
     *
     * @expectException \fproject\amf\AmfException
     *
     */
    public function testCtxNoResource()
    {
        try {
            $this->_callService("returnCtx");
        } catch(\fproject\amf\AmfException $e) {
            $this->assertContains("Plugin by name 'StreamContext' was not found in the registry", $e->getMessage());
            return;
        }
        $this->fail("Failed to throw exception on unknown resource");
    }

    /**
     * Defining new unknown resource type via plugin loader and handling it
     *
     */
    public function testCtxLoader()
    {
        TypeLoader::addResourceDirectory("Test_Resource", dirname(__FILE__)."/Resources");
        $resp = $this->_callService("returnCtx");
        $this->assertContains("Accept-language:", $resp->getResponse());
        $this->assertContains("foo=bar", $resp->getResponse());
    }

    /**
     * Defining new unknown resource type and handling it
     *
     */
    public function testCtx()
    {
        TypeLoader::setResourceLoader(new Zend_Amf_TestResourceLoader("2"));
        $resp = $this->_callService("returnCtx");
        $this->assertContains("Accept-language:", $resp->getResponse());
        $this->assertContains("foo=bar", $resp->getResponse());
    }

    /**
     * Defining new unknown resource type, handler has no parse()
     *
     */
    public function testCtxNoParse()
    {
        TypeLoader::setResourceLoader(new Zend_Amf_TestResourceLoader("3"));
        try {
            $resp = $this->_callService("returnCtx");
        } catch(\fproject\amf\AmfException $e) {
            $this->assertContains("Could not call parse()", $e->getMessage());
            return;
        }
        $this->fail("Failed to throw exception on unknown resource");
    }

}

class Zend_Amf_Resource_testclass {
    function returnFile()
    {
        return fopen(dirname(__FILE__)."/_files/testdata", "r");
    }
    function returnCtx()
    {
        $opts = array(
            'http'=>array(
            'method'=>"GET",
            'header'=>"Accept-language: en\r\n" .
                "Cookie: foo=bar\r\n"
            )
        );
        $context = stream_context_create($opts);
        return $context;
    }
}

class StreamContext2
{
    public function parse($resource)
    {
        return stream_context_get_options($resource);
    }
}
class StreamContext3
{
    protected function parse($resource)
    {
        return stream_context_get_options($resource);
    }
}
class Zend_Amf_TestResourceLoader implements \fproject\amf\loader\ResourceLoaderInterface {
    public $suffix;
    public function __construct($suffix) {
        $this->suffix = $suffix;
    }
    public function addPrefixPath($prefix, $path) {}
    public function removePrefixPath($prefix, $path = null) {}
    public function isLoaded($name) {}
    public function getClassName($name) {}
    public function load($name) {
        return $name.$this->suffix;
    }
}

