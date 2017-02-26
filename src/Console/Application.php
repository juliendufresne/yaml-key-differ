<?php

declare(strict_types=1);

namespace JulienDufresne\YAMLKeyDiffer\Console;

use JulienDufresne\YAMLKeyDiffer\Console\Command\DiffCommand;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    const VERSION = '1.0.0-DEV';

    public function __construct()
    {
        parent::__construct('YAML Key Differ', self::VERSION);

        $this->add(new DiffCommand());
    }
}
