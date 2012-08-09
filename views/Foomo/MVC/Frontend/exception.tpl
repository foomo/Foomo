<?php

?><h2>Error</h2>
<div class="errorMessage">
<p>Looks like something went really wrong</p>
<pre><?= $view->escape($exception->getMessage()) ?></pre>
</div>
