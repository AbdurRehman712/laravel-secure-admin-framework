<?php

return array (
  'providers' => 
  array (
    0 => 'Modules\\Core\\app\\Providers\\CoreServiceProvider',
    1 => 'Modules\\ModuleBuilder\\app\\Providers\\ModuleBuilderServiceProvider',
    2 => 'Modules\\PublicUser\\app\\Providers\\PublicUserServiceProvider',
    3 => 'Modules\\ShopModule\\app\\Providers\\ShopModuleServiceProvider',
  ),
  'eager' => 
  array (
    0 => 'Modules\\Core\\app\\Providers\\CoreServiceProvider',
    1 => 'Modules\\ModuleBuilder\\app\\Providers\\ModuleBuilderServiceProvider',
    2 => 'Modules\\PublicUser\\app\\Providers\\PublicUserServiceProvider',
    3 => 'Modules\\ShopModule\\app\\Providers\\ShopModuleServiceProvider',
  ),
  'deferred' => 
  array (
  ),
  'ShopModule' => 
  array (
    'name' => 'ShopModule',
    'alias' => 'shopmodule',
    'description' => 'A comprehensive e-commerce shop system with products, categories, and relationships',
    'enabled' => true,
    'providers' => 
    array (
      0 => 'Modules\\ShopModule\\Providers\\ShopModuleServiceProvider',
    ),
    'path' => 'Modules/ShopModule',
  ),
);
