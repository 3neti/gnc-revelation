<?php

namespace LBHurtado\Mortgage\Http\Controllers\AI;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\{Cache, Http};
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class AssistantController
{
    protected string $assistantId;

    public function __construct()
    {
        $this->assistantId = config('services.openai.assistant_id');
    }

    public function chat(Request $request): StreamedResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $threadId = Cache::get('openai_thread_id', function () {
            $id = $this->createThread();
            Cache::put('openai_thread_id', $id, now()->addMinutes(30));
            return $id;
        });

        $messageId = $this->addMessage($threadId, $request->input('message'));
        $runId = $this->runAssistant($threadId);

        return response()->stream(function () use ($threadId, $runId) {
            while (true) {
                $status = $this->getRunStatus($threadId, $runId);
                if ($status === 'completed') break;
                usleep(300_000); // 300ms
            }

            $response = $this->getMessages($threadId);
            echo json_encode(['reply' => $response]);
        }, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    protected function createThread(): string
    {
        $response = Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/threads');

        return $response['id'];
    }

    protected function addMessage(string $threadId, string $message): string
    {
        $response = Http::withToken(config('services.openai.api_key'))
            ->post("https://api.openai.com/v1/threads/{$threadId}/messages", [
                'role' => 'user',
                'content' => $message,
            ]);

        return $response['id'];
    }

    protected function runAssistant(string $threadId): string
    {
        $response = Http::withToken(config('services.openai.api_key'))
            ->post("https://api.openai.com/v1/threads/{$threadId}/runs", [
                'assistant_id' => $this->assistantId,
            ]);

        return $response['id'];
    }

    protected function getRunStatus(string $threadId, string $runId): string
    {
        $response = Http::withToken(config('services.openai.api_key'))
            ->get("https://api.openai.com/v1/threads/{$threadId}/runs/{$runId}");

        return $response['status'];
    }

    protected function getMessages(string $threadId): string
    {
        $response = Http::withToken(config('services.openai.api_key'))
            ->get("https://api.openai.com/v1/threads/{$threadId}/messages");

        $messages = $response['data'];
        return collect($messages)
            ->where('role', 'assistant')
            ->sortByDesc('created_at')
            ->first()['content'][0]['text']['value'] ?? 'No reply.';
    }
}
