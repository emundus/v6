<?php

/*
 * This file is part of JSON-API.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tobscure\JsonApi;

use Exception;
use RuntimeException;
use Tobscure\JsonApi\Exception\Handler\ExceptionHandlerInterface;

class ErrorHandler
{
    /**
     * Stores the valid handlers.
     *
     * @var \Tobscure\JsonApi\Exception\Handler\ExceptionHandlerInterface[]
     */
    private $handlers = [];

    /**
     * Handle the exception provided.
     *
     * @param Exception $e
     *
     * @throws RuntimeException
     *
     * @return \Tobscure\JsonApi\Exception\Handler\ResponseBag
     */
    public function handle(Exception $e)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->manages($e)) {
                return $handler->handle($e);
            }
        }

        throw new RuntimeException('Exception handler for '.get_class($e).' not found.');
    }

    /**
     * Register a new exception handler.
     *
     * @param \Tobscure\JsonApi\Exception\Handler\ExceptionHandlerInterface $handler
     *
     * @return void
     */
    public function registerHandler(ExceptionHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }
}
