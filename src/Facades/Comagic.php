<?php
namespace AtLab\Comagic\Facades;

use Illuminate\Support\Facades\Facade;
final class Comagic extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \AtLab\Comagic\Api::class;
    }
}
