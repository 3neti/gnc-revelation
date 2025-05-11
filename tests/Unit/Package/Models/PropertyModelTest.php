<?php

use LBHurtado\Mortgage\Classes\Property as DomainProperty;
use LBHurtado\Mortgage\Enums\Property\DevelopmentForm;
use LBHurtado\Mortgage\Enums\Property\DevelopmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use Brick\Math\Exception\NumberFormatException;
use LBHurtado\Mortgage\Enums\Property\HousingType;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\ValueObjects\Percent;
use LBHurtado\Mortgage\Models\Property;
use Whitecube\Price\Price;

uses(RefreshDatabase::class);

test('property has factory', function () {
    $property = Property::factory()->create();

    expect($property)->toBeInstanceOf(Property::class)
        ->and($property->id)->toBeInt()
        ->and($property->code)->toBeString()
        ->and($property->type)->toBeString()
        ->and($property->cluster)->toBeString()
        ->and($property->status)->toBeString();
});

test('property has additional attributes', function () {
    $property = Property::factory()->create();

    $domain_attributes = [
//        'total_contract_price' => $total_contract_price = fake()->numberBetween(750_000, 4_000_000)
          'total_contract_price' => $total_contract_price = 100.0
    ];

    // Update the Property model with the additional attribute
    $property->update($domain_attributes);

    // Check if the attribute is correctly set and retrieved after using MoneyFactory and casting
    expect($property->total_contract_price)
        ->toBeInstanceOf(Price::class) // Assert the attribute is a Price object
        ->and($property->total_contract_price->inclusive()->getAmount()->toFloat())->toBeCloseTo($total_contract_price) // Assert the amount matches
    ;
});

/*** Property::TOTAL_CONTRACT_PRICE ****/
test('it can set total_contract_price with a float value', function () {
    $property = Property::factory()->create();

    $totalContractPrice = 2500000.75; // A float value

    // Set the total_contract_price
    $property->update([Property::TOTAL_CONTRACT_PRICE => $totalContractPrice]);
    $property->save();

    // Assert that total_contract_price is stored and converted to a Price object
    expect($property->total_contract_price)
        ->toBeInstanceOf(Price::class)
        ->and($property->total_contract_price->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($totalContractPrice);
});

test('it can set total_contract_price with an integer value', function () {
    $property = Property::factory()->create();

    $totalContractPrice = 3000000; // An integer value

    // Set the total_contract_price
    $property->update([Property::TOTAL_CONTRACT_PRICE => $totalContractPrice]);
    $property->save();

    // Assert that total_contract_price is stored as a Price object and integer value matches
    expect($property->total_contract_price)
        ->toBeInstanceOf(Price::class)
        ->and($property->total_contract_price->inclusive()->getAmount()->toInt())
        ->toBe($totalContractPrice);
});

test('it can set total_contract_price with a Price object', function () {
    $property = Property::factory()->create();

    // Create a Price instance
    $totalContractPrice = MoneyFactory::price(4_000_000);

    // Set the total_contract_price using the Price object
    $property->update([Property::TOTAL_CONTRACT_PRICE => $totalContractPrice]);
    $property->save();

    // Assert that total_contract_price retains the Price object and matches the amount
    expect($property->total_contract_price)
        ->toBeInstanceOf(Price::class)
        ->and($property->total_contract_price->inclusive()->getAmount()->toInt())
        ->toBe($totalContractPrice->getAmount()->toInt())
    ;
});

test('it returns null when total_contract_price is not set', function () {
    $property = Property::factory()->create();

    // Assert that the total_contract_price getter returns null if not set
    expect($property->total_contract_price)->toBeNull();
});

test('it can set total_contract_price with a string value', function () {
    $property = Property::factory()->create();

    $totalContractPrice = "3500000.50"; // A numeric string

    // Set the total_contract_price using a string
    $property->update([Property::TOTAL_CONTRACT_PRICE => $totalContractPrice]);
    $property->save();

    // Assert that total_contract_price is stored as a Price object and matches the float equivalent
    expect($property->total_contract_price)
        ->toBeInstanceOf(Price::class)
        ->and($property->total_contract_price->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo((float) $totalContractPrice);
});

test('it throws an exception when total_contract_price is set with an invalid value', function () {
    $property = Property::factory()->create();

    // Attempt to set an invalid value and expect an exception
    $property->update([Property::TOTAL_CONTRACT_PRICE => 'invalid_value']);
})->throws(NumberFormatException::class);

test('it handles large total_contract_price values correctly', function () {
    $property = Property::factory()->create();

    $totalContractPrice = 9999999999.99; // A large float value

    // Set the total_contract_price with a large value
    $property->update([Property::TOTAL_CONTRACT_PRICE => $totalContractPrice]);
    $property->save();

    // Assert total_contract_price is handled correctly
    expect($property->total_contract_price)
        ->toBeInstanceOf(Price::class)
        ->and($property->total_contract_price->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($totalContractPrice);
});
/*** Property::TOTAL_CONTRACT_PRICE ****/

/*** Property::DEVELOPMENT_TYPE ****/
test('it can set and get development_type attribute', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example development type enum instance
    $developmentType = DevelopmentType::BP_220;

    // Use the setter to set the value
    $property->update([Property::DEVELOPMENT_TYPE => $developmentType->value]);
    $property->save();

    // Assert that the value retrieved from the getter matches what we set
    expect($property->development_type)
        ->toBeInstanceOf(DevelopmentType::class) // Ensures it returns an enum
        ->and($property->development_type->value)->toBe($developmentType->value); // Ensures the value matches the set value
});

test('it can set and get development_type as an enum instance', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example DevelopmentType enum instance
    $developmentType = DevelopmentType::BP_220;

    // Use the setter to set the development_type
    $property->update([Property::DEVELOPMENT_TYPE => $developmentType->value]);
    $property->save();

    // Assert that the getter returns the same enum instance
    expect($property->development_type)
        ->toBeInstanceOf(DevelopmentType::class) // Ensures it's an enum
        ->and($property->development_type->value)->toBe($developmentType->value); // Ensures the value matches
});

test('it can set and get development_type as a string value', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example development_type value as a string
    $developmentTypeValue = 'bp_957'; // Corresponds to DevelopmentType::BP_957

    // Use the setter to set the development_type as a string
    $property->update([Property::DEVELOPMENT_TYPE => $developmentTypeValue]);
    $property->save();

    // Assert that the getter converts it back to an enum
    expect($property->development_type)
        ->toBeInstanceOf(DevelopmentType::class) // Ensures it's an enum
        ->and($property->development_type->value)->toBe($developmentTypeValue); // Ensures the value matches
});

test('it returns null from getDevelopmentType if no value is set', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Assert that the getter returns null when no value is set in the meta field
    expect($property->development_type)->toBeNull();
});

test('it gracefully handles invalid development_type values in meta', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Simulate an invalid value in the meta field
    $property->update([Property::DEVELOPMENT_TYPE =>  'invalid_value']);
    $property->save();

    // Assert that the getter returns null for invalid values
    expect($property->development_type)->toBeNull();
});
/*** Property::DEVELOPMENT_TYPE ****/

/*** Property::DEVELOPMENT_FORM ****/
test('it can set and get development_form as an enum instance', function () {
    // Create a Property instance
    $property = Property::factory()->create();;

    // Example DevelopmentForm enum instance
    $developmentForm = DevelopmentForm::HORIZONTAL;

    // Use the setter to set the development_form
    $property->update([Property::DEVELOPMENT_FORM => $developmentForm]);
    $property->save();

    // Assert that the getter returns the same enum instance
    expect($property->development_form)
        ->toBeInstanceOf(DevelopmentForm::class) // Ensures it is a valid DevelopmentForm enum
        ->and($property->development_form->value)->toBe($developmentForm->value); // Ensures the value matches the set value
});

test('it can set and get development_form as a string value', function () {
    // Create a Property instance
    $property = Property::factory()->create();;

    // Example development_form value as a string
    $developmentFormValue = 'vertical'; // Corresponds to DevelopmentForm::VERTICAL

    // Use the setter to set the development_form as a string
    $property->update([Property::DEVELOPMENT_FORM => $developmentFormValue]);
    $property->save();

    // Assert that the getter converts it back to a DevelopmentForm enum
    expect($property->development_form)
        ->toBeInstanceOf(DevelopmentForm::class) // Ensures it is a valid DevelopmentForm enum
        ->and($property->development_form->value)->toBe($developmentFormValue); // Ensures the value matches
});

test('it returns null from getDevelopmentForm if no value is set', function () {
    // Create a Property instance
    $property = Property::factory()->create();;

    // Assert that the getter returns null when no value is set
    expect($property->development_form)->toBeNull();
});

test('it gracefully handles invalid development_form values in meta', function () {
    // Create a Property instance
    $property = Property::factory()->create();;

    // Simulate an invalid value in the meta field
    $property->getAttribute('meta')->set(Property::DEVELOPMENT_FORM, 'invalid_value');
})->throws(TypeError::class)->skip();
/*** Property::DEVELOPMENT_FORM ****/

/*** Property::HOUSING_TYPE ***/

test('it can set and get housing_type attribute', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example housing type enum instance
    $housingType = HousingType::CONDOMINIUM;

    // Use the setter to set the value
    $property->update([Property::HOUSING_TYPE => $housingType->value]);
    $property->save();

    // Assert that the value retrieved from the getter matches what we set
    expect($property->housing_type)
        ->toBeInstanceOf(HousingType::class) // Ensures it returns an enum
        ->and($property->housing_type->value)->toBe($housingType->value); // Ensures the value matches the set value
});

test('it can set and get housing_type as an enum instance', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example housing type enum instance
    $housingType = HousingType::ROW_HOUSE;

    // Use the setter to set the housing_type
    $property->update([Property::HOUSING_TYPE => $housingType->value]);
    $property->save();

    // Assert that the getter returns the same enum instance
    expect($property->housing_type)
        ->toBeInstanceOf(HousingType::class) // Ensures it's an enum
        ->and($property->housing_type->value)->toBe($housingType->value); // Ensures the value matches
});

test('it can set and get housing_type as a string value', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example housing_type value as a string
    $housingTypeValue = 'townhouse'; // Corresponds to HousingType::TOWNHOUSE

    // Use the setter to set the housing_type as a string
    $property->update([Property::HOUSING_TYPE => $housingTypeValue]);
    $property->save();

    // Assert that the getter converts it back to an enum
    expect($property->housing_type)
        ->toBeInstanceOf(HousingType::class) // Ensures it's an enum
        ->and($property->housing_type->value)->toBe($housingTypeValue); // Ensures the value matches
});

test('it returns null from getHousingType if no value is set', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Assert that the getter returns null when no value is set in the meta field
    expect($property->housing_type)->toBeNull();
});

test('it gracefully handles invalid housing_type values in meta', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Simulate an invalid value in the meta field
    $property->update([Property::HOUSING_TYPE =>  'invalid_value']);
    $property->save();

    // Assert that the getter returns null for invalid values
    expect($property->housing_type)->toBeNull();
})->skip();

/*** Property::HOUSING_TYPE ***/

/*** Property::REQUIRED_BUFFER_MARGIN ****/
test('it can set and get required_buffer_margin as a Percent instance', function () {
    // Create a Property instance
    $property = Property::factory()->create();;

    // Example Percent instance
    $requiredBufferMargin = Percent::ofPercent(15);

    // Use the setter to set the required_buffer_margin
    $property->update([Property::REQUIRED_BUFFER_MARGIN => $requiredBufferMargin]);
    $property->save();

    // Assert that the getter returns the same Percent instance
    expect($property->required_buffer_margin)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->required_buffer_margin->value())->toBe($requiredBufferMargin->value()); // Ensures the percent value matches
});

test('it can set and get required_buffer_margin as an integer percentage', function () {
    // Create a Property instance
    $property = Property::factory()->create();;

    // Example buffer margin as an integer percentage
    $requiredBufferMarginValue = 15; // 15%

    // Use the setter to set the required_buffer_margin as an integer
    $property->update([Property::REQUIRED_BUFFER_MARGIN => $requiredBufferMarginValue]);
    $property->save();

    // Assert that the getter converts it to a Percent instance
    expect($property->required_buffer_margin)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->required_buffer_margin->asPercent())->toBe((float) $requiredBufferMarginValue); // Ensures the percent value matches
});

test('it can set and get required_buffer_margin as a float percentage', function () {
    // Create a Property instance
    $property = Property::factory()->create();;

    // Example buffer margin as a float percentage
    $requiredBufferMarginValue = 15.5; // 15.5%

    // Use the setter to set the required_buffer_margin as a float
    $property->update([Property::REQUIRED_BUFFER_MARGIN => $requiredBufferMarginValue]);
    $property->save();

    // Assert that the getter converts it to a Percent instance
    expect($property->required_buffer_margin)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->required_buffer_margin->asPercent())->toBe($requiredBufferMarginValue); // Ensures the percent value matches
});

test('it can set and get required_buffer_margin as a fractional float (value <= 1)', function () {
    // Create a Property instance
    $property = Property::factory()->create();;

    // Example buffer margin as a fractional float
    $requiredBufferMarginValue = 0.2; // 20%

    // Use the setter to set the required_buffer_margin as a fractional float
    $property->update([Property::REQUIRED_BUFFER_MARGIN => $requiredBufferMarginValue]);
    $property->save();

    // Assert that the getter converts it to a Percent instance
    expect($property->required_buffer_margin)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->required_buffer_margin->asPercent())->toBe(20.0); // Ensures that it converts the fraction correctly to 20%
});

test('it returns null from getRequiredBufferMargin if no value is set', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Assert that the getter returns null when no value is set
    expect($property->required_buffer_margin)->toBeNull();
});

test('it throws an exception when setting an invalid required_buffer_margin', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    $property->update([Property::REQUIRED_BUFFER_MARGIN => 'invalid_value']);
    // Assert that an exception is thrown when trying to set an invalid value
})->throws(TypeError::class);
/*** Property::REQUIRED_BUFFER_MARGIN ****/

/*** Property::APPRAISAL_VALUE ****/
test('it can set appraisal_value with a float value', function () {
    $property = Property::factory()->create();

    $appraisalValue = 3250000.75; // A float value

    // Set the appraisal_value
    $property->update([Property::APPRAISAL_VALUE => $appraisalValue]);
    $property->save();

    // Assert that appraisal_value is stored and converted to a Price object
    expect($property->appraisal_value)
        ->toBeInstanceOf(Price::class)
        ->and($property->appraisal_value->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($appraisalValue);
});

test('it can set appraisal_value with an integer value', function () {
    $property = Property::factory()->create();

    $appraisalValue = 2750000; // An integer value

    // Set the appraisal_value
    $property->update([Property::APPRAISAL_VALUE => $appraisalValue]);
    $property->save();

    // Assert that appraisal_value is stored as a Price object and integer value matches
    expect($property->appraisal_value)
        ->toBeInstanceOf(Price::class)
        ->and($property->appraisal_value->inclusive()->getAmount()->toInt())
        ->toBe($appraisalValue);
});

test('it can set appraisal_value with a Price object', function () {
    $property = Property::factory()->create();

    // Create a Price instance
    $appraisalValue = MoneyFactory::price(3500000);

    // Set the appraisal_value using the Price object
    $property->update([Property::APPRAISAL_VALUE => $appraisalValue]);
    $property->save();

    // Assert that appraisal_value retains the Price object and matches the amount
    expect($property->appraisal_value)
        ->toBeInstanceOf(Price::class)
        ->and($property->appraisal_value->inclusive()->getAmount()->toInt())
        ->toBe($appraisalValue->inclusive()->getAmount()->toInt());
});

test('it returns null when appraisal_value is not set', function () {
    $property = Property::factory()->create();

    // Assert that the appraisal_value getter returns null if not set
    expect($property->appraisal_value)->toBeNull();
});

test('it can set appraisal_value with a string value', function () {
    $property = Property::factory()->create();

    $appraisalValue = "4500000.50"; // A numeric string

    // Set the appraisal_value using a string
    $property->update([Property::APPRAISAL_VALUE => $appraisalValue]);
    $property->save();

    // Assert that appraisal_value is stored as a Price object and matches the float equivalent
    expect($property->appraisal_value)
        ->toBeInstanceOf(Price::class)
        ->and($property->appraisal_value->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo((float) $appraisalValue);
});

test('it throws an exception when appraisal_value is set with an invalid value', function () {
    $property = Property::factory()->create();

    // Attempt to set an invalid value and expect an exception
    $property->update([Property::APPRAISAL_VALUE => 'not_a_number']);
})->throws(NumberFormatException::class);

test('it handles large appraisal_value values correctly', function () {
    $property = Property::factory()->create();

    $appraisalValue = 1000000000.99; // A large float value

    // Set the appraisal_value with a large value
    $property->update([Property::APPRAISAL_VALUE => $appraisalValue]);
    $property->save();

    // Assert appraisal_value is handled correctly
    expect($property->appraisal_value)
        ->toBeInstanceOf(Price::class)
        ->and($property->appraisal_value->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($appraisalValue);
});

test('it can remove (unset) appraisal_value by passing null', function () {
    $property = Property::factory()->create();

    // Set an initial value
    $property->update([Property::APPRAISAL_VALUE => 2750000]);
    $property->save();

    // Now unset the value by passing null
    $property->update([Property::APPRAISAL_VALUE => null]);
    $property->save();

    // Assert that the appraisal_value is now null
    expect($property->appraisal_value)->toBeNull();
});
/*** Property::APPRAISAL_VALUE ****/

/*** Property::PROCESSING_FEE ****/
test('it can set processing_fee with a float value', function () {
    $property = Property::factory()->create();

    $processingFee = 2500.75; // A float value

    // Set the processing_fee
    $property->update([Property::PROCESSING_FEE => $processingFee]);
    $property->save();

    // Assert that processing_fee is stored and converted to a Price object
    expect($property->processing_fee)
        ->toBeInstanceOf(Price::class)
        ->and($property->processing_fee->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($processingFee);
});

test('it can set processing_fee with an integer value', function () {
    $property = Property::factory()->create();

    $processingFee = 1500; // An integer value

    // Set the processing_fee
    $property->update([Property::PROCESSING_FEE => $processingFee]);
    $property->save();

    // Assert that processing_fee is stored as a Price object and integer value matches
    expect($property->processing_fee)
        ->toBeInstanceOf(Price::class)
        ->and($property->processing_fee->inclusive()->getAmount()->toInt())
        ->toBe($processingFee);
});

test('it can set processing_fee with a Price object', function () {
    $property = Property::factory()->create();

    // Create a Price instance
    $processingFee = MoneyFactory::price(5000);

    // Set the processing_fee using the Price object
    $property->update([Property::PROCESSING_FEE => $processingFee]);
    $property->save();

    // Assert that processing_fee retains the Price object and matches the amount
    expect($property->processing_fee)
        ->toBeInstanceOf(Price::class)
        ->and($property->processing_fee->inclusive()->getAmount()->toInt())
        ->toBe($processingFee->inclusive()->getAmount()->toInt());
});

test('it returns null when processing_fee is not set', function () {
    $property = Property::factory()->create();

    // Assert that the processing_fee getter returns null if not set
    expect($property->processing_fee)->toBeNull();
});

test('it can set processing_fee with a string value', function () {
    $property = Property::factory()->create();

    $processingFee = "3000.50"; // A numeric string

    // Set the processing_fee using a string
    $property->update([Property::PROCESSING_FEE => $processingFee]);
    $property->save();

    // Assert that processing_fee is stored as a Price object and matches the float equivalent
    expect($property->processing_fee)
        ->toBeInstanceOf(Price::class)
        ->and($property->processing_fee->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo((float) $processingFee);
});

test('it throws an exception when processing_fee is set with an invalid value', function () {
    $property = Property::factory()->create();

    // Attempt to set an invalid value and expect an exception
    $property->update([Property::PROCESSING_FEE => 'invalid_value']);
})->throws(NumberFormatException::class);

test('it handles large processing_fee values correctly', function () {
    $property = Property::factory()->create();

    $processingFee = 9999999.99; // A large float value

    // Set the processing_fee with a large value
    $property->update([Property::PROCESSING_FEE => $processingFee]);
    $property->save();

    // Assert processing_fee is handled correctly
    expect($property->processing_fee)
        ->toBeInstanceOf(Price::class)
        ->and($property->processing_fee->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($processingFee);
});

test('it can remove (unset) processing_fee by passing null', function () {
    $property = Property::factory()->create();

    // Set an initial value
    $property->update([Property::PROCESSING_FEE => 4500]);
    $property->save();

    // Now unset the value by passing null
    $property->update([Property::PROCESSING_FEE => null]);
    $property->save();

    // Assert that the processing_fee is now null
    expect($property->processing_fee)->toBeNull();
});
/*** Property::PROCESSING_FEE ****/

/*** Property::PERCENT_LOANABLE_VALUE ****/
test('it can set and get percent_loanable_value as a Percent instance', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example Percent instance
    $percentLoanableValue = Percent::ofPercent(80);

    // Use the setter to set the percent_loanable_value
    $property->update([Property::PERCENT_LOANABLE_VALUE => $percentLoanableValue->value()]);
    $property->save();

    // Assert that the getter returns the same Percent instance
    expect($property->percent_loanable_value)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->percent_loanable_value->value())->toBe($percentLoanableValue->value()); // Ensures the percent value matches
});

test('it can set and get percent_loanable_value as an integer percentage', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example loanable value as an integer percentage
    $percentLoanableValue = 90; // 90%

    // Use the setter to set the percent_loanable_value as an integer
    $property->update([Property::PERCENT_LOANABLE_VALUE => $percentLoanableValue]);
    $property->save();

    // Assert that the getter converts it to a Percent instance
    expect($property->percent_loanable_value)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->percent_loanable_value->asPercent())
        ->toBe((float) $percentLoanableValue); // Ensures the percent value matches
});

test('it can set and get percent_loanable_value as a fractional float (value <= 1)', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example loanable value as a fractional float
    $percentLoanableValue = 0.7; // 70%

    // Use the setter to set the percent_loanable_value as a fractional float
    $property->update([Property::PERCENT_LOANABLE_VALUE => $percentLoanableValue]);
    $property->save();

    // Assert that the getter converts it to a Percent instance
    expect($property->percent_loanable_value)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->percent_loanable_value->asPercent())->toBe(70.0); // Ensures the fraction is correctly converted to 70%
});

test('it can set and get percent_loanable_value as a float percentage', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example loanable value as a float percentage
    $percentLoanableValue = 75.5; // 75.5%

    // Use the setter to set the percent_loanable_value as a float
    $property->update([Property::PERCENT_LOANABLE_VALUE => $percentLoanableValue]);
    $property->save();

    // Assert that the getter converts it to a Percent instance
    expect($property->percent_loanable_value)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->percent_loanable_value->asPercent())->toBe($percentLoanableValue); // Ensures the percent value matches
});

test('it returns null from getPercentLoanableValueAttribute if no value is set', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Assert that the getter returns null when no value is set
    expect($property->percent_loanable_value)->toBeNull();
});

test('it throws an exception when setting an invalid percent_loanable_value', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Try to set an invalid value and expect an exception
    $property->setPercentLoanableValueAttribute('invalid_value');

    // Assert that an exception is thrown
})->throws(TypeError::class);
/*** Property::PERCENT_LOANABLE_VALUE ****/

/*** Property::PERCENT_MISCELLANEOUS_FEES ****/
test('it can set and get percent miscellaneous fees as a Percent instance', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example Percent instance
    $percentMiscellaneousFees = Percent::ofPercent(80);

    // Use the setter to set the percent_miscellaneous_fees
    $property->update([Property::PERCENT_MISCELLANEOUS_FEES => $percentMiscellaneousFees->value()]);
    $property->save();

    // Assert that the getter returns the same Percent instance
    expect($property->percent_miscellaneous_fees)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->percent_miscellaneous_fees->value())->toBe($percentMiscellaneousFees->value()); // Ensures the percent miscellaneous fees
});

test('it can set and get percent_miscellaneous_fees as an integer percentage', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example loanable value as an integer percentage
    $percentMiscellaneousFees = 90; // 90%

    // Use the setter to set the percent_miscellaneous_fees as an integer
    $property->update([Property::PERCENT_MISCELLANEOUS_FEES => $percentMiscellaneousFees]);
    $property->save();

    // Assert that the getter converts it to a Percent instance
    expect($property->percent_miscellaneous_fees)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->percent_miscellaneous_fees->asPercent())
        ->toBe((float) $percentMiscellaneousFees); // Ensures the percent miscellaneous fees matches
});

test('it can set and get percent_miscellaneous_fees as a fractional float (value <= 1)', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example loanable value as a fractional float
    $percentMiscellaneousFees = 0.7; // 70%

    // Use the setter to set the percent_miscellaneous_fees as a fractional float
    $property->update([Property::PERCENT_MISCELLANEOUS_FEES => $percentMiscellaneousFees]);
    $property->save();

    // Assert that the getter converts it to a Percent instance
    expect($property->percent_miscellaneous_fees)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->percent_miscellaneous_fees->asPercent())->toBe(70.0); // Ensures the fraction is correctly converted to 70%
});

test('it can set and get percent_miscellaneous_fees as a float percentage', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example percent miscellaneous fees as a float percentage
    $percentMiscellaneousFees = 75.5; // 75.5%

    // Use the setter to set the percent_miscellaneous_fees as a float
    $property->update([Property::PERCENT_MISCELLANEOUS_FEES => $percentMiscellaneousFees]);
    $property->save();

    // Assert that the getter converts it to a Percent instance
    expect($property->percent_miscellaneous_fees)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->percent_miscellaneous_fees->asPercent())->toBe($percentMiscellaneousFees); // Ensures the percent value matches
});

test('it returns null from percent_miscellaneous_fees if no value is set', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Assert that the getter returns null when no value is set
    expect($property->percent_miscellaneous_fees)->toBeNull();
});

test('it throws an exception when setting an invalid percent_miscellaneous_fees', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Try to set an invalid value and expect an exception
    $property->update([Property::PERCENT_MISCELLANEOUS_FEES => 'invalid_value']);

    // Assert that an exception is thrown
})->throws(TypeError::class);
/*** Property::PERCENT_MISCELLANEOUS_FEES ****/

/*** Property::LENDING_INSTITUTION ****/
test('it can set and get lending_institution as a LendingInstitution instance', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example LendingInstitution instance
    $lendingInstitution = new LendingInstitution('hdmf');

    // Use the setter to set the lending_institution
    $property->update([Property::LENDING_INSTITUTION => $lendingInstitution->key()]);
    $property->save();

    // Assert that the getter returns a LendingInstitution instance with a matching key
    expect($property->lending_institution)
        ->toBeInstanceOf(LendingInstitution::class) // Ensures it is a LendingInstitution instance
        ->and($property->lending_institution->key())->toBe($lendingInstitution->key()); // Ensures the key matches
});

test('it can set and get lending_institution using a string key', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example LendingInstitution key
    $lendingInstitutionKey = 'hdmf';

    // Use the setter to set the lending_institution using a string key
    $property->update([Property::LENDING_INSTITUTION => $lendingInstitutionKey]);
    $property->save();

    // Assert that the getter returns a LendingInstitution instance
    expect($property->lending_institution)
        ->toBeInstanceOf(LendingInstitution::class) // Ensures it is a LendingInstitution instance
        ->and($property->lending_institution->key())->toBe($lendingInstitutionKey); // Ensures the key matches
});

test('it throws an exception when setting an invalid lending_institution', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Try to set an invalid LendingInstitution key
    $property->setLendingInstitutionAttribute('invalid_key');
})->throws(InvalidArgumentException::class, 'Invalid lending institution key: invalid_key');

test('it returns null from getLendingInstitutionAttribute if no value is set', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Assert that the getter returns null when no value is set
    expect($property->lending_institution)->toBeNull();
});
/*** Property::LENDING_INSTITUTION ****/

/*** Property::INCOME_REQUIREMENT_MULTIPLIER ****/

test('it can set and get income_requirement_multiplier as a Percent instance', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example Percent instance
    $incomeMultiplier = Percent::ofPercent(10); // 10%

    // Use the setter to set the income requirement multiplier
    $property->update([Property::INCOME_REQUIREMENT_MULTIPLIER => $incomeMultiplier->value()]);
    $property->save();

    // Assert that the getter returns the same Percent instance
    expect($property->income_requirement_multiplier)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->income_requirement_multiplier->value())->toBe($incomeMultiplier->value()); // Ensures the percent value matches
});

test('it can set and get income_requirement_multiplier as an integer percentage', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example income requirement multiplier as an integer
    $incomeMultiplier = 15; // 15%

    // Use the setter to set the income requirement multiplier
    $property->update([Property::INCOME_REQUIREMENT_MULTIPLIER => $incomeMultiplier]);
    $property->save();

    // Assert it is converted to a Percent instance
    expect($property->income_requirement_multiplier)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->income_requirement_multiplier->asPercent())
        ->toBe((float) $incomeMultiplier); // Ensures the value matches
});

test('it can set and get income_requirement_multiplier as a fractional float (value <= 1)', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Example income requirement multiplier as a fractional float
    $incomeMultiplier = 0.2; // 20%

    // Use the setter
    $property->update([Property::INCOME_REQUIREMENT_MULTIPLIER => $incomeMultiplier]);
    $property->save();

    // Assert it is converted to a Percent instance
    expect($property->income_requirement_multiplier)
        ->toBeInstanceOf(Percent::class) // Ensures it is a Percent instance
        ->and($property->income_requirement_multiplier->asPercent())->toBe(20.0); // Ensures the converted value matches
});

test('it returns null from getIncomeRequirementMultiplierAttribute if no value is set', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Assert that the getter returns null if no value is set
    expect($property->income_requirement_multiplier)->toBeNull();
});

test('it throws an exception when setting an invalid income_requirement_multiplier', function () {
    // Create a Property instance
    $property = Property::factory()->create();

    // Try to set an invalid value for income requirement multiplier
    $property->setIncomeRequirementMultiplierAttribute('invalid_value');
})->throws(TypeError::class);
/*** Property::INCOME_REQUIREMENT_MULTIPLIER ****/

/*** Property::toDomain() ****/
test('it converts eloquent Property model to domain Property object', function () {
    $eloquent = Property::factory()->create([
        'code' => 'RDG1000',
        'name' => 'RDG 1.0M',
        'type' => 'residential',
        'cluster' => 'A1',
        'status' => 'available',
//        'sku' => 'RDG1000',
    ]);

    $tcp = 1_000_000;
    $appraisal = 950_000;
    $pf = 5000;
    $pdp = 90;
    $pmf = 8.5;
    $pdi = 35;
    $buffer = 10;
    $devType = DevelopmentType::BP_957;
    $devForm = DevelopmentForm::HORIZONTAL;
    $lendingInstitution = 'hdmf';
    $incomeRequirementMultiplier = 35;

    $eloquent->update([
        Property::TOTAL_CONTRACT_PRICE => $tcp,
        Property::APPRAISAL_VALUE => $appraisal,
        Property::PROCESSING_FEE => $pf,
        Property::PERCENT_LOANABLE_VALUE => $pdp,
        Property::PERCENT_MISCELLANEOUS_FEES => $pmf,
        Property::REQUIRED_BUFFER_MARGIN => $buffer,
        Property::DEVELOPMENT_TYPE => $devType->value,
        Property::DEVELOPMENT_FORM => $devForm->value,
        Property::LENDING_INSTITUTION => $lendingInstitution,
        Property::INCOME_REQUIREMENT_MULTIPLIER => $incomeRequirementMultiplier,
    ]);

    $domain = $eloquent->toDomain();

    expect($domain)->toBeInstanceOf(DomainProperty::class)
        ->and($domain->getTotalContractPrice()->inclusive()->getAmount()->toInt())->toBe($tcp)
        ->and($domain->getAppraisalValue()->inclusive()->getAmount()->toInt())->toBe($appraisal)
        ->and($domain->getProcessingFee()->inclusive()->getAmount()->toInt())->toBe($pf)
        ->and($domain->getPercentLoanableValue()->asPercent())->toBeCloseTo($pdp)
        ->and($domain->getPercentMiscellaneousFees()->asPercent())->toBeCloseTo($pmf)
        ->and($domain->getRequiredBufferMargin()->asPercent())->toBeCloseTo($buffer)
        ->and($domain->getDevelopmentType())->toBe($devType)
        ->and($domain->getDevelopmentForm())->toBe($devForm)
        ->and($domain->getLendingInstitution()->key())->toBe($lendingInstitution)
        ->and($domain->getIncomeRequirementMultiplier()->asPercent())->toBeCloseTo($incomeRequirementMultiplier)
    ;
});
/*** Property::toDomain() ****/

/*** Property::getRouteKeyName() ****/
test('property uses code as route key', function () {
    $property = Property::factory()->create(['code' => 'RDG1000']);

    expect($property->getRouteKeyName())->toBe('code')
        ->and($property->getRouteKey())->toBe('RDG1000');
});

test('resolves property by code via route model binding', function () {
    $property = Property::factory()->create([
        'code' => 'RDG1000',
        'name' => 'RDG 1.0M',
    ]);

    $this->get("/properties/{$property->code}")
        ->assertOk()
        ->assertJson([
            'code' => 'RDG1000',
            'name' => 'RDG 1.0M',
        ]);

    $this->get(route('test-property', ['property' => $property->code]))
        ->assertOk()
        ->assertJson([
            'code' => 'RDG1000',
            'name' => 'RDG 1.0M',
        ]);
});
/*** Property::getRouteKeyName() ****/

/*** Property::scopeWithMeta() ****/
test('it filters using withMeta with key-only', function () {
    Property::factory()->create([
        'code' => 'WKEY1',
        Property::TOTAL_CONTRACT_PRICE => 2_000_000,
    ]);

    Property::factory()->create([
        'code' => 'WKEY2',
        'meta' => [], // Missing total_contract_price
    ]);

    $results = Property::query()->withMeta('total_contract_price')->pluck('code')->all();

    expect($results)->toContain('WKEY1')->not->toContain('WKEY2');
});

test('it filters using withMeta with operator and value', function () {
    Property::factory()->create([
        'code' => 'WVAL1',
        Property::TOTAL_CONTRACT_PRICE => 1_500_000,
    ]);

    Property::factory()->create([
        'code' => 'WVAL2',
        Property::TOTAL_CONTRACT_PRICE => 750_000,
    ]);

    $results = Property::query()
        ->withMeta('total_contract_price', '>=', 1_000_000 * 100)
        ->pluck('code')
        ->all();

    expect($results)->toContain('WVAL1')->not->toContain('WVAL2');
});

test('it filters using withMeta with array of keys', function () {
    Property::factory()->create([
        'code' => 'WMULTI1',
        Property::TOTAL_CONTRACT_PRICE => 1_000_000,
        Property::APPRAISAL_VALUE => 1_000_000,
    ]);

    Property::factory()->create([
        'code' => 'WMULTI2',
        Property::TOTAL_CONTRACT_PRICE => 1_000_000,
        // No appraisal_value
    ]);

    $results = Property::query()
        ->withMeta([Property::TOTAL_CONTRACT_PRICE, Property::APPRAISAL_VALUE])
        ->pluck('code')
        ->all();

    expect($results)->toContain('WMULTI1')->not->toContain('WMULTI2');
});
/*** Property::scopeWithMeta() ****/
