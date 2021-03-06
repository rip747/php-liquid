<?php
/**
 * Base class for blocks.
 * 
 * @package Liquid
 */
class LiquidBlock extends LiquidTag
{
	/**
	 * @var array
	 */
	protected $_nodelist;


	/**
	 * 
	 *
	 * @return array
	 */
	public function getNodelist()
	{
		return $this->_nodelist;
	}


	/**
	 * Parses the given tokens
	 *
	 * @param array $tokens
	 */
	public function parse(&$tokens)
	{
		$start_regexp = new LiquidRegexp('/^'.LIQUID_TAG_START.'/');
		$tag_regexp = new LiquidRegexp('/^'.LIQUID_TAG_START.'\s*(\w+)\s*(.*)?'.LIQUID_TAG_END.'$/');
		$variable_start_regexp = new LiquidRegexp('/^'.LIQUID_VARIABLE_START.'/');
new dBug("Start = LiquidBlock::Parse");
new dBug(func_get_args(), "Arguments = LiquidBlock::Parse");

		$this->_nodelist = array();
new dBug($this->_nodelist, 'when nodelist intialized');
		if(!is_array($tokens))
		{
			return;
		}
		$tags = LiquidTemplate::getTags();
new dBug($tags, "all tags");
		while(count($tokens))
		{
      $token = array_shift($tokens);
	  
new dBug("token: " .$token);
new dBug($this->_nodelist, 'in tokens loop: _nodelist');

			if($start_regexp->match($token))
			{
				if($tag_regexp->match($token))
				{
new dBug($tag_regexp->matches, "token tag matches");

					// if we found the proper block delimitor just end parsing here and let the outer block proceed 
					if($tag_regexp->matches[1] == $this->block_delimiter())
					{
//new dBug($this->end_tag(), "token tag matches");exit();
						return $this->end_tag();
					}

					if(array_key_exists($tag_regexp->matches[1], $tags))
						$tag_name = $tags[$tag_regexp->matches[1]];
					else			
						$tag_name = 'LiquidTag'.ucwords($tag_regexp->matches[1]);// search for a defined class of the right name, instead of searching in an array	
new dBug($tag_name);
//exit();
					// fetch the tag from registered blocks
					if(class_exists($tag_name))
					{
new dBug('tag name found: '.$tag_name);
new dBug($this->_nodelist, 'before tag_name call');

						$temp = new $tag_name($tag_regexp->matches[2], $tokens, $this->file_system);
new dBug($temp, 'after tag_name call');
exit();

//						$this->_nodelist[] = new $tag_name($tag_regexp->matches[2], $tokens, $this->file_system);
new dBug($this->_nodelist, 'after tag_name call');
					}
					else
					{
new dBug('unknown tag: '.$tag_name);
//exit();
						$this->unknown_tag($tag_regexp->matches[1], $tag_regexp->matches[2], $tokens);	
					}
				}
				else
				{
					throw new LiquidException("Tag $token was not properly terminated");// harry
				}
								
			}
			elseif($variable_start_regexp->match($token))
			{
new dBug($this->_nodelist, 'before create_variable call');		
				$this->_nodelist[] = $this->create_variable($token);
new dBug($this->_nodelist, 'after create_variable call');
			}
			elseif($token != '')
			{
new dBug($this->_nodelist, 'before blank token assignment');	
				$this->_nodelist[] = $token;
new dBug($this->_nodelist, 'after blank token assignment');
			}
		}
new dBug('shouldnnot be here');
		$this->assert_missing_delimitation();
	}
	
	
	/**
	 * An action to execute when the end tag is reached
	 *
	 */
	function end_tag()
	{
	}


	/**
	 * Handler for unknown tags
	 *
	 * @param string $tag
	 * @param array $params
	 * @param array $tokens
	 */
	function unknown_tag($tag, $params, & $tokens)
	{
		switch ($tag)
		{
			case 'else':
				throw new LiquidException($this->block_name()." does not expect else tag");
			
			case 'end':
				throw new LiquidException("'end' is not a valid delimiter for ".$this->block_name()." tags. Use ".$this->block_delimiter());
			
			default:
				throw new LiquidException("Unkown tag $tag");
		}
		
	}


	/**
	 * Returns the string that delimits the end of the block
	 *
	 * @return string
	 */
	function block_delimiter()
	{
		return "end".$this->block_name();
	}


	/**
	 * Returns the name of the block
	 *
	 * @return string
	 */
	function block_name()
	{
		return str_replace('liquidtag', '', strtolower(get_class($this)));
	}


	/**
	 * Create a variable for the given token
	 *
	 * @param string $token
	 * @return LiquidVariable
	 */
	function create_variable($token)
	{
		$variable_regexp = new LiquidRegexp('/^'.LIQUID_VARIABLE_START.'(.*)'.LIQUID_VARIABLE_END.'$/');
		if($variable_regexp->match($token))
			return new LiquidVariable($variable_regexp->matches[1]);	
		
		throw new LiquidException("Variable $token was not properly terminated");
	}


	/**
	 * Render the block.
	 *
	 * @param LiquiContext $context
	 * @return string
	 */
	public function render(&$context)
	{
		return $this->render_all($this->_nodelist, $context);
	}


	/**
	 * This method is called at the end of parsing, and will through an error unless
	 * this method is subclassed, like it is for LiquidDocument
	 *
	 * @return bool
	 */
	function assert_missing_delimitation()
	{
		throw new LiquidException($this->block_name()." tag was never closed");
	}


	/**
	 * Renders all the given nodelist's nodes
	 *
	 * @param array $list
	 * @param LiquidContext $context
	 * @return string
	 */
	//public function render_all(array $list, &$context)
	protected function render_all(array $list, &$context)
	{
		$result = '';
		
		foreach($list as $token)
		{
			$result .= (is_object($token) && method_exists($token, 'render')) ? $token->render($context) : $token;
		}

		return $result;
	}
}
