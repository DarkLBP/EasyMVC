<?php
use Core\Utils\Html;

Html::beginHead();
    Html::charset();
    Html::title('Index page');
Html::endHead();
Html::beginBody();
    echo '<h2>Welcome to the index</h2>';
Html::endBody();

