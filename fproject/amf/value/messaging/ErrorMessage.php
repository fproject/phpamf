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

namespace fproject\amf\value\messaging;

/**
 * Creates the error message to report to flex the issue with the call
 *
 * Corresponds to flex.messaging.messages.ErrorMessage
 *
 */
class ErrorMessage extends AcknowledgeMessage
{
    /**
     * Additional data with error
     * @var object
     */
    public $extendedData = null;

    /**
     * Error code number
     * @var string
     */
    public $faultCode;

    /**
     * Description as to the cause of the error
     * @var string
     */
    public $faultDetail;

    /**
     * Short description of error
     * @var string
     */
    public $faultString = '';

    /**
     * root cause of error
     * @var object
     */
    public $rootCause = null;
}
