<script setup lang="js">
import { ref } from 'vue'
import axios from 'axios'

const prompt = ref('')
const response = ref('')
const loading = ref(false)

const sendPrompt = async () => {
    loading.value = true
    response.value = ''
    try {
        const { data } = await axios.post(route('api.ai.interact'), { prompt: prompt.value })
        response.value = data.response ?? 'No response from AI.'
    } catch (e) {
        response.value = 'Something went wrong: ' + (e.message || 'Unknown error')
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="max-w-xl mx-auto mt-10 space-y-4">
        <textarea v-model="prompt" class="w-full p-2 border rounded" rows="4" placeholder="Ask me anything..."></textarea>
        <button @click="sendPrompt" :disabled="loading" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            {{ loading ? 'Thinking...' : 'Send to AI' }}
        </button>
        <div class="whitespace-pre-wrap p-4 bg-gray-100 rounded border" v-if="response">
            {{ response }}
        </div>
    </div>
</template>
