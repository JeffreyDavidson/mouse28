<?php

use App\Enums\NavigationGroup;
use App\Filament\Resources\Categories\CategoryResource;

test('category resource is listed with the content resources', function (): void {
    expect(CategoryResource::getNavigationGroup())->toBe(NavigationGroup::Content);
});
