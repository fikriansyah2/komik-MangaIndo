<div id="ai-chatbot" class="fixed left-4 bottom-4 z-50">
    <div id="ai-chatbot-toggle" class="w-14 h-14 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-lg cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
            <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H7l-5 3V5z" />
        </svg>
    </div>

    <div id="ai-chatbot-panel" class="hidden w-80 max-h-96 bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden flex flex-col mt-3">
        <div class="px-4 py-2 bg-gray-100 dark:bg-gray-900 border-b dark:border-gray-700 flex items-center justify-between">
            <div class="font-semibold text-sm">AI Chat</div>
            <button id="ai-chatbot-close" class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">✕</button>
        </div>

        <div id="ai-chatbot-messages" class="p-3 flex-1 overflow-auto space-y-2 bg-white dark:bg-gray-800">
            <!-- Messages appended here -->
        </div>

        <div class="p-2 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <form id="ai-chatbot-form" class="flex gap-2">
                <input id="ai-chatbot-input" type="text" placeholder="Tanya sesuatu..." class="flex-1 px-3 py-2 rounded-md border dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:outline-none" />
                <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm">Kirim</button>
            </form>
        </div>
    </div>
</div>
