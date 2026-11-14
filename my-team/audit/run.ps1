param([string]$Php = 'php')
$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)
$env:APP_ENV = 'testing'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
$env:DB_URL = ''
$env:SESSION_DRIVER = 'array'
$env:CACHE_STORE = 'array'
$env:MAIL_MAILER = 'array'
$env:QUEUE_CONNECTION = 'sync'
$env:BCRYPT_ROUNDS = '4'
& $Php vendor/phpunit/phpunit/phpunit --no-configuration --bootstrap vendor/autoload.php audit/ProjectAuditTest.php --colors=never --log-junit audit/probe-results.xml *> audit/probe-output.txt
$probeExit = $LASTEXITCODE
Get-Content audit/probe-output.txt -Tail 3
exit $probeExit
