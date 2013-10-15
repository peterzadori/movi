<?php

namespace movi\Config\Extensions;

use movi\Config\CompilerExtension;

final class ExtensionsExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->getConfig();

		foreach ($config as $name => $extension)
		{
			$this->compiler->addExtension($name, new $extension);
		}
	}

}