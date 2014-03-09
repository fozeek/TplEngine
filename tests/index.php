<?php

require '../vendor/autoload.php';

$engine = new TplEngine\TplEngine('template.tpl');
?>

<div style="padding: 10px;margin: 20px;border: 1px solid black;">
	<pre>
		<?= htmlspecialchars($engine->getCompiledFile()) ?>
	</pre>
</div>

<div style="padding: 10px;margin: 20px;border: 1px solid black;">
	<?= $engine->render() ?>
</div>