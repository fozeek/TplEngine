<?php

class TplEngine {

	protected $file;
	protected $compiledFile;
	protected $view;
	protected $tags;
	protected $variable = '([a-zA-Z0-9\."]*)';
	protected $filter_storage = array();

	public function __construct($template, $vars = array()) {
		$object = $this;
		$this->tags = [
			'for' => [
				'pattern' => 'for '.$this->variable.' from '.$this->variable.' to '.$this->variable,
				'replace' => function($values) use ($object) {
					return '<?php for($'.$values[0].'='.$object->setVar($values[1]).';$'.$values[0].'<='.$object->setVar($values[2]).';$'.$values[0].'++) { ?>';
				}
			],
			'endfor' => [
				'pattern' => 'endfor',
				'replace' => '<?php } ?>'
			],
			'definition' => [
				'pattern' => 'set '.$this->variable.' = ([^}]+)',
				'replace' => function($values) use ($object) {
					return '<?php '.$object->setVar($values[0]).' = '.$values[1].' ?>';
				}
			],
			'foreach' => [
				'pattern' => 'for '.$this->variable.' in '.$this->variable,
				'replace' => function($values) use ($object) {
					return '<?php foreach('.$object->setVar($values[1]).' as '.$object->setVar($values[0]).') { ?>';
				}
			],
			'filter' =>  [
				'pattern' => 'filter:([a-z]*)',
				'replace' => function($values) use ($object) {
					$object->filter_storage[] = $values[0];
					return '<?php ob_start() ?>';
				}
			],
			'endfilter' =>  [
				'pattern' => 'endfilter',
				'replace' => function($values) use ($object) {
					$function = end($object->filter_storage);
					$object->filter_storage = array_pop($object->filter_storage);
					return '<?= '.$function.'(ob_get_clean()) ?>';
				}
			],
			'variable' =>  [
				'pattern' => $this->variable,
				'replace' => function($values) use ($object) {
					return '<?= '.$object->setVar($values[0]).' ?>';
				}
			],
			'function' =>  [
				'pattern' => '([a-zA-Z]+)[ ]{0,1}\(([^}]+)\)',
				'replace' => function($values) use ($object) {
					$vars = explode(',', $values[1]);
					$vars = array_map('trim', $vars);
					$vars = array_map([$object, 'setVar'], $vars);
					return '<?php /* '.$values[0].'('.implode(', ', $vars).') */  ?>';
				}
			],
			'helper' =>  [
				'pattern' => 'helper:'.$this->variable.' ([^}]+)',
				'replace' => function($values) use ($object) {
					$vars = explode(' ', $values[1]);
					$vars = array_map([$object, 'setVar'], array_filter($vars));
					return '<?php /* $this->helper("'.ucfirst($values[0]).'", ['.implode(', ', $vars).']) */ ?>';
				}
			],
		];
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

	public function compileFile() {
		$this->compiledFile = $this->file;
		foreach ($this->tags as $tag) {
			$matches = array();
			$return = preg_match_all('#{{[ ]{0,1}'.$tag['pattern'].'[ ]{0,1}}}#', $this->compiledFile, $matches);
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


}