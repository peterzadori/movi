<?php

namespace movi\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssetsCommand extends Command
{

	protected function configure()
	{
		$this->setName('assets:rebuild')
			->setDescription('Rebuild Assets files');
	}


	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$manager = $this->getHelper('container')->getByType('movi\Components\Assets\AssetsManager');

		$manager->rebuild();
		$output->writeLn('Assets rebuilt.');

		return 0;
	}

}