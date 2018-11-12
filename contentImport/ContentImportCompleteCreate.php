<?php
 namespace go1\util\ContentImport;

 use stdClass;

class ContentImportCompleteCreate
{
    public const ROUTING_KEY = 'notify.content_import.complete';

    public static function publish(stdClass $payload): stdClass
    {
      $message = new stdClass();

      $message->id = $payload->id;
      $message->type = $payload->type;
      $message->instanceId = $payload->instanceId;
      $message->configuration = $payload->configuration;
      $message->status = $payload->status;
      $message->priority = $payload->priority;
      $message->analytics = $payload->analytics;
      $message->reoccurringPeriod = $payload->reoccurringPeriod;
      $message->scheduleJobId = $payload->scheduleJobId;
      $message->createdDate = $payload->createdDate;
      $message->modifiedDate = $payload->modifiedDate;
      $message->message = $payload->message;

      return $message;
    }
}
