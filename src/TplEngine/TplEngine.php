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
                'pattern' => 'filter:([a-z]*)[ ]?}}(([^e]|e[^n]|en[^d]|end[^f]|endf[^i]|endfi[^l]|endfil[^t]|endfilt[^e]|endfilte[^r])+){{[ ]?endfilter',
                'replace' => function($values) {
                    $filter = $values[0];
                    $content = self::fromText($values[1])->getCompiledTemplate();
                    return '<?php ob_start() ?>'.$content.'<?= '.$filter.'(ob_get_clean())?>';
                }
            ],
            // 'endfilter' =>  [
            //     'pattern' => 'endfilter',
            //     'replace' => function($values) {
            //         echo 'CLOSE_STORAGE : ';var_dump($this->filter_storage);echo '<br />';echo '<br />';
            //         $function = end($this->filter_storage);
            //         echo 'CLOSE_FUNCTION : ';var_dump($function);echo '<br />';echo '<br />';
            //         $this->filter_storage = array_pop($this->filter_storage);
            //         echo 'CLOSE_END_STORAGE : ';var_dump($this->filter_storage);echo '<br />';echo '<br />';
            //         return '<?= /*$this->filter...*/'.$function.'(ob_get_clean()) ';
            //     }
            // ],
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

                    $content = self::fromText($values[2])->getCompiledTemplate();

                    // preg_replace_callback('#{{[ ]?'.$this->variable.'[ ]?}}#', function($matches) use (&$content) {
                    //     $content = str_replace($matches[0], '".'.$this->setVar($matches[1]).'."', $content);
                    // }, $content);


                    return '<?php function '.$function.'('.implode(', ', $args).') { ?> '.trim($content).' <?php } ?>';
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