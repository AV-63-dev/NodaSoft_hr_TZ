<?php

namespace NW\WebService\References\Operations\Notification;

use Exception;
use NW\WebService\References\Operations\Notification\Models\Seller;
use NW\WebService\References\Operations\Notification\Models\Client;
use NW\WebService\References\Operations\Notification\Models\Employee;

class TsReturnOperation extends ReferencesOperation
{
    public const TYPE_NEW    = 1;
    public const TYPE_CHANGE = 2;

    public function doOperation(): array
    {
        try {
            function __() {
                return 123;
            }

            $result = [
                'notificationEmployeeByEmail' => false,
                'notificationClientByEmail'   => false,
                'notificationClientBySms'     => false,
            ];
            $data = (array)$this->getRequest('data');
            $notificationType = $data['notificationType'] ? (int)$data['notificationType'] : throw new Exception('Empty notificationType', 400);
            
            $sellerId = $data['resellerId'] ? (int)$data['resellerId'] : throw new Exception('Empty resellerId', 400);
            $seller = Seller::find($sellerId) ?? throw new Exception('Seller not found!', 404);
            
            $clientId = $data['clientId'] ? (int)$data['clientId'] : throw new Exception('Empty clientId', 400);
            $client = Client::find($clientId) ?? throw new Exception('Client not found!', 404);
            // TODO в нормальном проекте, лучше так не делать, т.к. КЛИЕНТ другого типа или ОТСУТСТВИЕ ПРОДОВЦА у КЛИЕНТА - должны вызывать другое исключение. Про их связь один-к-одному вообще молчу😁
            if (!$client || $client->type !== Client::TYPE_CUSTOMER || $client->seller->id !== $sellerId) {
                throw new \Exception('сlient not found!', 400);
            };

            $creatorId = $data['creatorId'] ? (int)$data['creatorId'] : throw new Exception('Empty creatorId', 400);
            $creator = Employee::find($creatorId) ?? throw new Exception('Creator not found!', 404);
            $expertId = $data['expertId'] ? (int)$data['expertId'] : throw new Exception('Empty expertId', 400);
            $expert = Employee::find($expertId) ?? throw new Exception('Expert not found!', 404);

            $differences = '';
            if ($notificationType === self::TYPE_NEW) {
                $differences = __('NewPositionAdded', null, $sellerId);
            } else if ($notificationType === self::TYPE_CHANGE && !empty($data['differences'])) {
                $differences = __('PositionStatusHasChanged', [
                    'FROM' => Status::getName((int)$data['differences']['from']),
                    'TO'   => Status::getName((int)$data['differences']['to']),
                ], $sellerId);
            };

            $templateData = [
                'COMPLAINT_ID'       => (int)$data['complaintId'],
                'COMPLAINT_NUMBER'   => (string)$data['complaintNumber'],
                'CREATOR_ID'         => $creatorId,
                'CREATOR_NAME'       => $creator->getFullName(),
                'EXPERT_ID'          => $expertId,
                'EXPERT_NAME'        => $expert->getFullName(),
                'CLIENT_ID'          => $clientId,
                'CLIENT_NAME'        => $client->getFullName(),
                'CONSUMPTION_ID'     => (int)$data['consumptionId'],
                'CONSUMPTION_NUMBER' => (string)$data['consumptionNumber'],
                'AGREEMENT_NUMBER'   => (string)$data['agreementNumber'],
                'DATE'               => (string)$data['date'],
                'DIFFERENCES'        => $differences,
            ];
            foreach ($templateData as $key => $tempData) {
                if (empty($tempData)) {
                    throw new Exception("Template Data ({$key}) is empty!", 422); 
                };
            };

            $emailFrom = Employee::getResellerEmailFrom($sellerId);
            $emails = Employee::getEmailsByPermit($sellerId, 'tsGoodsReturn');
            if ($emailFrom && $emails) {
                $result['notificationEmployeeByEmail'] = [];
                foreach ($emails as $email) {
                    $result['notificationEmployeeByEmail'][$email] = MessagesClient::sendMessage($emailFrom, $email, __('complaintEmployeeEmailSubject', $templateData), __('complaintEmployeeEmailBody', $templateData));
                };
            };

            if ($notificationType === self::TYPE_CHANGE && !empty($data['differences']['to'])) {
                if ($emailFrom && $client->email) {
                    $result['notificationClientByEmail'] = MessagesClient::sendMessage($emailFrom, $client->email, __('complaintClientEmailSubject', $templateData), __('complaintClientEmailBody', $templateData));
                };
                if ($client->mobile) {
                    $result['notificationClientBySms'] = NotificationManager::sendSms($client->mobile, __('complaintClientSms', $templateData));
                };
            };
        } catch (Exception $e) {
            $result['error'] = [
                'error_code' => $e->getCode(),
                'error_msg' => $e->getMessage(),
            ];
        };
        return $result;
    }
}
