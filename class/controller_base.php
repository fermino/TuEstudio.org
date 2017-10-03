<?php
	abstract class ControllerBase
	{
		final public function handleRequest($http_method, $get_params)
		{
			if(method_exists($this, strtolower($http_method)))
				return $this->{$http_method}($get_params);
			// Else log error

			// extract($get_params, EXTR_OVERWRITE);

			return false;
		}
	}