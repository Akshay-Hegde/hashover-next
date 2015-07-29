<?php

	// Copyright (C) 2010-2015 Jacob Barkdull
	//
	//	This file is part of HashOver.
	//
	//	HashOver is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	HashOver is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		}
	}

	class Locales
	{
		public $locale;
		public $errors = array ();

		public
		function __construct ($language)
		{
			// Path to PHP locale file
			$locale_file_path = __DIR__ . '/locales/' . strtolower ($language) . '.php';

			// Default to English if locale doesn't exist
			if (!file_exists ($locale_file_path)) {
				$locale_file_path = __DIR__ . '/locales/en.php';
			}

			// Set language locale
			if (@include ($locale_file_path)) {
				$this->locale = $locale;
			} else {
				$this->errors[] = strtoupper ($language) . ' locale file could not be included!';
			}
		}

		// Return a locale string, optionally add C-style escaping
		public
		function locale ($string, $addcslashes = false, $charlist = "'")
		{
			if ($addcslashes) {
				if (is_array ($this->locale[$string])) {
					$escaped_array = array ();

					foreach ($this->locale[$string] as $key => $value) {
						$escaped_array[$key] = addcslashes ($value, $charlist);
					}

					return $escaped_array;
				}

				return addcslashes ($this->locale[$string], $charlist);
			}

			return $this->locale[$string];
		}
	}

?>
