<?php

namespace Tavurn\Contracts\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface Kernel
{
    public function run(InputInterface $input, OutputInterface $output): int;
}
