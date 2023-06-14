<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Listener;

use Xiidea\EasyAuditBundle\Logger\LoggerFactory;
use Xiidea\EasyAuditBundle\Resolver\EventResolverFactory;

class LogEventsListener
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;
    /**
     * @var \Xiidea\EasyAuditBundle\Resolver\EventResolverFactory
     */
    private $resolverFactory;

    public function __construct(LoggerFactory $loggerFactory, EventResolverFactory $resolverFactory)
    {
        $this->loggerFactory = $loggerFactory;
        $this->resolverFactory = $resolverFactory;
    }

    public function resolveEventHandler($event, $eventName)
    {
        $eventInfo = $this->resolverFactory->getEventLog($event, $eventName);
        $this->loggerFactory->executeLoggers($eventInfo);
    }
}
