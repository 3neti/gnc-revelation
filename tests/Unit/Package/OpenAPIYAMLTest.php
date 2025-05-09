<?php

use Illuminate\Support\Facades\Route;

it('serves the OpenAPI spec YAML file', function () {
    expect(Route::has('openapi.yaml'))->toBeTrue();

    $response = $this->get(route('openapi.yaml'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/yaml; charset=UTF-8')
        ->assertSee('openapi: 3.1.0')
    ;
});

