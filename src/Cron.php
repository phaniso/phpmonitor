<?php

use Monitor\Notification\Trigger\Comparator\Comparator;
use Monitor\Notification\Service\Factory as ServiceFactory;
use Monitor\Notification\Trigger\TriggerMgr;
use Monitor\Notification\Facade as NotificationFacade;
use Monitor\Notification\NotificationMgr;
use Monitor\Notification\Parser as NotificationParser;
use Monitor\Service\NotificationLog as NotificationLogService;
use Monitor\Service\ServerHistory as ServerHistoryService;
use Monitor\Format\Factory as FormatFactory;
use Monitor\Utils\PercentageHelper;
use Monitor\Client\Http\Http as Http;
use Monitor\Monitor as Monitor;
use Monitor\Utils\ArrayHelper;

require __DIR__.'/Bootstrap.php';

$formatFactory = new FormatFactory;
$format = $formatFactory->build($config->get('format'));

$notificationMgr = new NotificationMgr(
    new NotificationParser,
    $config->get('notification_delay_in_hours'),
    new NotificationLogService($entityManager),
    $entityManager->getRepository('Monitor\Model\Notification')
);

$triggerMgr = new TriggerMgr(
    $notificationMgr,
    new PercentageHelper,
    $entityManager->getRepository('Monitor\Model\Trigger'),
    $entityManager->getRepository('Monitor\Model\Service'),
    new NotificationLogService($entityManager),
    new Comparator
);
$triggerMgr->setNotificationData($config->get('notification')['data']);

$notificationFacade = new NotificationFacade(
        $config,
        $triggerMgr,
        new ServiceFactory,
        $entityManager->getRepository('Monitor\Model\Service')
);

$monitor = new Monitor(
    $config,
    $notificationFacade,
    $format,
    $entityManager->getRepository('Monitor\Model\Server'),
    new ServerHistoryService($entityManager),
    new ArrayHelper
);
$monitor->setClient(new Http);
$monitor->run();