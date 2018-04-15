<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'recipe/slack.php';
require __DIR__.'/deployer/dotenv.php';
require __DIR__.'/deployer/laravel.php';
require __DIR__.'/deployer/backlog.php';

set('backlog.url.ticket', 'https://ACCOUNT.backlog.jp/view/%s');
set('backlog.url.commit', 'https://ACCOUNT.backlog.jp/git/PROJECT/REPO/commit/%s');
set('backlog.regex.ticket_id', '/(PROJECT-\d+)/');

set('slack_webhook', 'https://hooks.slack.com/services/KEY1/KEY2/KEY3');

// Project name
set('application', 'APP_NAME');

// Project repository
set('repository', 'git@grohiro.github.com:/deployer-template.git');

set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader');

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', ['vendor']);
set('http_user', 'apache');
set('writable_use_sudo', true);

// Writable dirs by web server 
set('allow_anonymous_stats', false);
set('tty', true);
set('deploy_path', '/var/www/{{application}}');
set('user', 'deploy');
// Hosts

host('127.0.0.1')
    ->stage('dev')
    ->set('branch', 'master')
    ->addSshOption('StrictHostKeyChecking', 'no')
    ->forwardAgent(true);

// Tasks
task('logrotate:link', function () {
    run('sudo ln -fs {{current_path}}/etc/logrotate.conf /etc/logrotate.d/{{application}}');
});

// Create a symlink to rotate laravel logs.
after('deploy:symlink', 'logrotate:link');

// Migrate database before symlink new release.
before('deploy:symlink', 'artisan:migrate');

// Build assets
before('artisan:optimize', 'laravel:webpack');

