<?php

namespace Teleminergmbh\EpicFhir\Resolvers;

use Illuminate\Http\Request;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirRequestConfigResolverInterface;

class NullEpicFhirRequestConfigResolver implements EpicFhirRequestConfigResolverInterface
{
    public function resolveForRequest(Request $request, ?string $clientId = null): array
    {
        return [];
    }
}
