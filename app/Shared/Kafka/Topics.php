<?php

namespace App\Shared\Kafka;

class Topics
{
    const AUTHORIZE_TRANSACTION = 'authorize_transaction_topic';
    const AUTHORIZE_TRANSACTION_DLQ = 'authorize_transaction_topic_dlq';

    const TRANSACTION_AUTHORIZED = 'transaction_authorized_topic';
    const TRANSACTION_AUTHORIZED_DLQ = 'transaction_authorized_topic_dlq';

    const TRANSACTION_NOT_AUTHORIZED = 'transaction_not_authorized_topic';
    const TRANSACTION_NOT_AUTHORIZED_DLQ = 'transaction_not_authorized_topic_dlq';

    const TRANSACTION_NOTIFICATION = 'transaction_notification_topic';
    const TRANSACTION_NOTIFICATION_DLQ = 'transaction_notification_topic_dlq';
}