<?php
namespace Deployer;

after('dotenv:upload', 'artisan:config:cache');

task('laravel:webpack', ['laravel:webpack:install', 'laravel:webpack:build']);

task('laravel:webpack:install', function () {
    run('cd {{ release_path }}; npm install');
});
task('laravel:webpack:build', function () {
    run('cd {{ release_path }}; npm run production');
});
