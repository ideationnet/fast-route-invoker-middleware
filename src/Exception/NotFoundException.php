<?php

namespace IdNet\Exception;

use Psr\Http\Message\ServerRequestInterface;


class NotFoundException extends \Exception
{
    /** @var ServerRequestInterface */
    protected $request;

    public function __construct(
        ServerRequestInterface $request,
        $code = 404,
        $message = "Not found"
    ) {
        $this->request = $request;
        parent::__construct($message, $code);
    }

    public function getRequest()
    {
        return $this->request;
    }
}