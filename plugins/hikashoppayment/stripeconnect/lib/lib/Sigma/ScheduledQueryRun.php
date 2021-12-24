<?php

namespace Stripe\Sigma;

class ScheduledQueryRun extends \Stripe\ApiResource
{
    const OBJECT_NAME = "scheduled_query_run";

    use \Stripe\ApiOperations\All;
    use \Stripe\ApiOperations\Retrieve;

    public static function classUrl()
    {
        return "/v1/sigma/scheduled_query_runs";
    }
}
