<?php
/*
 * Igo-php : A morphological analyzer. (http://sourceforge.jp/projects/igo-php/)
 * Copyright 2011, Toshio HIRAI. <toshio.hirai@gmail.com>
 * (This software is based on Igo Java Version (c) Takeru Ohta <phjgt308@gmail.com>)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * Contributors:
 *  Takeru Ohta <phjgt308@gmail.com> - original idea
 *  Toshio HIRAI <toshio.hirai@gmail.com> - initial implementation
 *
 */

$IGO_BASEDIR = dirname(__FILE__).DIRECTORY_SEPARATOR;

/** IGO root directory */
if (!defined('IGO_ROOT')) {
	define('IGO_ROOT', dirname(__FILE__) . '/');
	require(IGO_ROOT . 'Igo/Autoloader.php');
}
class Igo{

}
