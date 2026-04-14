<?php

namespace Teleminergmbh\EpicFhir\Facades;

use Illuminate\Support\Facades\Facade;

class EpicFhir extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Teleminergmbh\EpicFhir\EpicFhir::class;
    }
}
