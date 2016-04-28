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

namespace fproject\amf\loader;

/**
 * Resource class loader interface
 *
 */
interface ResourceLoaderInterface
{
    /**
     * Add prefixed paths to the registry of paths
     *
     * @param string $prefix
     * @param string $path
     * @return ResourceLoaderInterface
     */
    public function addPrefixPath($prefix, $path);

    /**
     * Remove a prefix (or prefixed-path) from the registry
     *
     * @param string $prefix
     * @param string $path OPTIONAL
     * @return ResourceLoaderInterface
     */
    public function removePrefixPath($prefix, $path = null);

    /**
     * Whether or not a Helper by a specific name
     *
     * @param string $name
     * @return bool
     */
    public function isLoaded($name);

    /**
     * Return full class name for a named helper
     *
     * @param string $name
     * @return string
     */
    public function getClassName($name);

    /**
     * Load a helper via the name provided
     *
     * @param string $name
     * @return string
     */
    public function load($name);
}
