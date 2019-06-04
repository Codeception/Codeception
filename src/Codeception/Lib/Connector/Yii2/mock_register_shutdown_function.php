<?php

namespace yii\log;

function register_shutdown_function()
{
    codecept_debug('Register shutdown function called and ignored');
}
