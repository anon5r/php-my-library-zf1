<?php

<<<<<<< HEAD
class My_KeyValueStore_Exception extends Exception {}
=======
class My_KeyValueStore_Exception extends Exception {

	const CODE_EXTENSION_UNAVAILABLE	= 2000;
	const CODE_CLASS_NOTEXIST			= 2001;

	const CODE_CONNECTION_FAILED		= 1000;
	const CODE_CONNECTION_UNREACHABLE	= 1001;
	const CODE_CONNECTION_REJECTED		= 1002;
	const CODE_CONNECTION_CLOSED		= 1010;
	const CODE_KEY_NOTFOUND				= 4000;
}
>>>>>>> cca4de32cc305a89fa98d555ddcb5d21c2a00c07
