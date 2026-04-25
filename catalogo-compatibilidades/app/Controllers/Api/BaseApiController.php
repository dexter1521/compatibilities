<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;

abstract class BaseApiController extends BaseController
{
    use ApiResponseTrait;
}
