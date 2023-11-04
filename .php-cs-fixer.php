<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__])
    ->path('controllers/settings/Templates.php')
    ->path('libraries/contracts')
    ->path('libraries/ServiceProvider.php')
    ->path('libraries/TemplateManager.php')
    ->path('libraries/Template.php')
    ->path('frontend/views/settings/templates')
    ->path('frontend/html/settings/templates')
    ->path('frontend/libraries/views')
    ->path('controllers/settings/Labels.php')
    ->path('libraries/LabelManager.php')
    ->path('libraries/Label.php')
    ->path('logs/labels')
    ;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
;