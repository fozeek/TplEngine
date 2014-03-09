<?php

require '../vendor/autoload.php';

$engine = TplEngine\TplEngine::fromFile('template.tpl');
?>

<div style="padding: 10px;margin: 20px;border: 1px solid black;">
	<pre>
		<?= htmlspecialchars($engine->getCompiledTemplate()) ?>
	</pre>
</div>

<div style="padding: 10px;margin: 20px;border: 1px solid black;">
	<?= $engine->render() ?>
</div>