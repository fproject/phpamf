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

use fproject\amf\loader\ResourceLoaderInterface;
use fproject\amf\AmfException;

/**
 * Loads a local class and executes the instantiation of that class.
 *
 * @todo       PHP 5.3 can drastically change this class w/ namespace and the new call_user_func w/ namespace
 */
final class TypeLoader
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
        'flex.messaging.messages.ErrorMessage'       => 'fproject\amf\value\messaging\ErrorMessage',
        'flex.messaging.messages.RemotingMessage'    => 'fproject\amf\value\messaging\RemotingMessage',
        'flex.messaging.io.ArrayCollection'          => 'fproject\amf\value\messaging\ArrayCollection',
    );

    /**
     * @var array Default class map
     */
    protected static $_defaultClassMap = array(
        'flex.messaging.messages.AcknowledgeMessage' => 'fproject\amf\value\messaging\AcknowledgeMessage',
        'flex.messaging.messages.AsyncMessage'       => 'fproject\amf\value\messaging\AsyncMessage',
        'flex.messaging.messages.CommandMessage'     => 'fproject\amf\value\messaging\CommandMessage',
        'flex.messaging.messages.ErrorMessage'       => 'fproject\amf\value\messaging\ErrorMessage',
        'flex.messaging.messages.RemotingMessage'    => 'fproject\amf\value\messaging\RemotingMessage',
        'flex.messaging.io.ArrayCollection'          => 'fproject\amf\value\messaging\ArrayCollection',
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
     * @throws AmfException
     */
    public static function handleResource($resource)
    {
        if(!self::$_resourceLoader) {
            throw new AmfException('Unable to handle resources - resource plugin loader not set');
        }
        try {
            while(is_resource($resource)) {
                $resclass = self::getResourceParser($resource);
                if(!$resclass) {
                    throw new AmfException('Can not serialize resource type: '. get_resource_type($resource));
                }
                $parser = new $resclass();
                if(is_callable(array($parser, 'parse'))) {
                    $resource = $parser->parse($resource);
                } else {
                    throw new AmfException("Could not call parse() method on class $resclass");
                }
            }
            return $resource;
        } catch(AmfException $e) {
            throw new AmfException($e->getMessage(), $e->getCode(), $e);
        } catch(\Exception $e) {
            throw new AmfException('Can not serialize resource type: '. get_resource_type($resource), 0, $e);
        }
    }
}
