<?php

namespace App\Core\Utils;

use Illuminate\Console\Command;

class FunctionUtil extends Command
{
    protected $name;

    public function createMigration(): void
    {
        $tableName = $this->camelToSnake($this->name) . 's';
        $path = database_path('migrations/' . date('Y_m_d_His') . '_create_' . $tableName . '_table.php');

        if (!file_exists($path)) {
            $stubContents = str_replace('{{tableName}}', $tableName, $this->getStubContent('migration'));
            file_put_contents($path, $stubContents);
        } else {
            // $this->info('Migration ' . $tableName . ' already exists.');
        }
    }

    public function createModel($subFolder = ''): array
    {
        $directory = $this->prepareDirectory('', 'Models', $subFolder);
        $tableName = $this->camelToSnake($this->name) . 's';

        $this->ensureDirectoryExists($directory['directory']);

        $this->createMigration();

        if (!file_exists($directory['path'])) {
            $stubContents = str_replace(
                [
                    '{{subPath}}',
                    '{{name}}',
                    '{{tableName}}'
                ],
                [
                    $directory['subPath'],
                    $directory['functionName'],
                    $tableName,
                ],
                $this->getStubContent('model')
            );

            file_put_contents($directory['path'], $stubContents);
        } else {
            // $this->info('Model ' . $functionName . ' already exists.');
        }

        return [
            'name' => $directory['functionName'],
            'usePath' => $directory['usePath']
        ];
    }

    public function createService($subFolder = ''): array
    {
        $directory = $this->prepareDirectory('Service', 'Services', $subFolder);

        $this->ensureDirectoryExists($directory['directory']);

        $modelData = $this->createModel();
        if (!file_exists($directory['path'])) {

            $stubContents = str_replace(
                [
                    '{{subPath}}',
                    '{{name}}',
                    '{{modelPath}}',
                    '{{modelName}}'
                ],
                [
                    $directory['subPath'],
                    $directory['functionName'],
                    $modelData['usePath'],
                    $modelData['name']
                ],
                $this->getStubContent('service')
            );

            file_put_contents($directory['path'], $stubContents);
        } else {
            // $this->info('Service ' . $functionName . ' already exists.');
        }

        return [
            'name' => $directory['functionName'],
            'usePath' => $directory['usePath']
        ];
    }

    public function createRequest($api = false, $subFolder = ''): array
    {
        $directory = $this->prepareDirectory('Request', 'Http/Requests', $subFolder);

        $this->ensureDirectoryExists($directory['directory']);

        if (!file_exists($directory['path'])) {
            $stub = $this->getStubContent('request');

            $stub = str_replace('{{subPath}}', $directory['subPath'], $stub);
            $stub = str_replace('{{name}}', $directory['functionName'], $stub);
            if ($api) {
                $throwValidation = <<<EOT
                                    protected function failedValidation(Validator \$validator)
                                    {
                                        \$errors = \$validator->errors()->all();
                                        throw new HttpResponseException(jsonResponse(1, \$errors));
                                    }
                                    EOT;
                $stub = str_replace('{{api}}', $throwValidation, $stub);
            } else {
                $stub = str_replace('{{api}}', '', $stub);
            }
            file_put_contents($directory['path'], $stub);
        } else {
            // $this->info('Request ' . $functionName . ' already exists.');
        }

        return [
            'name' => $directory['functionName'],
            'usePath' => $directory['usePath']
        ];
    }

    public function createController($api = false, $subFolder = ''): void
    {
        $directory = $this->prepareDirectory('Controller', 'Http/Controllers', $subFolder);

        $this->ensureDirectoryExists($directory['directory']);

        $serviceData = $this->createService();
        $requestData = $this->createRequest($api);

        if (!file_exists($directory['path'])) {
            $stubContents = str_replace(
                [
                    '{{subPath}}',
                    '{{name}}',
                    '{{serviceName}}',
                    '{{varServiceName}}',
                    '{{servicePath}}',
                    '{{requestName}}',
                    '{{requestPath}}'
                ],
                [
                    $directory['subPath'],
                    $directory['functionName'],
                    $serviceData['name'],
                    lcfirst($serviceData['name']),
                    $serviceData['usePath'],
                    $requestData['name'],
                    $requestData['usePath']
                ],
                $this->getStubContent('controller')
            );
            file_put_contents($directory['path'], $stubContents);
        } else {
            // $this->info('Controller ' . $functionName . ' already exists.');
        }
    }

    public function prepareDirectory($function = '', $directoryPath, $subFolder = ''): array
    {
        $functionName = $this->makeFunctionName($function);
        $subFolderPath = $directoryPath . '/' . ($subFolder ? $subFolder . '/' : '') . $this->name;
        $directory = base_path('app') . '/' . $subFolderPath;
        $path = $directory . '/' . $functionName . '.php';

        return [
            'functionName' => $functionName,
            'subPath' => $this->makeUsePath($subFolderPath),
            'usePath' => $this->makeUsePath($subFolderPath . '/' . $functionName),
            'directory' => $directory,
            'path' => $path
        ];
    }

    function camelToSnake($camelCase)
    {
        $snakeCase = '';
        $i = 0;
        $len = strlen($camelCase);
        while ($i < $len) {
            if (ctype_upper($camelCase[$i])) {
                if ($i !== 0) {
                    $snakeCase .= '_';
                }
                $snakeCase .= strtolower($camelCase[$i]);
            } else {
                $snakeCase .= $camelCase[$i];
            }
            $i++;
        }
        return $snakeCase;
    }

    public function declareName(string $name): void
    {
        $this->name = $name;
    }

    public function makeFunctionName(string $functionName = ''): string
    {
        return $this->name . $functionName;
    }

    public function makeUsePath($path)
    {
        return 'App\\' . strtr($path, '/', '\\');
    }

    public function getStubContent(string $stubType): string
    {
        return file_get_contents(base_path('app/Core/stubs/' . $stubType . '.stub'));
    }

    public function ensureDirectoryExists(string $path): void
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }
}