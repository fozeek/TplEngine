<?php

namespace TplEngine;

class TplEngine {

    protected $template;
    protected $compiledTemplate;
    protected $view;
    protected $tags;
    protected $variable = '([a-zA-Z0-9\."]*)';

    private function __construct($template, $vars = array()) {
        $this->bootstrap();
        $this->template = $template;
        $this->compileTemplate();
    }

    public static function fromFile($template, $vars = array()) {
        return new self(file_get_contents($template), $vars);
    }

    public static function fromText($template, $vars = array()) {
        return new self($template, $vars);
    }

    public function render($vars = array()) {
        ob_start();
        extract($vars);
        eval(" ?>".$this->compiledTemplate."<?php ");
        return ob_get_clean();
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

    public function getCompiledTemplate() {
        return $this->compiledTemplate;
    }

    public function compileTemplate() {
        $this->compiledTemplate = $this->template;
        foreach ($this->tags as $tag) {
            $matches = array();
            $return = preg_match_all('#{{[ ]?'.$tag['pattern'].'[ ]?}}#', $this->compiledTemplate, $matches);
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

                $this->compiledTemplate = str_replace($matches[0][$cpt], $tag_replace, $this->compiledTemplate);
            }
        }
    }

    protected function bootstrap() {
        require __DIR__.'../boostrap.php';
    }

}
