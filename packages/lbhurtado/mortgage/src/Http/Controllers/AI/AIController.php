<?php

namespace LBHurtado\Mortgage\Http\Controllers\AI;

use LBHurtado\Mortgage\Http\Controllers\Controller;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    public function interact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string'],
        ]);

        Log::info('AI Interact Request Received', [
            'prompt' => $validated['prompt'],
        ]);

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => $validated['prompt']],
                ],
            ]);

            $content = $response->choices[0]->message->content ?? 'No response.';

            Log::info('AI Interact Response', [
                'response' => $content,
            ]);

            return response()->json(['response' => $content]);
        } catch (\Throwable $e) {
            Log::error('AI Interact Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['response' => 'Something went wrong.'], 500);
        }
    }
}
