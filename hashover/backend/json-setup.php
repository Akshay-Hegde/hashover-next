<?php namespace HashOver;

// Copyright (C) 2017 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


// Display source code
if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
	if (isset ($_GET['source'])) {
		header ('Content-type: text/plain; charset=UTF-8');
		exit (file_get_contents (basename (__FILE__)));
	}
}

// Tell browser output is JSON
header ('Content-Type: application/json');

// Do some standard HashOver setup work
require ('nocache-headers.php');
require ('standard-setup.php');

// Autoload class files
spl_autoload_register (function ($uri) {
	$uri = str_replace ('\\', '/', strtolower ($uri));
	$class_name = basename ($uri);

	if (!@include ('./' . $class_name . '.php')) {
		echo json_encode (array (
			'error' => $class_name . '.php" file could not be included!'
		));

		exit;
	}
});
