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
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Amf_Value_Messaging_ErrorMessage
 */
require_once 'Zend/Amf/Value/Messaging/ErrorMessage.php';
/**
 * @see Zend_Amf_Value_Messaging_RemotingMessage
 */
require_once 'Zend/Amf/Value/Messaging/RemotingMessage.php';

use fproject\amf\loader\ResourceLoaderInterface;

/**
 * Loads a local class and executes the instantiation of that class.
 *
 * @todo       PHP 5.3 can drastically change this class w/ namespace and the new call_user_func w/ namespace
 * @package    Zend_Amf
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
final class Zend_Amf_Parse_TypeLoader
{
    /**
     * @var string callback class
     */
    public static $callbackClass;

    /**
     * @var array AMF class map
     */
    public static $classMap = array (
        'flex.messaging.messages.AcknowledgeMessage' => 'fproject\amf\value\messaging\AcknowledgeMessage',
        'flex.messaging.messages.AsyncMessage'       => 'fproject\amf\value\messaging\AsyncMessage',
        'flex.messaging.messages.CommandMessage'     => 'fproject\amf\value\messaging\CommandMessage',
        'flex.messaging.messages.ErrorMessage'       => 'Zend_Amf_Value_Messaging_ErrorMessage',
        'flex.messaging.messages.RemotingMessage'    => 'Zend_Amf_Value_Messaging_RemotingMessage',
        'flex.messaging.io.ArrayCollection'          => 'Zend_Amf_Value_Messaging_ArrayCollection',
    );

    /**
     * @var array Default class map
     */
    protected static $_defaultClassMap = array(
        'flex.messaging.messages.AcknowledgeMessage' => 'fproject\amf\value\messaging\AcknowledgeMessage',
        'flex.messaging.messages.AsyncMessage'       => 'fproject\amf\value\messaging\AsyncMessage',
        'flex.messaging.messages.CommandMessage'     => 'fproject\amf\value\messaging\CommandMessage',
        'flex.messaging.messages.ErrorMessage'       => 'Zend_Amf_Value_Messaging_ErrorMessage',
        'flex.messaging.messages.RemotingMessage'    => 'Zend_Amf_Value_Messaging_RemotingMessage',
        'flex.messaging.io.ArrayCollection'          => 'Zend_Amf_Value_Messaging_ArrayCollection',
    );

    /**
     * @var ResourceLoaderInterface
     */
    protected static $_resourceLoader = null;


    protected static $failureCache=[];

    /**
     * Load the mapped class type into a callback.
     *
     * @param  string $className
     * @return object|false
     */
    public static function loadType($className)
    {
        $class    = self::getMappedClassName($className);
        if(!$class) {
            $class = str_replace('.', '_', $className);
        }
        elseif(isset(self::$failureCache[$className])) {
            return self::$failureCache[$className];
        }

        if (!class_exists($class)) {
            return "stdClass";
        }

        return $class;
    }

    /**
     * Looks up the supplied call name to its mapped class name
     *
     * @param  string $className
     * @return string
     */
    public static function getMappedClassName($className)
    {
        $mappedName = array_search($className, self::$classMap);

        if ($mappedName) {
            return $mappedName;
        }

        $mappedName = array_search($className, array_flip(self::$classMap));

        if ($mappedName) {
            return $mappedName;
        }

        return false;
    }

    /**
     * Map PHP class names to ActionScript class names
     *
     * Allows users to map the class names of there action script classes
     * to the equivelent php class name. Used in deserialization to load a class
     * and serialiation to set the class name of the returned object.
     *
     * @param  string $asClassName
     * @param  string $phpClassName
     * @return void
     */
    public static function setMapping($asClassName, $phpClassName)
    {
        self::$classMap[$asClassName] = $phpClassName;
    }

    /**
     * Reset type map
     *
     * @return void
     */
    public static function resetMap()
    {
        self::$classMap = self::$_defaultClassMap;
    }

    /**
     * Set loader for resource type handlers
     *
     * @param ResourceLoaderInterface $loader
     */
    public static function setResourceLoader(ResourceLoaderInterface $loader)
    {
        self::$_resourceLoader = $loader;
    }

    /**
     * Add directory to the list of places where to look for resource handlers
     *
     * @param string $prefix
     * @param string $dir
     */
    public static function addResourceDirectory($prefix, $dir)
    {
        if(self::$_resourceLoader) {
            self::$_resourceLoader->addPrefixPath($prefix, $dir);
        }
    }

    /**
     * Get plugin class that handles this resource
     *
     * @param resource $resource Resource type
     * @return string Class name
     */
    public static function getResourceParser($resource)
    {
        if(self::$_resourceLoader) {
            $type = preg_replace("/[^A-Za-z0-9_]/", " ", get_resource_type($resource));
            $type = str_replace(" ","", ucwords($type));
            return self::$_resourceLoader->load($type);
        }
        return false;
    }

    /**
     * Convert resource to a serializable object
     *
     * @param resource $resource
     * @return mixed
     * @throws \fproject\amf\AmfException
     */
    public static function handleResource($resource)
    {
        if(!self::$_resourceLoader) {
            throw new \fproject\amf\AmfException('Unable to handle resources - resource plugin loader not set');
        }
        try {
            while(is_resource($resource)) {
                $resclass = self::getResourceParser($resource);
                if(!$resclass) {
                    throw new \fproject\amf\AmfException('Can not serialize resource type: '. get_resource_type($resource));
                }
                $parser = new $resclass();
                if(is_callable(array($parser, 'parse'))) {
                    $resource = $parser->parse($resource);
                } else {
                    throw new \fproject\amf\AmfException("Could not call parse() method on class $resclass");
                }
            }
            return $resource;
        } catch(\fproject\amf\AmfException $e) {
            throw new \fproject\amf\AmfException($e->getMessage(), $e->getCode(), $e);
        } catch(Exception $e) {
            throw new \fproject\amf\AmfException('Can not serialize resource type: '. get_resource_type($resource), 0, $e);
        }
    }
}
