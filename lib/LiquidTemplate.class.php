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
 * The template class.
 * 
 * @example 
 * $tpl = new LiquidTemplate();
 * $tpl->parse(template_source);
 * $tpl->render(array('foo'=>1, 'bar'=>2);
 *
 * @package Liquid
 */
class LiquidTemplate
{
	/**
	 * @var LiquidDocument The _root of the node tree
	 */
	private $_root;
	
	/**
	 * @var LiquidBlankFileSystem The file system to use for includes
	 */
	private $_fileSystem;
	
	/**
	 * @var array Globally included filters
	 */
	private $_filters;
	
	/**
	 * @var array Custom tags
	 */
	private static $_tags = array();


	private static $_cache;


	/**
	 * Constructor
	 *
	 * @return LiquidTemplate
	 */
	public function __construct($path = null, $cache = null)
	{
		$this->_fileSystem = (isset($path)) ? new LiquidLocalFileSystem($path) : new LiquidBlankFileSystem();
new dBug($this->_fileSystem, "fiesystem = LiquidTemplate::Init");
		$this->_filters = array();
		$this->setCache($cache);
	}


	/**
	 * 
	 *
	 */
	public function setFileSystem($fileSystem)
	{
		$this->_fileSystem = $fileSystem;
	}


	/**
	 * 
	 *
	 */
	public function setCache($cache)
	{
		if(is_array($cache))
		{
			if(isset($cache['cache']) && class_exists('LiquidCache'.ucwords($cache['cache'])) )
			{
				$classname = 'LiquidCache'.ucwords($cache['cache']);
				self::$_cache = new $classname($cache);
			}
			else
				throw new LiquidException('Invalid Cache options!');
		}
		else
		{
			self::$_cache = $cache;
		}
	}
	
	
	/**
	 * 
	 *
	 * @return object
	 */
	public static function getCache()
	{
		return self::$_cache;
	}


	/**
	 * 
	 *
	 * @return LiquidDocument
	 */
	public function getRoot()
	{
		return $this->_root;
	}

	
	/**
	 * Register custom Tags
	 *
	 * @param string $name
	 * @param string $class
	 */
	public function registerTag($name, $class)
	{
		self::$_tags[$name] = $class;
	}


	/**
	 * 
	 *
	 * @return array
	 */
	public static function getTags()
	{
		return self::$_tags;
	}


	/**
	 * Register the filter
	 *
	 * @param unknown_type $filter
	 */
	public function registerFilter($filter)
	{
		$this->_filters[] = $filter;
	}	


	/**
	 * Tokenizes the given source string
	 *
	 * @param string $source
	 * @return array
	 */
	public static function tokenize($source)
	{
		return (!$source) ? array() : preg_split(LIQUID_TOKENIZATION_REGEXP, $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	}


	/**
	 * Parses the given source string
	 *
	 * @param string $source
	 */
	public function parse($source)
	{
		$cache = self::$_cache;
new dBug($cache, "source cache");

		if(isset($cache))
		{
			if(($this->_root = $cache->read(md5($source))) != false && $this->_root->checkIncludes() != true)
			{
			}
			else
			{
				$this->_root = new LiquidDocument(LiquidTemplate::tokenize($source), $this->_fileSystem);
				$cache->write(md5($source), $this->_root);
			}
		}
		else
		{
new dBug(LiquidTemplate::tokenize($source), "Tokenized Source");
new dBug($this->_fileSystem, "File System");
			$this->_root = new LiquidDocument(LiquidTemplate::tokenize($source), $this->_fileSystem);
new dBug($this->_root, "Document Root");
		}
exit();
		return $this;
	}


	/**
	 * Renders the current template
	 *
	 * @param array $assigns An array of values for the template
	 * @param array $filters Additional filters for the template
	 * @param array $registers Additional registers for the template
	 * @return string
	 */
	public function render(array $assigns = array(), $filters = null, $registers = null)
	{
		$context = new LiquidContext($assigns, $registers);
		
		if(!is_null($filters))
		{
			if(is_array($filters))
			{
				array_merge($this->_filters, $filters);
			}
			else
			{
				$this->_filters[] = $filters;
			}
		}
	
		foreach($this->_filters as $filter)
		{
			$context->add_filters($filter);
		}
		
		return $this->_root->render($context);
	}
}