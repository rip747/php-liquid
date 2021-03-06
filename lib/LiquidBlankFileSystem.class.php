<?php
/**
 * Liquid for PHP
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://www.opensource.org/licenses/mit-license.php
 */



/**
 * A Liquid file system is way to let your templates retrieve other templates for use with the include tag.
 *
 * You can implement subclasses that retrieve templates from the database, from the file system using a different 
 * path structure, you can provide them as hard-coded inline strings, or any manner that you see fit.
 *
 * You can add additional instance variables, arguments, or methods as needed
 * 
 * @package Liquid
 */
class LiquidBlankFileSystem
{
	/**
	 * Retrieve a template file
	 *
	 * @param string $template_path
	 */
	function read_template_file($template_path)
	{
		throw new LiquidException("This liquid context does not allow includes.");
	}
	
}