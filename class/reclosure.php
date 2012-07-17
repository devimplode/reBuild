<?php
/*
helper script to extend the PHP5 closure syntax
by Jeremy Lindblom
http://www.htmlist.com/development/extending-php-5-3-closures-with-serialization-and-reflection/
with improvements by Devimplode
*/
class reClosure{
	protected $closure = NULL;
	protected $reflection = NULL;
	protected $code = NULL;
	protected $used_variables = array();

	public function __construct($function)
	{
		if ( ! $function instanceOf Closure)
			throw new InvalidArgumentException();

		$this->closure = $function;
		$this->reflection = new ReflectionFunction($function);
		$this->code = $this->_fetchCode();
		$this->used_variables = $this->_fetchUsedVariables();
	}

	public function __invoke()
	{
		$args = func_get_args();
		return $this->reflection->invokeArgs($args);
	}

	public function getClosure()
	{
		return $this->closure;
	}

	protected function _fetchCode()
	{
		// Open file and seek to the first line of the closure
		$file = new SplFileObject($this->reflection->getFileName());
		$file->seek($this->reflection->getStartLine()-1);

		// Remove comments from the code
		$comment=false;
		$stripComments=function($code)use(&$comment){
			if($comment && (stripos($code,'*/')!==false)){
				$code = preg_replace('!.*?\*/!', '', $code);
				$comment=false;
			}
			elseif($comment){
				return "";
			}
			$code = preg_replace('!/\*.*?\*/!', '', $code);
			if(stripos($code,'/*')!==false){
				$code = preg_replace('!/\*.*!s', '', $code);
				$comment=true;
			}
			$code = preg_replace('!//.*!s', '', $code);
			return $code;
		};

		// Retrieve all of the lines that contain code for the closure
		$code = '';
		while ($file->key() < $this->reflection->getEndLine())
		{
			$line = $stripComments($file->current());
			while($comment && ($file->key()+1 < $this->reflection->getEndLine())){
				$file->next();
				$line .= $stripComments($file->current());
			}
			$code .= $line;
			$file->next();
		}

		// Only keep the code defining that closure
		$begin = strpos($code, 'function');
		$end = strrpos($code, '}');
		$code = substr($code, $begin, $end - $begin + 1);

		return $code;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getParameters()
	{
		return $this->reflection->getParameters();
	}

	protected function _fetchUsedVariables()
	{
		// Make sure the use construct is actually used
		$use_index = stripos($this->code, 'use');
		if ($use_index===false || $use_index > strpos($this->code,'{'))
			return array();

		// Get the names of the variables inside the use statement
		$begin = strpos($this->code, '(', $use_index) + 1;
		$end = strpos($this->code, ')', $begin);
		$vars = explode(',', substr($this->code, $begin, $end - $begin));

		// Get the static variables of the function via reflection
		$static_vars = $this->reflection->getStaticVariables();

		// Only keep the variables that appeared in both sets
		$used_vars = array();
		foreach ($vars as $var)
		{
			$var = preg_replace('/[\r\n\t\s\0\x0B\$&]*/', '', $var);
			if(isset($vars['&$'.$var]))
				$used_vars[$var]=&$static_vars[$var];
			else
				$used_vars[$var] = $static_vars[$var];
		}
		return $used_vars;
	}

	public function getUsedVariables()
	{
		$this->used_variables = $this->_fetchUsedVariables();
		return $this->used_variables;
	}

	public function __sleep()
	{
		$this->used_variables = $this->_fetchUsedVariables();
		return array('code', 'used_variables');
	}

	public function __wakeup()
	{
		extract($this->used_variables);
		eval('$_function = '.$this->code.';');
		if (isset($_function) AND $_function instanceOf Closure)
		{
			$this->closure = $_function;
			$this->reflection = new ReflectionFunction($_function);
		}
		else
			throw new Exception();
	}
}
?>