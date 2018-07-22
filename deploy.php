<?php

namespace Deployer;

require 'recipe/symfony3.php';

// Application settings
set('application', 'cashback');
set('repository', 'git@github.com:IvanAlekseevichPopov/cashback.git');
inventory('config/deployer/hosts.yaml');

// Basic settings
set('default_stage', 'dev');
set('ssh_multiplexing', false);
set('allow_anonymous_stats', false);
set('git_tty', true);
set('docker-compose', 'docker-compose -f docker-compose-prod.yml');
set('keep_releases', 2);

// Shared and writable files/dirs between deploys
add('shared_files', ['.env']);
set('clear_paths', []);
//set('shared_dirs', ['var/log', 'var/sessions']);
set('writable_dirs', ['var']);

task('deploy:copy', function () {
    $sharedPath = "{{deploy_path}}/shared";

    foreach (get('shared_files') as $file) {
        $dirname = dirname(parse($file));

        // Create dir of shared file
        run("mkdir -p $sharedPath/" . $dirname);

        // Check if shared file does not exist in shared.
        // and file exist in release
        if (!test("[ -f $sharedPath/$file ]") && test("[ -f {{release_path}}/$file ]")) {
            // Copy file in shared dir if not present
            run("cp -rv {{release_path}}/$file $sharedPath/$file");
        }

        // Remove from source.
        run("if [ -f $(echo {{release_path}}/$file) ]; then rm -rf {{release_path}}/$file; fi");

        // Ensure dir is available in release
        run("if [ ! -d $(echo {{release_path}}/$dirname) ]; then mkdir -p {{release_path}}/$dirname;fi");

        // Touch shared
        run("touch $sharedPath/$file");

        // Symlink shared dir to release dir
        run("cp $sharedPath/$file {{release_path}}/$file");
    }
});

task('deploy:build', function () {
    run('cd {{release_path}}; {{docker-compose}} build');
});

task('deploy:up:php', function () {
    run('cd {{release_path}}; {{docker-compose}} up -d php');
    run('sleep 2');
});

task('deploy:vendors', function () {
    run('cd {{release_path}}; {{docker-compose}} exec -T php composer install -n');
});

task('deploy:up:db', function () {
    if(has('previous_release')) {
        run('cd {{previous_release}}; {{docker-compose}} stop db');
    }
    run('cd {{release_path}}; {{docker-compose}} up -d db');
    run('sleep 3');
});

task('database:migrate', function () {
    run('cd {{release_path}}; {{docker-compose}} exec -T php bin/console d:m:m -n');
});

task('deploy:cache:clear', function () {
    run('cd {{release_path}}; {{docker-compose}} exec -T php bin/console cache:clear');
});

task('deploy:cache:warmup', function () {
    run('cd {{release_path}}; {{docker-compose}} exec -T php bin/console cache:warmup');
});

task('deploy:down:previous', function () {
    if(has('previous_release')) {
        run('cd {{previous_release}}; {{docker-compose}} down');
    }
});

task('deploy:up:all', function () {
    run('cd {{release_path}}; {{docker-compose}} up -d');
});

task('deploy:failed', function () {
    run('cd {{release_path}}; {{docker-compose}} down || true');
    if(has('previous_release')) {
        run('cd {{previous_release}}; {{docker-compose}} start');
    }
})->setPrivate();

// Additional pre and post deploy jobs
before('deploy:symlink', 'database:migrate');
after('deploy:failed', 'deploy:unlock');

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
//    'deploy:down:current',
    'deploy:release',
    'deploy:update_code',
    'deploy:clear_paths',
    'deploy:create_cache_dir',
    'deploy:copy',
//    'deploy:shared',
//    'deploy:assets',
    'deploy:build',
    'deploy:up:php',
    'deploy:vendors',
//    'deploy:assets:install',
//    'deploy:assetic:dump',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:writable',
    'deploy:up:db', //Вот тут простой отсюда и ->
    'deploy:symlink',
    'deploy:down:previous',
    'deploy:up:all', //До cюда
    'deploy:unlock',
    'cleanup',
])->desc('Deploy your project');
