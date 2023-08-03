<?php
namespace AtLab\Comagic\Facades;

use AtLab\Comagic\Api;
use Illuminate\Support\Facades\Facade;
final class Comagic extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Api::class;
    }
}
