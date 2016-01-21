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

namespace fproject\amf\auth;

class AuthResult
{
    /**
     * General Failure
     */
    const FAILURE                        =  0;

    /**
     * Failure due to identity not being found.
     */
    const FAILURE_IDENTITY_NOT_FOUND     = -1;

    /**
     * Failure due to identity being ambiguous.
     */
    const FAILURE_IDENTITY_AMBIGUOUS     = -2;

    /**
     * Failure due to invalid credential being supplied.
     */
    const FAILURE_CREDENTIAL_INVALID     = -3;

    /**
     * Failure due to uncategorized reasons.
     */
    const FAILURE_UNCATEGORIZED          = -4;

    /**
     * Authentication success.
     */
    const SUCCESS                        =  1;

    /**
     * Authentication result code
     *
     * @var int
     */
    protected $_code;

    /**
     * The identity used in the authentication attempt
     *
     * @var mixed
     */
    protected $_identity;

    /**
     * An array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this should be an empty array.
     *
     * @var array
     */
    protected $_messages;

    /**
     * Sets the result code, identity, and failure messages
     *
     * @param  int $code
     * @param  mixed $identity
     * @param  array $messages
     */
    public function __construct($code, $identity, array $messages = array())
    {
        $code = (int) $code;

        if ($code < self::FAILURE_UNCATEGORIZED) {
            $code = self::FAILURE;
        } elseif ($code > self::SUCCESS ) {
            $code = 1;
        }

        $this->_code     = $code;
        $this->_identity = $identity;
        $this->_messages = $messages;
    }

    /**
     * Returns whether the result represents a successful authentication attempt
     *
     * @return boolean
     */
    public function isValid()
    {
        return ($this->_code > 0) ? true : false;
    }

    /**
     * getCode() - Get the result code for this authentication attempt
     *
     * @return int
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Returns the identity used in the authentication attempt
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    /**
     * Returns an array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this method returns an empty array.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
