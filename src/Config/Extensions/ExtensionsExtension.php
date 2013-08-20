<?php

namespace movi\Config\Extensions;

use Nette;

final class ExtensionsExtension extends Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->getConfig(); // Extension list

		foreach ($config as $name => $extension)
		{
			$this->compiler->addExtension($name, new $extension);
		}
	}

}