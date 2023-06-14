<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Resolver;

use Xiidea\EasyAuditBundle\Common\UserAwareComponent;
use Xiidea\EasyAuditBundle\Resolver\UserEventCommand\AuthenticationFailedCommand;
use Xiidea\EasyAuditBundle\Resolver\UserEventCommand\InteractiveLoginCommand;
use Xiidea\EasyAuditBundle\Resolver\UserEventCommand\ResolverCommand;

/** Custom Event Resolver Example For FosUserBundle  */
class UserEventResolver extends UserAwareComponent implements EventResolverInterface
{
    private $commands = array();

    private $default;

    public function __construct()
    {
        $this->commands = array(
            'security.interactive_login' => new InteractiveLoginCommand($this),
            'security.authentication.failure' => new AuthenticationFailedCommand(),
        );
    }

    /**
     * @param Event $event
     * @param $eventName
     *
     * @return array
     */
    public function getEventLogInfo($event, $eventName)
    {
        $this->default = array(
            'type' => $eventName,
            'description' => $eventName,
        );

        if (!isset($this->commands[$eventName])) {
            return $this->default;
        }

        return $this->getEventLogDetails($event, $this->commands[$eventName]);
    }

    /**
     * @param Event           $event
     * @param ResolverCommand $command
     *
     * @return array
     */
    protected function getEventLogDetails($event, ResolverCommand $command)
    {
        $details = $command->resolve($event);

        return empty($details) ? $this->default : $details;
    }
}
