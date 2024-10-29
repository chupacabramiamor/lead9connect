<?php

namespace Chupacabramiamor\Lead9Connect\Contracts;

interface ReplaceResponseData
{
	/**
	 * Заміщує данні, які прийшли з серверу, на користувацькі
	 *
	 * @return mixed
	 */
	public function replace($input): mixed;
}
