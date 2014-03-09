<?php

require 'TplEngine.php';

$engine = new TplEngine('template.tpl');
?>

<div style="padding: 10px;margin: 20px;border: 1px solid black;">
	<pre>
		<?= htmlspecialchars($engine->render()) ?>
	</pre>
</div>

<div style="padding: 10px;margin: 20px;border: 1px solid black;">
	<?= $engine->render() ?>
</div>