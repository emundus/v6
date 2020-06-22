<?php
namespace Payplug;

class Notification
{
    public static function treat($requestBody, $authentication = null)
    {
        $postArray = json_decode($requestBody, true);

        if ($postArray === null) {
            throw new Exception\UnknownAPIResourceException('Request body is not valid JSON.');
        }

        $unsafeAPIResource = Resource\APIResource::factory($postArray);
        return $unsafeAPIResource->getConsistentResource($authentication);
    }
}
