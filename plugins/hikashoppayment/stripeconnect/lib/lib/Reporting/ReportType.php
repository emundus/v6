<?php

namespace Stripe\Reporting;

class ReportType extends \Stripe\ApiResource
{
    const OBJECT_NAME = "reporting.report_type";

    use \Stripe\ApiOperations\All;
    use \Stripe\ApiOperations\Retrieve;
}
