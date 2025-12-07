/* Chatbot frontend script
   - toggles panel
   - sends message to server route POST /ai/chat
   - displays user and assistant messages
*/
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('ai-chatbot-toggle');
    const panel = document.getElementById('ai-chatbot-panel');
    const closeBtn = document.getElementById('ai-chatbot-close');
    const form = document.getElementById('ai-chatbot-form');
    const input = document.getElementById('ai-chatbot-input');
    const messagesEl = document.getElementById('ai-chatbot-messages');

    function appendMessage(role, text) {
        const wrapper = document.createElement('div');
        wrapper.className = role === 'user' ? 'text-right' : 'text-left';

        const bubble = document.createElement('div');
        bubble.className = role === 'user' ? 'inline-block bg-blue-600 text-white px-3 py-2 rounded-lg text-sm' : 'inline-block bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 rounded-lg text-sm';
        bubble.innerText = text;

        wrapper.appendChild(bubble);
        messagesEl.appendChild(wrapper);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function setLoading(loading) {
        if (loading) {
            const el = document.createElement('div');
            el.id = 'ai-chatbot-loading';
            el.className = 'text-left text-sm text-gray-500';
            el.innerText = 'AI sedang mengetik...';
            messagesEl.appendChild(el);
            messagesEl.scrollTop = messagesEl.scrollHeight;
        } else {
            const el = document.getElementById('ai-chatbot-loading');
            if (el) el.remove();
        }
    }

    toggle.addEventListener('click', () => {
        panel.classList.toggle('hidden');
        const inputEl = document.getElementById('ai-chatbot-input');
        if (!panel.classList.contains('hidden')) inputEl.focus();
    });

    closeBtn.addEventListener('click', () => panel.classList.add('hidden'));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        appendMessage('user', text);
        input.value = '';
        setLoading(true);

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch('/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: text })
            });

            const data = await res.json();
            setLoading(false);

            if (res.ok && data && data.reply) {
                appendMessage('assistant', data.reply);
            } else {
                appendMessage('assistant', data.error || 'Terjadi kesalahan saat memproses pesan.');
            }
        } catch (err) {
            setLoading(false);
            appendMessage('assistant', 'Gagal mengirim pesan: ' + err.message);
        }
    });
});
