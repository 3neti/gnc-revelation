<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use LBHurtado\Mortgage\Models\Property;

uses(RefreshDatabase::class);

beforeEach(function () {
    Property::factory()->count(5)->sequence(
        ['code' => 'PROP0001', 'status' => 'available'],
        ['code' => 'PROP0002', 'status' => 'sold'],
        ['code' => 'PROP0003', 'status' => 'available'],
        ['code' => 'PROP0004', 'status' => 'reserved'],
        ['code' => 'PROP0005', 'status' => 'available'],
    )->create([
        'total_contract_price' => 1500000,
        'appraisal_value' => 1400000,
        'percent_loanable_value' => 1.0,
        'percent_miscellaneous_fees' => 0.085,
        'processing_fee' => 10000,
        'lending_institution' => 'hdmf'
    ]);
});

it('returns the correct response structure from properties endpoint', function () {
    $this->getJson(route('api.v1.properties'))
        ->assertOk()
        ->assertJsonStructure([
            '*' => [
                'code',
                'name',
                'type', //new
                'cluster', //new
                'status',
                'project_code', //new
                'total_contract_price', //updated
                'appraisal_value',
                'percent_loanable_value',
                'percent_miscellaneous_fees',
                'processing_fee',
                'required_buffer_margin',
                'percent_down_payment',
            ],
        ]);
});

it('returns seeded property data from properties endpoint', function () {
    $property = Property::where('code', 'PROP0002')->first();

    $this->getJson(route('api.v1.properties', ['available_only' => false]))
        ->assertOk()
        ->assertJsonFragment([
            'code' => $property->code,
            'name' => $property->name,
            'status' => $property->status,
            'total_contract_price' => 1500000.0,
            'appraisal_value' => 1400000.0,
            'percent_loanable_value' => 1.0,
            'percent_miscellaneous_fees' => 0.085,
            'processing_fee' => 10000.0,
        ]);
});

it('returns only available properties', function () {
    $this->getJson(route('api.v1.properties'))
        ->assertOk()
        ->assertJsonCount(3);
});

it('returns all properties', function () {
    $this->getJson(route('api.v1.properties', ['available_only' => false]))
        ->assertOk()
        ->assertJsonCount(5);
});

it('filters properties by code', function () {
    $this->getJson(route('api.v1.properties', ['code' => 'PROP0003']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['code' => 'PROP0003']);
});

it('filters only available properties', function () {
    $this->getJson(route('api.v1.properties', ['available_only' => true]))
        ->assertOk()
        ->assertJsonCount(3)
        ->assertJson(fn($json) =>
            $json->each(fn($property) =>
                $property->where('status', 'available')->etc()
            )
        );
});

it('filters by minimum price', function () {
    // Arrange
    $property = Property::first();
    $property->update(['total_contract_price' => 1_000_000]);
    $property->save();

    $expectedCodes = Property::query()
        ->withMeta('total_contract_price', '>=', 1_400_000 * 100)
        ->pluck('code')
        ->all();

    // Act
    $response = $this->getJson(route('api.v1.properties', ['min_price' => 1_400_000, 'available_only' => false]));

    // Assert
    $response->assertOk()->assertJsonCount(4);

    $returnedCodes = collect($response->json())->pluck('code')->all();
    expect($returnedCodes)->toEqualCanonicalizing($expectedCodes);
});

it('filters by max price', function () {
    $this->getJson(route('api.v1.properties', ['max_price' => 1_500_000]))
        ->assertOk()
        ->assertJsonCount(3);
});

it('filters properties by lending institutions', function () {
    // Add additional properties with a different lending institution
    Property::factory()->count(3)->sequence(
        ['code' => 'PROP0006', 'status' => 'available'],
        ['code' => 'PROP0007', 'status' => 'available'],
        ['code' => 'PROP0008', 'status' => 'sold']
    )->create([
        'total_contract_price' => 2000000,
        'appraisal_value' => 1800000,
        'percent_loanable_value' => 0.85,
        'percent_miscellaneous_fees' => 0.1,
        'processing_fee' => 12000,
        'lending_institution' => 'rcbc' // Different institution
    ]);

    // Perform a GET request with the lending_institutions filter
    $response = $this->getJson(route('api.v1.properties', ['lending_institution' => 'hdmf']));

    // Assert the response contains only properties with 'hdmf' as the lending institution
    $response->assertOk()
        ->assertJsonCount(3) // Since 5 out of 8 have lending_institution = 'hdmf'
        ->assertJsonFragment(['code' => 'PROP0001'])
        ->assertJsonFragment(['code' => 'PROP0003'])
        ->assertJsonFragment(['code' => 'PROP0005'])
    ;

    // Perform another GET request with 'rcbc' as the filter
    $response = $this->getJson(route('api.v1.properties', ['lending_institution' => 'rcbc', 'available_only' => true]));

    // Assert the response contains the correct properties with 'rcbc'
    $response->assertOk()
        ->assertJsonCount(2) // 3 properties have lending_institution = 'rcbc'
        ->assertJsonFragment(['code' => 'PROP0006'])
        ->assertJsonFragment(['code' => 'PROP0007'])
    ;
});
