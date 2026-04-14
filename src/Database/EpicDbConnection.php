<?php

namespace Teleminergmbh\EpicFhir\Database;

use BadMethodCallException;

class EpicDbConnection
{
    public function __construct()
    {
        throw new BadMethodCallException('EpicDbConnection has been removed. Use the TokenStore database driver instead.');
    }
}
