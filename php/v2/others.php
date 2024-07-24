<?php

namespace NW\WebService\References\Operations\Notification;

abstract class ReferencesOperation
{
    abstract public function doOperation(): array;

    public function getRequest($pName)
    {
        return $_REQUEST[$pName];
    }
}

class Status
{
    public $id, $name;

    public static function getName(int $id): string
    {
        $a = [
            0 => 'Completed',
            1 => 'Pending',
            2 => 'Rejected',
        ];
        return $a[$id];
    }
}

class MessagesClient
{
    public static function sendMessage($emailFrom, $emailTo, $subject, $message): bool
    {
        // TODO в зависимости от ответа почтового сервера
        return true;
    }
}

class NotificationManager
{
    public static function sendSms($phone_number, $message): bool
    {
        // TODO в зависимости от ответа sms провайдера
        return true;
    }
}
