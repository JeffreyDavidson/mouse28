<?php

declare(strict_types=1);

use Filament\Resources\Resource;
use Illuminate\Console\Command;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

arch('application code does not contain debugging calls')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsedIn('App');

arch('environment values are read through configuration')
    ->expect('env')
    ->not->toBeUsedIn('App');

arch('form requests extend the Laravel form request')
    ->expect('App\Http\Requests')
    ->toExtend(FormRequest::class);

arch('models do not query through the database facade')
    ->expect(DB::class)
    ->not->toBeUsedIn('App\Models');

arch('controllers do not query through the database facade')
    ->expect(DB::class)
    ->not->toBeUsedIn('App\Http\Controllers');

arch('controllers do not send mail directly')
    ->expect(Mail::class)
    ->not->toBeUsedIn('App\Http\Controllers');

arch('actions are invokable classes')
    ->expect('App\Actions')
    ->toBeClasses()
    ->toHaveMethod('__invoke');

$commandClasses = collect(glob(__DIR__.'/../../app/Console/Commands/*.php') ?: [])
    ->map(fn (string $file): string => 'App\\Console\\Commands\\'.basename($file, '.php'))
    ->filter(fn (string $class): bool => class_exists($class))
    ->values()
    ->all();

test('console commands extend the Laravel command base class', function () use ($commandClasses): void {
    foreach ($commandClasses as $commandClass) {
        expect(is_a($commandClass, Command::class, true))->toBeTrue(
            "{$commandClass} must extend ".Command::class.'.',
        );
    }
});

$mailableClasses = collect(glob(__DIR__.'/../../app/Mail/*.php') ?: [])
    ->map(fn (string $file): string => 'App\\Mail\\'.basename($file, '.php'))
    ->filter(fn (string $class): bool => class_exists($class))
    ->values()
    ->all();

test('mail classes extend the Laravel mailable base class', function () use ($mailableClasses): void {
    foreach ($mailableClasses as $mailableClass) {
        expect(is_a($mailableClass, Mailable::class, true))->toBeTrue(
            "{$mailableClass} must extend ".Mailable::class.'.',
        );
    }
});

$viewModelFiles = glob(__DIR__.'/../../app/ViewModels/*.php') ?: [];

test('view model classes use the ViewModel suffix', function () use ($viewModelFiles): void {
    $violations = collect($viewModelFiles)
        ->map(fn (string $file): string => basename($file, '.php'))
        ->reject(fn (string $class): bool => str_ends_with($class, 'ViewModel'))
        ->values()
        ->all();

    expect($violations)->toBeEmpty('View model classes must end with ViewModel.');
});

$resourceRoot = __DIR__.'/../../app/Filament/Resources/';
$resourceClasses = collect(glob($resourceRoot.'*/*Resource.php') ?: [])
    ->map(fn (string $file): string => 'App\\Filament\\Resources\\'.str_replace(
        DIRECTORY_SEPARATOR,
        '\\',
        substr($file, strlen($resourceRoot), -4),
    ))
    ->filter(fn (string $class): bool => class_exists($class))
    ->values()
    ->all();

test('Filament resource classes extend the resource base class', function () use ($resourceClasses): void {
    foreach ($resourceClasses as $resourceClass) {
        expect(is_a($resourceClass, Resource::class, true))->toBeTrue(
            "{$resourceClass} must extend ".Resource::class.'.',
        );
    }
});

$resourceMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

$controllerClasses = collect(glob(__DIR__.'/../../app/Http/Controllers/*.php') ?: [])
    ->map(fn (string $file): string => 'App\\Http\\Controllers\\'.basename($file, '.php'))
    ->filter(fn (string $class): bool => class_exists($class))
    ->values();

foreach ($controllerClasses as $controllerClass) {
    test(class_basename($controllerClass).' is invokable or resourceful', function () use ($controllerClass, $resourceMethods): void {
        $reflection = new ReflectionClass($controllerClass);
        $publicMethods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn (ReflectionMethod $method): bool => $method->class === $controllerClass)
            ->reject(fn (ReflectionMethod $method): bool => $method->isStatic())
            ->reject(fn (ReflectionMethod $method): bool => str_starts_with($method->name, '__') && $method->name !== '__invoke')
            ->map(fn (ReflectionMethod $method): string => $method->name)
            ->values()
            ->all();

        $isInvokable = $publicMethods === ['__invoke'];
        $isResourceful = array_diff($publicMethods, $resourceMethods) === [];

        expect($isInvokable || $isResourceful)->toBeTrue(
            'Expected only __invoke() or resource methods; found: '.implode(', ', $publicMethods),
        );
    });
}
