<?php

use Illuminate\Testing\Fluent\AssertableJson;

it('lists all lending institutions with minimal fields', function () {
    $response = $this->getJson('/api/v1/lending-institutions');

    $response->assertOk()
        ->assertJsonIsArray()
        ->assertJson(fn (AssertableJson $json) =>
        $json->each(fn ($item) =>
        $item
            ->hasAll(['key', 'name', 'alias', 'type'])
            ->whereType('key', 'string')
            ->whereType('name', 'string')
            ->whereType('alias', 'string')
            ->whereType('type', 'string')
        )
        );
});

it('shows full information for a valid lending institution', function () {
    $key = 'rcbc';
    $response = $this->getJson("/api/v1/lending-institutions/{$key}");

    $response->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
        $json
            ->where('key', $key)
            ->where('name', 'Rizal Commercial Banking Corporation')
            ->where('alias', 'RCBC')
            ->where('type', 'universal bank')
            ->has('borrowing_age', fn ($age) =>
            $age->whereAll([
                'minimum' => 18,
                'maximum' => 60,
                'offset' => -1,
            ])
            )
            ->where('maximum_term', 20)
            ->where('maximum_paying_age', 65)
            ->where('buffer_margin', 0.15)
            ->where('income_requirement_multiplier', 0.35)
            ->where('interest_rate', 0.0625)
            ->where('percent_down_payment', 0.10)
        );
});

it('returns 404 for an invalid lending institution key', function () {
    $response = $this->getJson('/api/v1/lending-institutions/unknown-bank');

    $response->assertNotFound()
        ->assertJson([ 'message' => "Lending institution 'unknown-bank' not found."]);
});

it('shows full data for all known lending institutions', function (string $key) {
    $this->getJson("/api/v1/lending-institutions/{$key}")
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
        $json->hasAll([
            'key', 'name', 'alias', 'type',
            'borrowing_age', 'maximum_term', 'maximum_paying_age',
            'buffer_margin', 'income_requirement_multiplier',
            'interest_rate', 'percent_down_payment',
        ])
        );
})->with(['hdmf', 'rcbc', 'cbc']);
