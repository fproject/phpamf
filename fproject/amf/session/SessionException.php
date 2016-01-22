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

namespace fproject\amf\session;


use fproject\amf\AmfException;

class SessionException extends AmfException
{
    /**
     * sessionStartError
     *
     * @see http://framework.zend.com/issues/browse/ZF-1325
     * @var string PHP Error Message
     */
    static public $sessionStartError = null;

    /**
     * handleSessionStartError() - interface for set_error_handler()
     *
     * @see    http://framework.zend.com/issues/browse/ZF-1325
     * @param $errNo
     * @param $errStr
     * @param $errFile
     * @param $errLine
     * @param $errContext
     */
    static public function handleSessionStartError($errNo, $errStr, $errFile, $errLine, $errContext)
    {
        self::$sessionStartError = $errFile . '(Line:' . $errLine . '): Error #' . $errNo . ' ' . $errStr;
    }

    /**
     * handleSilentWriteClose() - interface for set_error_handler()
     *
     * @see    http://framework.zend.com/issues/browse/ZF-1325
     * @param  int $errNo
     * @param $errStr
     * @param $errFile
     * @param $errLine
     * @param $errContext
     */
    static public function handleSilentWriteClose($errNo, $errStr, $errFile, $errLine, $errContext)
    {
        self::$sessionStartError .= PHP_EOL . $errFile . '(Line:' . $errLine . '): Error #' . $errNo . ' ' . $errStr;
    }
}

