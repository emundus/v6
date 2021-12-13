<?php

namespace Stripe;

class FileLink extends ApiResource
{

    const OBJECT_NAME = "file_link";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
