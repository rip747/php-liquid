<?php
/**
 * An if statement
 * 
 * @example
 * {% if true %} YES {% else %} NO {% endif %}
 * 
 * will return:
 * YES
 *
 * @package Liquid
 */
class LiquidTagIf extends LiquidDecisionBlock
{

	/**
	 * Array holding the nodes to render for each logical block
	 *
	 * @var array
	 */
	private $_nodelistHolders = array();

	/**
	 * Array holding the block type, block markup (conditions) and block nodelist
	 *
	 * @var array
	 */
	private $_blocks = array();


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param LiquidFileSystem $file_system
	 * @return IfLiquidTag
	 */
	public function __construct($markup, &$tokens, &$file_system)
	{
new dBug(func_get_args(), "Arguments = LiquidTagIf::Init");
//exit();
		$this->_nodelist = &$this->_nodelistHolders[count($this->_blocks)];
new dBug($this->_nodelist, "_nodelist = LiquidTagIf::Init");
new dBug($this->_nodelistHolders, "_nodelistHolders = LiquidTagIf::Init");


		array_push($this->_blocks, array(
			'if', $markup, &$this->_nodelist
		));
new dBug($this->_blocks, 'blocks  = LiquidTagIf::Init');
new dBug($tokens, 'tokens  = LiquidTagIf::Init');
		parent::__construct($markup, $tokens, $file_system);

	}


	/**
	 * Handler for unknown tags, handle else tags
	 *
	 * @param string $tag
	 * @param array $params
	 * @param array $tokens
	 */
	function unknown_tag($tag, $params, &$tokens)
	{
new dBug(func_get_args(), 'Start = LiquidIf::unknown_tag');
		if($tag == 'else' || $tag == 'elsif')
		{
new dBug(count($this->_blocks), 'block count');
new dBug($this->_blocks, 'blocks');
new dBug($this->_nodelist, '_nodelist');
new dBug($this->_nodelistHolders, '_nodelistHolders');
			/* Update reference to nodelistHolder for this block */
			$this->_nodelist = &$this->_nodelistHolders[count($this->_blocks) + 1];
			$this->_nodelistHolders[count($this->_blocks) + 1] = array();
new dBug($this->_blocks, 'blocks 2');
new dBug($this->_nodelist, '_nodelist 2');
new dBug($this->_nodelistHolders, '_nodelistHolders 2');

			array_push($this->_blocks, array(
				$tag, $params, &$this->_nodelist
			));
			
new dBug($this->_blocks, 'blocks 3');
new dBug($this->_nodelist, '_nodelist 3');
new dBug($this->_nodelistHolders, '_nodelistHolders 3');

		}
		else
		{
			parent::unknown_tag($tag, $params, $tokens);
		}
	}


	/**
	 * Render the tag
	 *
	 * @param LiquidContext $context
	 */
	public function render(&$context)
	{
		$context->push();

		$logicalRegex = new LiquidRegexp('/\s+(and|or)\s+/');
		$conditionalRegex = new LiquidRegexp('/(' . LIQUID_QUOTED_FRAGMENT . ')\s*([=!<>a-z_]+)?\s*(' . LIQUID_QUOTED_FRAGMENT . ')?/');

		$result = '';

		foreach($this->_blocks as $i => $block)
		{
			if($block[0] == 'else')
			{
				$result = $this->render_all($block[2], $context);

				break;
			}

			if($block[0] == 'if' || $block[0] == 'elsif')
			{
				/* Extract logical operators */
				$logicalRegex->match($block[1]);

				$logicalOperators = $logicalRegex->matches;
				array_shift($logicalOperators);

				/* Extract individual conditions */
				$temp = $logicalRegex->split($block[1]);

				$conditions = array();

				foreach($temp as $condition)
				{
					if($conditionalRegex->match($condition))
					{
						$left = (isset($conditionalRegex->matches[1])) ? $conditionalRegex->matches[1] : null;
						$operator = (isset($conditionalRegex->matches[2])) ? $conditionalRegex->matches[2] : null;
						$right = (isset($conditionalRegex->matches[3])) ? $conditionalRegex->matches[3] : null;

						array_push($conditions, array(
								'left' => $left,
								'operator' => $operator,
								'right' => $right
						));
					}
					else
					{
						throw new LiquidException("Syntax Error in tag 'if' - Valid syntax: if [condition]");
					}
				}

				if(count($logicalOperators))
				{
					/* If statement contains and/or */
					$display = true;

					foreach($logicalOperators as $k => $logicalOperator)
					{
						if($logicalOperator == 'and')
						{
							$display = $this->interpret_condition($conditions[$k]['left'], $conditions[$k]['right'], $conditions[$k]['operator'], $context) && $this->interpret_condition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context);
						}
						else
						{
							$display = $this->interpret_condition($conditions[$k]['left'], $conditions[$k]['right'], $conditions[$k]['operator'], $context) || $this->interpret_condition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context);
						}
					}

				}
				else
				{
					/* If statement is a single condition */
					$display = $this->interpret_condition($conditions[0]['left'], $conditions[0]['right'], $conditions[0]['operator'], $context);
				}

				if($display)
				{
					$result = $this->render_all($block[2], $context);

					break;
				}
			}
		}

		$context->pop();

		return $result;
	}
}

