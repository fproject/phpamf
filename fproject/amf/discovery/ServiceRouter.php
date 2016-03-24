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
use fproject\amf\AmfException;

/**
 * The Service Router class is responsible for executing the remote service method and returning it's value.
 * based on the old 'Executive' of php 1.9. It looks for a service either explicitly defined in a
 * ClassFindInfo object, or in a service folder.
 *
 */
class ServiceRouter {
    /**
     * filter called when the service object is created. Useful for authentication
     * @param Object $serviceObject 
     * @param string $serviceName
     * @param string $methodName
     * @param array $parameters
     */
    const FILTER_SERVICE_OBJECT = 'FILTER_SERVICE_OBJECT';
    /**
     * paths to folders containing services(relative or absolute)
     * @var array of paths
     */
    public $serviceFolderPaths;

    /**
     *
     * @var array of ClassFindInfo
     */
    public $serviceNames2ClassFindInfo;
    
    /**
     * check parameters. This is useful for development, but should be disabled for production
     * @var Boolean
     */
    public $checkArgumentCount;

    /**
     * constructor
     * @param array $serviceFolderPaths folders containing service classes
     * @param array $serviceNames2ClassFindInfo a dictionary of service classes represented in a ClassFindInfo.
     * @param Boolean $checkArgumentCount
     */
    public function __construct($serviceFolderPaths, $serviceNames2ClassFindInfo, $checkArgumentCount = false) {
        $this->serviceFolderPaths = $serviceFolderPaths;
        $this->serviceNames2ClassFindInfo = $serviceNames2ClassFindInfo;
        $this->checkArgumentCount = $checkArgumentCount;
    }

    /**
     * get a service object by its name. Looks for a match in serviceNames2ClassFindInfo, then in the defined service folders.
     * If none found, an exception is thrown
     * @todo maybe option for a fully qualified class name.
     * this method is static so that it can be used also by the discovery service
     *  '__' are replaced by '/' to help the client generator support packages without messing with folders and the like
     *
     * @param string $serviceName
     * @param array $serviceFolderPaths
     * @param array $serviceNames2ClassFindInfo
     * @throws AmfException
     * @return Object service object
     */
    public static function getServiceObjectStatically($serviceName, array $serviceFolderPaths, array $serviceNames2ClassFindInfo){
        $serviceObject = null;
        if (isset($serviceNames2ClassFindInfo[$serviceName])) {
            $classFindInfo = $serviceNames2ClassFindInfo[$serviceName];
            $s = $classFindInfo->absolutePath;
            if(strcasecmp(substr($s, -4),'.php') !== 0)
            {
                $s = $s.'.php';
            }
            require_once $s;
            $serviceObject = new $classFindInfo->className();
        } else {
            $temp = str_replace('.', '/', $serviceName);
            $serviceNameWithSlashes = str_replace('__', '/', $temp);
            $serviceIncludePath = $serviceNameWithSlashes . '.php';
            $exploded = explode('/', $serviceNameWithSlashes);
            $className = $exploded[count($exploded) - 1];
            //no class find info. try to look in the folders
            foreach ($serviceFolderPaths as $folderPath) {
                if(substr($folderPath, -1) !== '/')
                {
                    $folderPath = $folderPath.'/';
                }
                $servicePath = $folderPath . $serviceIncludePath;
                if (file_exists($servicePath)) {
                    require_once $servicePath;
                    $serviceObject = new $className();
                    break;
                }
            }
        }

        if (!$serviceObject) {
            throw new AmfException("Service not found: $serviceName");
        }
        return $serviceObject;
        
    }
    
    /**
     * get service object
     * @param String $serviceName
     * @return Object service object
     */
    public function getServiceObject($serviceName) {
        return self::getServiceObjectStatically($serviceName, $this->serviceFolderPaths, $this->serviceNames2ClassFindInfo);
    }
}

?>