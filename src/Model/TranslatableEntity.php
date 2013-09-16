<?php

namespace movi\Model;

/**
 * Class TranslatableEntity
 * @package movi\Model
 * @property \movi\Model\Entities\Language $language m:hasOne(language_id:languages) m:translate
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