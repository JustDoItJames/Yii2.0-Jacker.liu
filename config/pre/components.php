<?php
return array_merge(
    require(CONFIG_DIR . DIRECTORY_SEPARATOR . 'database.php'),
    require(CONFIG_DIR . DIRECTORY_SEPARATOR . 'thrift.php')
);