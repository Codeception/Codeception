<?php

// disable Yii error handling logic
defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER',false);
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER',false);

Yii::import('codeceptionsrc.plugins.frameworks.yii.test.CTestCase');
Yii::import('codeceptionsrc.plugins.frameworks.yii.web.CodeceptionHttpRequest');
Yii::import('system.test.CDbTestCase');
Yii::import('system.test.CWebTestCase');
