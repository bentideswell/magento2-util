<?php
/**
 * FishPig_Db
 * https://fishpig.com/
 */
use \Magento\Framework\Component\ComponentRegistrar;

// This is not a cached request so lets just load the module
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'FishPig_Util',
    __DIR__
);
