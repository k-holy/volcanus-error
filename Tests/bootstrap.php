<?php
/**
 * PHP versions 5
 *
 * @copyright  2011 k-holy <k.holy74@gmail.com>
 * @author     k.holy74@gmail.com
 * @license    http://www.opensource.org/licenses/mit-license.php  The MIT License (MIT)
 */
spl_autoload_register(function($className) {
	if (0 === strpos(ltrim($className, '/'), 'Volcanus\Error')) {
		require_once realpath(__DIR__ . '/..') . substr(
			str_replace('\\', '/', $className),
			strlen('Volcanus\Error')
		).'.php';
	}
}, true, true);
