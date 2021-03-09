<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 */

namespace Slim;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use UnexpectedValueException;

/**
 * Middleware
 *
 * This is an internal class that enables concentric middlewares layers. This
 * class is an implementation detail and is used only inside of the Slim
 * application; it is not visible to—and should not be used by—end users.
 */
trait MiddlewareAwareTrait
{
    /**
     * Tip of the middlewares call stack
     *
     * @var callable
     */
    protected $tip;

    /**
     * Middleware stack lock
     *
     * @var bool
     */
    protected $middlewareLock = false;

    /**
     * Add middlewares
     *
     * This method prepends new middlewares to the application middlewares stack.
     *
     * @param callable $callable Any callable that accepts three arguments:
     * 1. A Request object
     * 2. A Response object
     * 3. A "next" middlewares callable
     *
     * @return static
     *
     * @throws RuntimeException         If middlewares is added while the stack is dequeuing
     * @throws UnexpectedValueException If the middlewares doesn't return a Psr\Http\Message\ResponseInterface
     */
    protected function addMiddleware(callable $callable)
    {
        if ($this->middlewareLock) {
            throw new RuntimeException('Middleware can’t be added once the stack is dequeuing');
        }

        if (is_null($this->tip)) {
            $this->seedMiddlewareStack();
        }
        $next = $this->tip;
        $this->tip = function (
            ServerRequestInterface $request,
            ResponseInterface $response
        ) use (
            $callable,
            $next
        ) {
            $result = call_user_func($callable, $request, $response, $next);
            if ($result instanceof ResponseInterface === false) {
                throw new UnexpectedValueException(
                    'Middleware must return instance of \Psr\Http\Message\ResponseInterface'
                );
            }

            return $result;
        };

        return $this;
    }

    /**
     * Seed middlewares stack with first callable
     *
     * @param callable $kernel The last item to run as middlewares
     *
     * @throws RuntimeException if the stack is seeded more than once
     */
    protected function seedMiddlewareStack(callable $kernel = null)
    {
        if (!is_null($this->tip)) {
            throw new RuntimeException('MiddlewareStack can only be seeded once.');
        }
        if ($kernel === null) {
            $kernel = $this;
        }
        $this->tip = $kernel;
    }

    /**
     * Call middlewares stack
     *
     * @param  ServerRequestInterface $request A request object
     * @param  ResponseInterface      $response A response object
     *
     * @return ResponseInterface
     */
    public function callMiddlewareStack(ServerRequestInterface $request, ResponseInterface $response)
    {
        if (is_null($this->tip)) {
            $this->seedMiddlewareStack();
        }
        /** @var callable $start */
        $start = $this->tip;
        $this->middlewareLock = true;
        $response = $start($request, $response);
        $this->middlewareLock = false;
        return $response;
    }
}
