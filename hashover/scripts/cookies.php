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

	class Cookies
	{
		public $domain;
		public $expire;
		public $secure = false;

		public
		function __construct ($domain, $expire, $secure = false)
		{
			// Set domain and default expiration date from parameters
			$this->domain = $domain;
			$this->expire = $expire;

			// Transmit cookies over HTTPS if set so in Settings
			if ($secure) {
				$this->secure = !empty ($_SERVER['HTTPS']) ? true : false;
			}
		}

		// Set a cookie, with either a specific expiration date or the one in Settings
		public
		function set ($name, $value, $date = '')
		{
			$date = !empty ($date) ? $date : $this->expire;
			setcookie ($name, $value, $date, '/', $this->domain, $this->secure, true);
		}

		// Expire a cookie by setting its expiration date to 1
		public
		function expireCookie ($cookie)
		{
			if (isset ($_COOKIE[$cookie])) {
				setcookie ($cookie, '', 1, '/', $this->domain, $this->secure, true);
			}
		}

		// Expire HashOver's default cookies
		public
		function clear ()
		{
			// Expire message cookie
			if (isset ($_COOKIE['message'])) {
				setcookie ('message', '', 1, '/', $this->domain, $this->secure, true);
			}

			// Expire error cookie
			if (isset ($_COOKIE['error'])) {
				setcookie ('error', '', 1, '/', $this->domain, $this->secure, true);
			}

			// Expire comment and reply failure cookie(s)
			if (isset ($_COOKIE['success']) and $_COOKIE['success'] === 'no') {
				setcookie ('success', '', 1, '/', $this->domain, $this->secure, true);

				if (!empty ($_COOKIE['replied'])) {
					setcookie ('replied', '', 1, '/', $this->domain, $this->secure, true);
				}
			}
		}
	}

?>
