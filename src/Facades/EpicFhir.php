<?php
namespace Telemedicall\EpicFhir\Facades;

use Illuminate\Support\Facades\Facade;

class EpicFhir extends Facade {
	
    protected static function getFacadeAccessor() 
	{
        return \Telemedicall\EpicFhir\Services\EpicFhirService::class;
    }
}