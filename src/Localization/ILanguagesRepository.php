<?php

namespace movi\Localization;

interface ILanguagesRepository
{

	public function getActive();

	public function findByCode($code);

}