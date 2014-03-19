<?php

namespace TplEngine;

class Node {

	protected $instructions;
	protected $content;
	protected $params;

	public function __construct($instructions, $content, $params) {
		$this->instructions = $instructions;
		$this->content = $content;
		$this->params = $params;
	}

	public function getInstructions($type = 'start') {
		return $this->instructions[$type];
	}

	public function getParams(array $sons) {
		return $this->params;
	}

}
