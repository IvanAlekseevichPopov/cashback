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

// Shared and writable files/dirs between deploys
add('shared_files', ['config/packages/prod/parameters.yaml', '.env']);
set('clear_paths', []);
set('shared_dirs', ['var/log', 'var/sessions']);
set('writable_dirs', ['var/cache', 'var/log', 'var/sessions']);

task('deploy:assets:install', function () {
    run('{{bin/php}} {{bin/console}} assets:install {{console_options}} {{release_path}}/public');
})->desc('Install bundle assets');

task('deploy:build', function () {
    run('cd {{release_path}}; docker-compose -f docker-compose-prod.yml build');
});

task('deploy:vendors', function () {
    run('cd {{release_path}}; docker-compose -f docker-compose-prod.yml exec php composer install -n');
});


// Additional pre and post deploy jobs
before('deploy:symlink', 'database:migrate');
after('deploy:failed', 'deploy:unlock');

//desc('Restart PHP-FPM service');
//task('php-fpm:restart', function () {
//    run('service php7.1-fpm restart');
//});
//after('deploy:symlink', 'php-fpm:restart');

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:clear_paths',
    'deploy:create_cache_dir',
    'deploy:shared',
    'deploy:assets',
    'deploy:build',
    'deploy:vendors',
//    'deploy:assets:install',
//    'deploy:assetic:dump',
//    'deploy:cache:clear',
//    'deploy:cache:warmup',
//    'deploy:writable',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
])->desc('Deploy your project');
