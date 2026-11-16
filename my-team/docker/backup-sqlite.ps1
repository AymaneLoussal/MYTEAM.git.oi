param(
    [string] $BackupDirectory = (Join-Path $PSScriptRoot '..\backups')
)

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$backupRoot = [System.IO.Path]::GetFullPath($BackupDirectory)
New-Item -ItemType Directory -Path $backupRoot -Force | Out-Null

$container = (docker compose --project-directory $projectRoot ps -q app).Trim()
if (-not $container) {
    throw 'The FTMBALL app container is not running.'
}

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$fileName = "ftmball-$stamp.sqlite"
$containerBackup = "/tmp/$fileName"
$hostBackup = Join-Path $backupRoot $fileName

docker compose --project-directory $projectRoot exec -T --user app app php /app/docker/sqlite-backup.php create $containerBackup
if ($LASTEXITCODE -ne 0) { throw 'SQLite online backup failed.' }

try {
    docker cp "${container}:$containerBackup" $hostBackup
    if ($LASTEXITCODE -ne 0) { throw 'Copying the backup outside the Docker volume failed.' }
} finally {
    docker compose --project-directory $projectRoot exec -T app rm -f $containerBackup
}

$mount = "type=bind,source=$backupRoot,target=/backup,readonly"
docker run --rm --entrypoint sh --mount $mount my-team-app:latest -c "cp '/backup/$fileName' /tmp/restored.sqlite && php /app/docker/sqlite-backup.php verify /tmp/restored.sqlite"
if ($LASTEXITCODE -ne 0) { throw 'Isolated restore verification failed.' }

$hash = (Get-FileHash -Algorithm SHA256 -LiteralPath $hostBackup).Hash
Write-Output "Backup: $hostBackup"
Write-Output "SHA256: $hash"
