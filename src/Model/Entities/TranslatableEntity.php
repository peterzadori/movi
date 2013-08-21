<?php

namespace movi\Model\Entities;

/**
 * @property Language $language m:hasOne
 */
abstract class TranslatableEntity extends IdentifiedEntity
{

	public function getTranslatableColumns()
	{
		$columns = [];

		/** @var $property \LeanMapper\Reflection\Property */
		foreach ($this->getCurrentReflection()->getEntityProperties() as $property)
		{
			if ($property->hasCustomFlag('translate')) {
				$columns[] = $property->getColumn();
			}
		}

		return array_flip($columns);
	}

}