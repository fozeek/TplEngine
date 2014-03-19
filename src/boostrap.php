<?php

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
                return '<?php ob_start() ?>'.$content.'<?= '.$filter.'(ob_get_clean()) ?>';
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
                /*  TODO :
                    Check if a new macro is created in this macro => throw exception
                */

                $values = array_values(array_filter($values));
                $function = ucfirst($values[0]);
                $args = explode(', ', $values[1]);
                $args = array_map([$this, 'setVar'], array_filter($args));

                $content = self::fromText($values[2])->getCompiledTemplate();

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
