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
 * A message that represents an infrastructure command passed between
 * client and server. Subscribe/unsubscribe operations result in
 * CommandMessage transmissions, as do polling operations.
 *
 * Corresponds to flex.messaging.messages.CommandMessage
 *
 * Note: THESE VALUES MUST BE THE SAME ON CLIENT AND SERVER
 *
 */
class CommandMessage extends AsyncMessage
{
    /**
     *  This operation is used to subscribe to a remote destination.
     */
    const SUBSCRIBE_OPERATION = 0;

    /**
     * This operation is used to unsubscribe from a remote destination.
     */
    const UNSUSBSCRIBE_OPERATION = 1;

    /**
     * This operation is used to poll a remote destination for pending,
     * undelivered messages.
     */
    const POLL_OPERATION = 2;

    /**
     * This operation is used by a remote destination to sync missed or cached messages
     * back to a client as a result of a client issued poll command.
     */
    const CLIENT_SYNC_OPERATION = 4;

    /**
     * This operation is used to test connectivity over the current channel to
     * the remote endpoint.
     */
    const CLIENT_PING_OPERATION = 5;

    /**
     * This operation is used to request a list of failover endpoint URIs
     * for the remote destination based on cluster membership.
     */
    const CLUSTER_REQUEST_OPERATION = 7;

    /**
     * This operation is used to send credentials to the endpoint so that
     * the user can be logged in over the current channel.
     * The credentials need to be Base64 encoded and stored in the <code>body</code>
     * of the message.
     */
    const LOGIN_OPERATION = 8;

    /**
     * This operation is used to log the user out of the current channel, and
     * will invalidate the server session if the channel is HTTP based.
     */
    const LOGOUT_OPERATION = 9;

    /**
     * This operation is used to indicate that the client's subscription to a
     * remote destination has been invalidated.
     */
    const SESSION_INVALIDATE_OPERATION = 10;

    /**
     * This operation is used by the MultiTopicConsumer to subscribe/unsubscribe
     * from multiple subtopics/selectors in the same message.
     */
    const MULTI_SUBSCRIBE_OPERATION = 11;

    /**
     * This operation is used to indicate that a channel has disconnected
     */
    const DISCONNECT_OPERATION = 12;

    /**
     * This is the default operation for new CommandMessage instances.
     */
    const UNKNOWN_OPERATION = 10000;

    /**
     * The operation to execute for messages of this type
     * @var int
     */
    public $operation = self::UNKNOWN_OPERATION;
}
