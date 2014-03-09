<?php

namespace TplEngine;

class TplEngine {

	/*

		A voir : preg_replace_callback


	*/

	protected $file;
	protected $compiledFile;
	protected $view;
	protected $tags;
	protected $variable = '([a-zA-Z0-9\."]*)';
	protected $filter_storage = array();
	protected $macros = array();

	public function __construct($template, $vars = array()) {
		$this->bootstrap();
		$this->file = file_get_contents($template);
		$this->compileFile();
		$this->generateView();
	}

	public function render() {
		return $this->view;
	}

	protected function generateView() {
		ob_start();
		eval(" ?>".$this->compiledFile."<?php ");
		$this->view = ob_get_clean();
	}

	protected function setVar($var) {
		if(strpos($var, "\"")===false && !ctype_digit($var)) {
			if(strpos($var, ".")!==false) {
				$expl = explode(".", $var);
				$var = $expl[0];
				unset($expl[0]);
				foreach ($expl as $key) {
					if(!ctype_digit($key)) {
						$var .= "[\"".$key."\"]";
					}
					else {
						$var .= "[".$key."]";
					}
				}
			}
			$var = '$'.$var;
		}
		return $var;
	}

	public function getCompiledFile() {
		return $this->compiledFile;
	}

	public function compileFile() {
		$this->compiledFile = $this->file;
		foreach ($this->tags as $tag) {
			$matches = array();
			$return = preg_match_all('#{{[ ]?'.$tag['pattern'].'[ ]?}}#', $this->compiledFile, $matches);
			$count = count($matches[0]);

			$count_replace = count($matches)-1;

			for($cpt = 0;$cpt<$count;$cpt++) {
				$tag_replace = $tag['replace'];
				if(is_string($tag_replace)) {
					for ($cpt2 = 0;$cpt2<$count_replace;$cpt2++) {
						$tag_replace = str_replace('{{'.$cpt2.'}}', $matches[$cpt2+1][$cpt], $tag_replace);
					}
				}
				else {
					$data = array();
					for ($cpt2 = 0;$cpt2<$count_replace;$cpt2++) {
						array_push($data, $matches[$cpt2+1][$cpt]);
					}
					$tag_replace = $tag_replace($data);
				}

				$this->compiledFile = str_replace($matches[0][$cpt], $tag_replace, $this->compiledFile);
			}
		}
	}

	protected function bootstrap() {
		$this->tags = [
			'for' => [
				'pattern' => 'for '.$this->variable.' from '.$this->variable.' to '.$this->variable,
				'replace' => function($values) {
					return '<?php for($'.$values[0].'='.$this->setVar($values[1]).';$'.$values[0].'<='.$this->setVar($values[2]).';$'.$values[0].'++) { ?>';
				}
			],
			'endfor' => [
				'pattern' => 'endfor',
				'replace' => '<?php } ?>'
			],
			'definition' => [
				'pattern' => 'set '.$this->variable.' = ([^}]+)',
				'replace' => function($values) {
					return '<?php '.$this->setVar($values[0]).' = '.$values[1].' ?>';
				}
			],
			'foreach' => [
				'pattern' => 'for '.$this->variable.' in '.$this->variable,
				'replace' => function($values) {
					return '<?php foreach('.$this->setVar($values[1]).' as '.$this->setVar($values[0]).') { ?>';
				}
			],
			'filter' =>  [
				'pattern' => 'filter:([a-z]*)',
				'replace' => function($values) {
					$this->filter_storage[] = $values[0];
					return '<?php ob_start() ?>';
				}
			],
			'endfilter' =>  [
				'pattern' => 'endfilter',
				'replace' => function($values) {
					$function = end($this->filter_storage);
					$this->filter_storage = array_pop($this->filter_storage);
					return '<?= /*$this->filter...*/'.$function.'(ob_get_clean()) ?>';
				}
			],
			'function' =>  [
				'pattern' => '([a-zA-Z_]+)[ ]?\(([^}]+)\)',
				'replace' => function($values) {
					$vars = explode(',', $values[1]);
					$vars = array_map('trim', $vars);
					$vars = array_map([$this, 'setVar'], $vars);
					return '<?= /* $this-> */ '.$values[0].'('.implode(', ', $vars).')  ?>';
				}
			],
			'helper' =>  [
				'pattern' => 'helper:'.$this->variable.' ([^}]+)',
				'replace' => function($values) {
					$vars = explode(' ', $values[1]);
					$vars = array_map([$this, 'setVar'], array_filter($vars));
					return '<?php /* $this->helper("'.ucfirst($values[0]).'", ['.implode(', ', $vars).']) */ ?>';
				}
			],
			'macro' =>  [
				'pattern' => 'macro ([a-zA-Z\_]+)[ ]?\((([a-zA-Z0-9\."]*[, ]?)*)\)[ ]?}}(([^e]|e[^n]|en[^d]|end[^m]|endm[^a]|endma[^c]|endmac[^r]|endmacr[^o])+){{[ ]?endmacro',
				'replace' => function($values) {
					$values = array_values(array_filter($values));
					$function = ucfirst($values[0]);
					$args = explode(', ', $values[1]);
					$args = array_map([$this, 'setVar'], array_filter($args));

					$content = $values[2];

					preg_replace_callback('#{{[ ]?'.$this->variable.'[ ]?}}#', function($matches) use (&$content) {
						$content = str_replace($matches[0], '".'.$this->setVar($matches[1]).'."', $content);
					}, $content);


					return '<?php function '.$function.'('.implode(', ', $args).') { return "'.trim($content).'"; } ?>';
				}
			],
			'variable' =>  [
				'pattern' => $this->variable,
				'replace' => function($values) {
					return '<?= '.$this->setVar($values[0]).' ?>';
				}
			],
		];
	}

}