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

namespace fproject\amf\discovery;

use stdClass;
use ReflectionObject;
use ReflectionMethod;

/**
 * Analyses existing services. Warning: if 2 or more services have the same name, t-only one will appear in the returned data,
 * as it is an associative array using the service name as key. 
 * @package Amfphp_Plugins_Discovery
 * @author Ariel Sommeria-Klein
 */
class DiscoveryService {

    /**
     * @see AmfphpDiscovery
     * @var array of strings(patterns)
     */
    public static $excludePaths = [];

    /**
     * Paths to folders containing services(relative or absolute). Set by plugin.
     * @var array of paths
     */
    public static $serviceFolderPaths = [];

    /**
     * Paths to folders containing models(relative or absolute). Set by plugin.
     * @var array of paths
     */
    public static $modelFolderPaths = [];

    /**
     *
     * @var array of ClassFindInfo. set by plugin.
     */
    public static $classNames2ClassFindInfo = [];

    /**
     * Restrict access to amfphp_admin.
     * @var boolean
     */
    public static $restrictAccess;

    /**
     * get method roles
     * @param string $methodName
     * @return array
     */
    public function _getMethodRoles($methodName)
    {
        if (self::$restrictAccess) {
            return ['phpAmf_Admin'];
        }
        return [];
    }

    /**
     * Finds classes in folder. If in sub-folders add the relative path to the name.
     * recursive, so use with care.
     * @param string $rootPath
     * @param string $subFolder
     * @return array
     */
    protected function searchFolderForClasses($rootPath, $subFolder)
    {
        $ret = array();
        $folderContent = scandir($rootPath . $subFolder);

        if ($folderContent) {
            foreach ($folderContent as $fileName) {
                //add all .php file names, but removing the .php suffix
                if (strpos($fileName, ".php")) {
                    $fullServiceName = $subFolder . substr($fileName, 0, strlen($fileName) - 4);
                    $ret[] = $fullServiceName;
                } else if ((substr($fileName, 0, 1) != '.') && is_dir($rootPath . $subFolder . $fileName)) {
                    $ret = array_merge($ret, $this->searchFolderForClasses($rootPath, $subFolder . $fileName . '/'));
                }
            }
        }
        return $ret;
    }

    /**
     * Returns a list of available services
     * @param array $folderPaths
     * @param array $classNameToClassFindInfo
     * @return array of service names
     */
    protected function getClassNames(array $folderPaths, array $classNameToClassFindInfo)
    {
        $ret = array();
        foreach ($folderPaths as $serviceFolderPath) {
            $ret = array_merge($ret, $this->searchFolderForClasses($serviceFolderPath, ''));
        }

        foreach ($classNameToClassFindInfo as $key => $value) {
            $ret[] = $key;
        }

        return $ret;
    }

    /**
     * Extracts
     * - types from param tags. type is first word after tag name, name of the variable is second word ($ is removed)
     * - return tag
     * 
     * @param string $comment 
     * @return array{'returns' => type, 'params' => array{var name => type}}
     */
    protected function parseMethodComment($comment)
    {
        //get rid of phpdoc formatting
        $comment = str_replace('/**', '', $comment);
        $comment = str_replace('*/', '', $comment);
        $comment = str_replace('*', '', $comment);
        $exploded = explode('@', $comment);
        $ret = array();
        $params = array();
        foreach ($exploded as $value) {
            if (strtolower(substr($value, 0, 5)) == 'param') {
                $words = explode(' ', $value);
                $type = trim($words[1]);
                $varName = trim(str_replace('$', '', $words[2]));
                $params[$varName] = $type;
            } else if (strtolower(substr($value, 0, 6)) == 'return') {

                $words = explode(' ', $value);
                $type = trim($words[1]);
                $ret['return'] = $type;
            }
        }
        $ret['param'] = $params;
        if (!isset($ret['return'])) {
            $ret['return'] = '';
        }
        return $ret;
    }

    /**
     * Does the actual collection of data about available services and models
     * @return stdClass a standard object that contains two fields:
     * 'services': an array of ServiceDescriptor objects
     * 'models': an array of ModelDescriptor objects
     */
    public function discover()
    {
        $return = new stdClass();
        $return->services = $this->discoverServices();

        $return->models = $this->discoverModels();

        return $return;
    }

    /**
     * Does the actual collection of data about available services
     * @return ServiceDescriptor[] an array of ServiceDescriptor objects
     */
    public function discoverServices()
    {
        $serviceNames = $this->getClassNames(self::$serviceFolderPaths, self::$classNames2ClassFindInfo);
        $services = array();
        foreach ($serviceNames as $serviceName) {
            $serviceObject = ServiceRouter::getServiceObjectStatically($serviceName, self::$serviceFolderPaths, self::$classNames2ClassFindInfo);
            $reflectionObject = new ReflectionObject($serviceObject);
            $docComment = $reflectionObject->getDocComment();
            $rflMethods = $reflectionObject->getMethods(ReflectionMethod::IS_PUBLIC);
            $methods = array();
            foreach ($rflMethods as $rflMethod) {
                $methodName = $rflMethod->name;

                if (substr($methodName, 0, 1) == '_') {
                    //methods starting with a '_' as they are reserved, so filter them out
                    continue;
                }

                $parameters = array();
                $rflParams = $rflMethod->getParameters();

                $methodComment = $rflMethod->getDocComment();
                $parsedMethodComment = $this->parseMethodComment($methodComment);
                foreach ($rflParams as $rflParam)
                {
                    $parameterName = $rflParam->name;
                    $type = '';
                    if ($rflParam->getClass()) {
                        $type = $rflParam->getClass()->name;
                    } else if (isset($parsedMethodComment['param'][$parameterName])) {
                        $type = $parsedMethodComment['param'][$parameterName];
                    }
                    $parameterInfo = new VariableDescriptor($parameterName, $type);

                    $parameters[] = $parameterInfo;
                }
                $methods[$methodName] = new MethodDescriptor($methodName, $parameters, $methodComment, $parsedMethodComment['return']);
            }

            $services[$serviceName] = new ServiceDescriptor($serviceName, $methods, $docComment);
        }
        //note : filtering must be done at the end, as for example excluding a Vo class needed by another creates issues
        foreach ($services as $serviceName => $serviceObj) {
            foreach (self::$excludePaths as $excludePath) {
                if (strpos($serviceName, $excludePath) !== false) {
                    unset($services[$serviceName]);
                    break;
                }
            }
        }

        return $services;
    }

    /**
     * Does the actual collection of data about available services
     * @return ServiceDescriptor[] an array of ServiceDescriptor objects
     */
    public function discoverModels()
    {
        $modelNames = $this->getClassNames(self::$modelFolderPaths, self::$classNames2ClassFindInfo);
        $models = array();
        foreach ($modelNames as $modelName) {
            $modelObject = ServiceRouter::getServiceObjectStatically($modelName, self::$modelFolderPaths, self::$classNames2ClassFindInfo);
            $reflectionObject = new ReflectionObject($modelObject);
            $rflProps = $reflectionObject->getProperties(ReflectionMethod::IS_PUBLIC);
            /** @var VariableDescriptor[] $props */
            $props = [];
            foreach ($rflProps as $rflProp) {
                $propName = $rflProp->name;

                if (substr($propName, 0, 1) == '_') {
                    //methods starting with a '_' as they are reserved, so filter them out
                    continue;
                }

                $prop = new VariableDescriptor($propName);
                $prop->comment = $rflProp->getDocComment();
                $prop->parseTypeFromComment();

                $props[$propName] = $prop;
            }

            $models[$modelName] = new ModelDescriptor($modelName, $props, $reflectionObject->getDocComment());
        }
        //note : filtering must be done at the end, as for example excluding a Vo class needed by another creates issues
        foreach ($models as $modelName => $serviceObj) {
            foreach (self::$excludePaths as $excludePath) {
                if (strpos($modelName, $excludePath) !== false) {
                    unset($models[$modelName]);
                    break;
                }
            }
        }

        return $models;
    }
}

?>
