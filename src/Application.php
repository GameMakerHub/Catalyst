<?php

namespace Catalyst;

final class Application extends \Symfony\Component\Console\Application
{

    public function __construct()
    {
        parent::__construct('GameMakerHub Catalyst', '0.1.0');
    }

}
