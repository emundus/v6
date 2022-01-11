<?php

/**
 * @package   Gantry5
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2021 RocketTheme, LLC
 * @license   Dual License: MIT or GNU/GPLv2 and later
 *
 * http://opensource.org/licenses/MIT
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Gantry Framework code that extends GPL code is considered GNU/GPLv2 and later
 */

namespace Gantry\Component\Controller;

use Gantry\Component\Response\JsonResponse;

/**
 * Class JsonController
 * @package Gantry\Component\Controller
 */
abstract class JsonController extends BaseController
{
    /**
     * Execute controller and returns JsonResponse object.
     *
     * @param string $method
     * @param array $path
     * @param array $params
     * @return JsonResponse
     * @throws \RuntimeException
     */
    public function execute($method, array $path, array $params)
    {
        $response = parent::execute($method, $path, $params);

        if (!$response instanceof JsonResponse) {
            $response = new JsonResponse($response);
        }

        return $response;
    }
}
