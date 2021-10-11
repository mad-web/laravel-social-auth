<?php

namespace MadWeb\SocialAuth\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class SocialGetUserInfoException extends Exception
{
    /**
     * @var Model
     */
    protected $social;

    /**
     * SocialUserAttachException constructor.
     *
     * @param  Model  $social
     * @param  string  $message
     * @param  int  $code
     * @param  Exception|null  $previous
     */
    public function __construct(Model $social, $message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->social = $social;
    }
}
