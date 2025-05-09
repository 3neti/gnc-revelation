<?php

use Illuminate\Support\Facades\{File, Route};
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/properties/{property}', function (\LBHurtado\Mortgage\Models\Property $property) {
    return response()->json([
        'code' => $property->code,
        'name' => $property->name,
    ]);
})->name('test-property');

// routes/web.php or api.php
Route::get('/openapi.yaml', fn () => response()->file(base_path('openapi.yaml'), [
    'Content-Type' => 'text/yaml'
]));

Route::get('/openapi.yaml', function () {
    $path = base_path('packages/lbhurtado/mortgage/resources/docs/openapi.yaml');
    abort_unless(File::exists($path), 404);

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'text/yaml',
    ]);
})->name('openapi.yaml');

use OpenAI\Laravel\Facades\OpenAI;

Route::get('/test-gpt', function () {
    $assistantId = 'asst_R2Gqs8URxnIX2OZzMk7abR9V'; // Replace with your Assistant ID

    // 1. Create a thread
    $thread = OpenAI::threads()->create();

    // 2. Add a message to the thread
    OpenAI::threads()->messages()->create($thread->id, [
        'role' => 'user',
        'content' => 'What is the current interest rate for a ₱1,000,000 property?',
    ]);

    // 3. Run the assistant
    $run = OpenAI::threads()->runs()->create($thread->id, [
        'assistant_id' => $assistantId,
    ]);

    // 4. Wait for completion
    do {
        $status = OpenAI::threads()->runs()->retrieve($thread->id, $run->id)->status;
        usleep(500000); // 0.5 sec
    } while ($status !== 'completed');

    // 5. Get the assistant's reply
    $messages = OpenAI::threads()->messages()->list($thread->id);
    $reply = collect($messages->data)->last()->content[0]->text->value;

    return response()->json(['reply' => $reply]);
});

Route::get('/ai-chat', fn () => Inertia::render('AIChat'))->name('ai.chat');

Route::view('/privacy', 'privacy')->name('privacy');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
