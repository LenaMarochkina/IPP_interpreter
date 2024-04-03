<?php
require_once(__DIR__ . '/vendor/autoload.php');

use Permafrost\PhpCsFixerRules\Finders\BasicProjectFinder;
use Permafrost\PhpCsFixerRules\Rulesets\DefaultRuleset;
use Permafrost\PhpCsFixerRules\SharedConfig;

// optional: chain additional custom Finder options:
$finder = BasicProjectFinder::create(__DIR__);

return SharedConfig::create($finder, new DefaultRuleset());