<?php

namespace TplEngine;

class Parser {

	protected $text;
	protected $quote = "/({{([^}]|}[^}])+}})/i";
	protected $parsedText;
    protected $variable = '([a-zA-Z0-9\."]*)';
	protected $tags;

	public function __construct($text) {
		$this->text = $text;
		$this->bootstrap();
	}

	public function parse() {
		$matches = array();
		preg_match_all($this->quote, $this->text, $matches);
		$matches = $matches[1];
		$matches = array_map(function($value) {
			return trim(str_replace(["\r", "\t", "  ", "\n"], '', $value));
		}, $matches);


		echo '<pre>';
		var_dump($matches);
		echo '</pre>';
	}

	public function parseTest() {
		$text = preg_replace_callback($this->quote, function($matches) {
			return '#QUOTE#'.$matches[1].'#QUOTE#';
			echo $matches[1].'<br />';
		}, $this->text);
		echo '<pre>';
		echo htmlentities($text);
		echo '</pre>';
	}

	public function parseTestTest() {
		$instructions = array();
		$text = preg_replace_callback($this->quote, function($matches) use (&$instructions) {
			$instructions[] = $matches[1];
			return '{{ #REF# }}'; // Ajout d'un espace avant le }} sinon le split bug et duplique le dernier caractère matché sur la case suivante
		}, $this->text);
		$text = preg_split($this->quote, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

		// $text = array_map(function($value) {
		// 	return /*trim(*/str_replace(["\r", "\t", "  ", "\n"], '', $value)/*)*/;
		// }, $text);

		$text = array_values(array_filter($text));


		// $instructions = array();
		// foreach ($text as $key => $value) {
		// 	if(strrpos($value, self::$QUOTE_CAPTURE)!==false) {
		// 		$instructions[] = substr($value, strlen(self::$QUOTE_CAPTURE+1));
		// 	}
		// }

		$instructionsTmp = $instructions;
		foreach ($this->tags as $name => $tag) {
			$result = preg_grep("/^{{[ ]?".$tag["pattern"]."[ ]?}}$/i", $instructionsTmp);
			foreach ($result as $key => $value) {
				$instructions[$key] = array(
					'tag' => $name,
					'instruction' => trim(str_replace(["\r", "\t", "  ", "\n", "{{", "}}"], '', $value))
				);
				unset($instructionsTmp[$key]); // pour que la recherche soit plus rapide pour les tags suivants
			}
		}


		$cpt = 0;
		$instructionsPosition = preg_grep("/^{{ \#REF\# }}$/i", $text);
		foreach ($instructionsPosition as $key => $value) {
			$text[$key] = $instructions[$cpt++];
		}

		// $this->parsedText = new Node();
		// $dadNode = $this->parsedText;
		// $tmp = array();
		// $instruction = array();
		// foreach ($text as $key => $value) {
		// 	if(strrpos($value, self::$QUOTE_CAPTURE)!==false) {
		// 		$instruction[] = substr($value, strlen(self::$QUOTE_CAPTURE+1));
		// 	}
		// 	else {
		// 		$tmp[] = $value;
		// 	}
		// }
		echo '<pre>';
		var_dump($instructions);
		var_dump($text);
		echo '</pre>';
	}

	protected function bootstrap() {
        require __DIR__.'../boostrap.php';
    }

}
