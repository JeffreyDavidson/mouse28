<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Http\FormRequest;
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

$resourceMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

$controllerClasses = collect(glob(__DIR__.'/../../app/Http/Controllers/*.php') ?: [])
    ->map(fn (string $file): string => 'App\\Http\\Controllers\\'.basename($file, '.php'))
    ->reject(fn (string $class): bool => $class === Controller::class)
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
