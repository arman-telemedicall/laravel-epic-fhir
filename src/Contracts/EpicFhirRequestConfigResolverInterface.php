<?php

namespace Teleminergmbh\EpicFhir\Contracts;

use Illuminate\Http\Request;

interface EpicFhirRequestConfigResolverInterface
{
    /**
     * @return array<string, mixed>
     */
    public function resolveForRequest(Request $request, ?string $clientId = null): array;
}
