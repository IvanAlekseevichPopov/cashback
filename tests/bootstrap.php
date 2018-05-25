<?php

require __DIR__.'/../vendor/autoload.php';

$runCommand = function ($command) {
    printf('Running %s %s', $command, PHP_EOL);
    passthru(
        sprintf('APP_ENV=test php %s/../%s', __DIR__, $command),
        $code
    );

    if (0 !== $code) {
        throw new \Exception('Database preparing failed');
    }
};

(new \Symfony\Component\Dotenv\Dotenv())->load(__DIR__.'/../.env');

//if (isset($_ENV['BOOTSTRAP_DB'])) {
    print_r($_ENV);
    echo 'Preparing database...'.PHP_EOL;
    $runCommand('bin/console doctrine:schema:drop --full-database --force');
    $runCommand('bin/console doctrine:migrations:migrate --no-interaction');
    $runCommand('bin/console doctrine:schema:validate');
    $runCommand('bin/console doctrine:fixtures:load --no-interaction --append');
//}
