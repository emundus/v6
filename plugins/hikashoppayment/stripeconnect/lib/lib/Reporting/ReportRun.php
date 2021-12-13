<?php

namespace Stripe\Reporting;

class ReportRun extends \Stripe\ApiResource
{
    const OBJECT_NAME = "reporting.report_run";

    use \Stripe\ApiOperations\All;
    use \Stripe\ApiOperations\Create;
    use \Stripe\ApiOperations\Retrieve;
}
