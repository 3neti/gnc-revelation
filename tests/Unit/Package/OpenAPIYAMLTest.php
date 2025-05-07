<?php

use Illuminate\Support\Facades\Route;

it('serves the OpenAPI spec YAML file', function () {
    expect(Route::has('api.v1.mortgage.openapi.yaml'))->toBeTrue();

    $response = $this->get(route('api.v1.mortgage.openapi.yaml'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/yaml; charset=UTF-8')
        ->assertSee('openapi: 3.0.0')
    ;
});
