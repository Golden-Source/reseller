<?php
namespace GoldenSource;
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.ir)
 */
class PHPView
{
	public $vars = [];
	public $filename;
	private $name;

	public function __construct($name)
	{
		$this->name = $name;
		$this->filename = $name.'.php';
	}

	public static function render($name, $vars = NULL)
	{
		$view = new self($name);

		return $view->output($vars);
	}

	public function output($vars = NULL)
	{
		ob_start();
		$this->display($vars);

		return ob_get_clean();
	}

	public function display($vars = NULL)
	{
		$vars = is_null($vars) ? $this->vars : $vars;
		$vars['batch_file_name'] = $this->name;
		if($this->name == 'empty') {
			echo $vars['content'];

			return;
		}
		require($this->filename);
	}
} 
