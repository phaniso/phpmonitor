<?php
namespace Monitor\Notification\Trigger;

use Monitor\Notification\Service\ServiceInterface;
use Monitor\Notification\Notification;

abstract class Observable
{
    protected $observers;

    public function addObserver(ServiceInterface $observer)
    {
        $this->observers[] = $observer;
    }

    public function popObserver()
    {
        array_pop($this->observers);
    }

    abstract public function notifyServices(Notification $notification);
}