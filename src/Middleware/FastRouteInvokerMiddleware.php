<?php

namespace IdNet\Middleware;

use DI\InvokerInterface;
use FastRoute\Dispatcher;
use IdNet\Exception\MethodNotAllowedException;
use IdNet\Exception\NotFoundException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class FastRouteInvokerMiddleware implements MiddlewareInterface
{
    /** @var Dispatcher */
    protected $dispatcher;

    /** @var InvokerInterface */
    protected $invoker;

    /**
     * @param Dispatcher $dispatcher
     * @param InvokerInterface $invoker
     */
    public function __construct(Dispatcher $dispatcher, InvokerInterface $invoker)
    {
        $this->dispatcher = $dispatcher;
        $this->invoker = $invoker;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        list($action, $vars) = $this->getRoute($request);

        foreach ($vars as $key => $value)
        {
            $request = $request->withAttribute($key, $value);
        }

        return $this->invoker->call($action, ['request' => $request]);
    }


    private function getRoute(ServerRequestInterface $request)
    {
        $route = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );
        $status = array_shift($route);

        if ($status === Dispatcher::FOUND) {
            return $route;
        }

        if ($status === Dispatcher::METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException($request, $route[0]);
        }

        throw new NotFoundException($request);
    }
}
