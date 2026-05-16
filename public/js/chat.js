//scroll to bottom
const chatMessages = document.getElementById('chat-messages');
if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;

//new chat
function filterFollowing(query) {
    document.querySelectorAll('#follow-list .follow-item').forEach(item => {
        item.style.display = item.innerText.toLowerCase().includes(query.toLowerCase()) ? 'flex' : 'none';
    });
}

//ajax
const conversationId = new URLSearchParams(window.location.search).get('conversation');
if (conversationId && chatMessages) {
    let lastMessageId = 0;
    //last msg
    const bubbles = chatMessages.querySelectorAll('.message-bubble[data-msg-id]');
    if (bubbles.length) lastMessageId = parseInt(bubbles[bubbles.length - 1].dataset.msgId) || 0;

    const myUserId = parseInt(document.body.dataset.userId || '0');

    async function pollMessages() {
        try {
            const res = await fetch(`../private/poll-messages.php?conversation=${conversationId}&after=${lastMessageId}`);
            const data = await res.json();
            if (data.messages?.length) {
                data.messages.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = 'message-bubble ' + (msg.isMine ? 'sent' : 'received');
                    div.dataset.msgId = msg.id;
                    div.innerHTML = `<p>${escHtml(msg.Text)}</p><span class="message-time">${msg.TimeSent}</span>`;
                    chatMessages.appendChild(div);
                    lastMessageId = Math.max(lastMessageId, parseInt(msg.id));
                });
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        } 
        catch(e){}
    }

    setInterval(pollMessages, 3000);
    
    const chatForm = document.querySelector('.chat-input-form');
    const chatInput = chatForm?.querySelector('input[name="text"]');
    if (chatForm && chatInput) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text) return;
            chatInput.value = '';
            chatInput.focus();
            const body = new URLSearchParams(new FormData(chatForm));
            body.set('text', text);
            try {
                await fetch('../private/send-message.php?ajax=1', { method: 'POST', body });
                pollMessages();
            }
            catch (err) {
                chatInput.value = text; 
            }
        });
    }
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}