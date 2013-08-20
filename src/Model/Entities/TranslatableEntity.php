<?php

namespace movi\Model\Entities;

abstract class TranslatableEntity extends IdentifiedEntity
{

	/**
	 * @return array|null
	 */
	public function getTranslatableColumns()
	{
		static $columns;

		if ($columns === NULL) {
			/** @var $property \LeanMapper\Reflection\Property */
			foreach ($this->getCurrentReflection()->getEntityProperties() as $property)
			{
				if ($property->hasCustomFlag('translate')) {
					$columns[$property->getColumn()] = true;
				}
			}
		}

		return $columns;
	}


	/**
	 * @return array
	 */
	public function getModifiedRowData()
	{
		$modified = $this->row->getModifiedData();
		$translatableColumns = $this->getTranslatableColumns();

		$data = array_diff_key($modified, $translatableColumns);

		if (count($data) == 0) {
			$data['id'] = $this->id;
		}

		// To persist
		return $data;
	}


	/**
	 * @return array
	 */
	public function getModifiedTranslationsData()
	{
		$modified = $this->row->getData();
		$translatableColumns = $this->getTranslatableColumns();

		return array_intersect_key($modified, $translatableColumns);
	}

}