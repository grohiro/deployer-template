<?php
namespace Deployer;

// deployer dotenv:upload stage
// deployer dotenv:dump
// deployer dotenv:export
// deployer dotenv stage

set('dotenv_path', '{{deploy_path}}/shared/.env');
set('dotenv_local_path', '.');

task('dotenv', ['dotenv:upload']);

// Uplaod a .env.{stage} file.
task('dotenv:upload', function () {
  $stage = get('stage');
  $src = get('dotenv_local_path')."/.env.${stage}";
  if (!file_exists($src)) {
    throw new \Exception("File not found: $src. Make sure `{{dotenv_local_path}}` variable.");
  }

  $dest = '{{dotenv_path}}';
  upload($src, $dest);
});

task('dotenv:export', function () {
    run('export $(cat {{dotenv_path}} | xargs)');
});

task('dotenv:dump', function () {
    // Read .env
    writeln(run('cat {{dotenv_path}}'));
});

