<?php

namespace Hzmwelec\CPQ\Console;

use Exception;
use Hzmwelec\CPQ\Services\VersionService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class CreateVersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cpq:create-version {version_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create CPQ version';

    /**
     * @var \Hzmwelec\CPQ\Services\VersionService
     */
    protected $versionService;

    /**
     * @param \Hzmwelec\CPQ\Services\VersionService $versionService
     */
    public function __construct(VersionService $versionService)
    {
        parent::__construct();

        $this->versionService = $versionService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $version = $this->versionService->createVersion([
                'name' => $this->argument('version_name'),
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();

            return $this->error(reset($errors)[0]);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        $this->info('Version Id: ' . $version->id);
        $this->info('Version Name: ' . $version->name);
        $this->info('Version UUID: ' . $version->uuid);
    }
}
