<?php
namespace Monitor;

use Monitor\Notification\Trigger\Comparator\Comparator;
use Monitor\Notification\Service\Factory as ServiceFactory;
use Monitor\Notification\Trigger\TriggerMgr;
use Monitor\Notification\Facade as NotificationFacade;
use Monitor\Notification\NotificationMgr;
use Monitor\Notification\Parser as NotificationParser;
use Monitor\Format\Factory as FormatFactory;
use Monitor\Utils\PercentageHelper;
use Monitor\Client\Http\Http as Http;
use Monitor\Service\NotificationLog as NotificationLogService;
use Monitor\Service\ServerHistory as ServerHistoryService;


require __DIR__.'/bootstrap.php';

$formatFactory = new FormatFactory;

$notificationMgr = new NotificationMgr(
    $config->get('notification')['data'],
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
    new NotificationLogService($entityManager)
);

$triggerMgr->setComparator(new Comparator);

$monitor = new Monitor(
    $config,
    new NotificationFacade(
        $config,
        $triggerMgr,
        new ServiceFactory,
        $entityManager->getRepository('Monitor\Model\Service')
    ),
    $formatFactory->build($config->get('format')),
    $entityManager->getRepository('Monitor\Model\Server'),
    new ServerHistoryService($entityManager)
);

$monitor->setClient(new Http);
$monitor->run();
