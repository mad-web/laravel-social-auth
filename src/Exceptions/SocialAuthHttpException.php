<?php

namespace MadWeb\SocialAuth\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class SocialAuthHttpException extends HttpResponseException
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $social;

    /**
     * SocialAuthException constructor.
     *
     * @param  Response  $response
     * @param  \Illuminate\Database\Eloquent\Model  $social
     */
    public function __construct(Response $response, Model $social)
    {
        parent::__construct($response);

        $this->social = $social;
    }
}
