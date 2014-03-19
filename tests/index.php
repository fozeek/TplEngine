<?php

require '../vendor/autoload.php';


$parser = new TplEngine\Parser(file_get_contents('template.tpl'));
$parser->parseTestTest();

Parser::addTag('crypt {{number}} {{var}}', function($node) {
	$raw = $node->getRow(); // text non formaté de l'instruction du bloc
	$params = $node->getParams(); // les parametres
	$content = $node->getContent();
	for ($cpt = 0; $cpt < $params[0]; $cpt++) {
		$content = crypt($content, $params[1]);
	}
	return $content;
}, 'endcrypt');

Parser::addTag('name')
	->startPattern('crypt {{number}} {{var}}')
	->action(function($node) {
		$raw = $node->getRow(); // text non formaté de l'instruction du bloc
		$params = $node->getParams(); // les parametres
		$content = $node->getContent();
		for ($cpt = 0; $cpt < $params[0]; $cpt++) {
			$content = crypt($content, $params[1]);
		}
		return $content;
	})
	->endPattern('endcrypt');

/*
	faire un test si le preg match correspon a un autre deja present
*/


$engine = TplEngine\TplEngine::fromFile('template.tpl');
?>

<div style="padding: 10px;margin: 20px;border: 1px solid black;">
	<pre>
		<?= htmlspecialchars($engine->getCompiledTemplate()) ?>
	</pre>
</div>

<div style="padding: 10px;margin: 20px;border: 1px solid black;">
	<?= $engine->render(['myVar' => 'Test']) ?>
</div>
