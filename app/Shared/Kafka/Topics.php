<?php

namespace App\Shared\Kafka;

enum Topics: string
{
    case AuthorizeTransaction = 'authorize_transaction_topic';
    case AuthorizeTransactionDlq = 'authorize_transaction_topic_dlq';

    case TransactionAuthorized = 'transaction_authorized_topic';
    case TransactionAuthorizedDlq = 'transaction_authorized_topic_dlq';

    case TransactionNotAuthorized = 'transaction_not_authorized_topic';
    case TransactionNotAuthorizedDlq = 'transaction_not_authorized_topic_dlq';

    case TransactionNotification = 'transaction_notification_topic';
    case TransactionNotificationDlq = 'transaction_notification_topic_dlq';
}