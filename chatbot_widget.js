/**
 * TouRz Floating Chatbot Widget
 * Self-contained — injects HTML + CSS + handles all chat logic
 */
(function () {
  // Don't load on admin pages
  if (window.location.pathname.includes('admin')) return;

  // ===== INJECT CSS =====
  const style = document.createElement('style');
  style.textContent = `
    #tourz-chatbot-btn {
      position: fixed;
      bottom: 25px;
      right: 25px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, #1a4a4a 0%, #2d7d7d 100%);
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.3s, box-shadow 0.3s;
      animation: tourz-pulse 2s infinite;
    }
    #tourz-chatbot-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 30px rgba(0,0,0,0.4);
    }
    #tourz-chatbot-btn svg {
      width: 28px;
      height: 28px;
      fill: white;
    }
    @keyframes tourz-pulse {
      0%, 100% { box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
      50% { box-shadow: 0 4px 30px rgba(45,125,125,0.6); }
    }

    #tourz-chatbot-panel {
      position: fixed;
      bottom: 95px;
      right: 25px;
      width: 380px;
      height: 520px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 10px 50px rgba(0,0,0,0.25);
      z-index: 99999;
      display: none;
      flex-direction: column;
      overflow: hidden;
      font-family: 'Poppins', 'Segoe UI', sans-serif;
      animation: tourz-slide-up 0.3s ease;
    }
    @keyframes tourz-slide-up {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    #tourz-chatbot-panel.open {
      display: flex;
    }

    .tourz-chat-header {
      background: linear-gradient(135deg, #1a4a4a 0%, #2d7d7d 100%);
      color: white;
      padding: 16px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-shrink: 0;
    }
    .tourz-chat-header-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .tourz-chat-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: rgba(255,255,255,0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }
    .tourz-chat-header h4 {
      margin: 0;
      font-size: 15px;
      font-weight: 600;
    }
    .tourz-chat-header p {
      margin: 0;
      font-size: 11px;
      opacity: 0.8;
    }
    .tourz-chat-close {
      background: none;
      border: none;
      color: white;
      font-size: 22px;
      cursor: pointer;
      padding: 0;
      line-height: 1;
      opacity: 0.7;
      transition: opacity 0.2s;
    }
    .tourz-chat-close:hover { opacity: 1; }

    .tourz-chat-messages {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      background: #f7f8fa;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .tourz-chat-messages::-webkit-scrollbar { width: 4px; }
    .tourz-chat-messages::-webkit-scrollbar-thumb { background: #ccc; border-radius: 2px; }

    .tourz-msg {
      max-width: 85%;
      padding: 10px 14px;
      border-radius: 14px;
      font-size: 13px;
      line-height: 1.5;
      word-wrap: break-word;
      white-space: pre-wrap;
    }
    .tourz-msg-bot {
      background: white;
      color: #333;
      align-self: flex-start;
      border-bottom-left-radius: 4px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    }
    .tourz-msg-user {
      background: linear-gradient(135deg, #1a4a4a, #2d7d7d);
      color: white;
      align-self: flex-end;
      border-bottom-right-radius: 4px;
    }
    .tourz-msg-bot a {
      color: #2d7d7d;
      text-decoration: underline;
    }

    .tourz-typing {
      align-self: flex-start;
      padding: 10px 14px;
      background: white;
      border-radius: 14px;
      border-bottom-left-radius: 4px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.08);
      display: none;
    }
    .tourz-typing span {
      display: inline-block;
      width: 7px;
      height: 7px;
      background: #aaa;
      border-radius: 50%;
      margin: 0 2px;
      animation: tourz-dot 1.4s infinite;
    }
    .tourz-typing span:nth-child(2) { animation-delay: 0.2s; }
    .tourz-typing span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes tourz-dot {
      0%, 60%, 100% { transform: translateY(0); }
      30% { transform: translateY(-6px); }
    }

    .tourz-chat-input {
      display: flex;
      padding: 12px 16px;
      background: white;
      border-top: 1px solid #eee;
      gap: 10px;
      flex-shrink: 0;
    }
    .tourz-chat-input input {
      flex: 1;
      border: 1px solid #e0e0e0;
      border-radius: 24px;
      padding: 10px 16px;
      font-size: 13px;
      outline: none;
      font-family: inherit;
      transition: border-color 0.2s;
    }
    .tourz-chat-input input:focus {
      border-color: #2d7d7d;
    }
    .tourz-chat-input button {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: none;
      background: linear-gradient(135deg, #1a4a4a, #2d7d7d);
      color: white;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s;
      flex-shrink: 0;
    }
    .tourz-chat-input button:hover { transform: scale(1.05); }
    .tourz-chat-input button svg { width: 18px; height: 18px; fill: white; }

    .tourz-quick-btns {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      padding: 0 16px 10px;
      background: #f7f8fa;
    }
    .tourz-quick-btn {
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 16px;
      padding: 5px 12px;
      font-size: 11px;
      cursor: pointer;
      color: #555;
      transition: all 0.2s;
      font-family: inherit;
    }
    .tourz-quick-btn:hover {
      background: #1a4a4a;
      color: white;
      border-color: #1a4a4a;
    }
  `;
  document.head.appendChild(style);

  // ===== INJECT HTML =====
  const widget = document.createElement('div');
  widget.innerHTML = `
    <button id="tourz-chatbot-btn" title="Chat with us!">
      <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12zM7 9h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"/></svg>
    </button>
    <div id="tourz-chatbot-panel">
      <div class="tourz-chat-header">
        <div class="tourz-chat-header-left">
          <div class="tourz-chat-avatar">🤖</div>
          <div>
            <h4>TouRz Assistant</h4>
            <p>Online • Typically replies instantly</p>
          </div>
        </div>
        <button class="tourz-chat-close" id="tourz-chat-close">×</button>
      </div>
      <div class="tourz-chat-messages" id="tourz-chat-messages">
        <div class="tourz-msg tourz-msg-bot">Hello! 👋 I'm your TouRz assistant. Ask me about tours, prices, bookings, or anything else!</div>
      </div>
      <div class="tourz-quick-btns" id="tourz-quick-btns">
        <button class="tourz-quick-btn" data-q="Show all tours">📍 All Tours</button>
        <button class="tourz-quick-btn" data-q="Show prices">💰 Prices</button>
        <button class="tourz-quick-btn" data-q="How to book">🎫 How to Book</button>
        <button class="tourz-quick-btn" data-q="Upcoming tours">📅 Upcoming</button>
        <button class="tourz-quick-btn" data-q="Contact information">📞 Contact</button>
      </div>
      <div class="tourz-chat-input">
        <input type="text" id="tourz-chat-input" placeholder="Ask me anything..." autocomplete="off">
        <button id="tourz-chat-send">
          <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
      </div>
    </div>
  `;
  document.body.appendChild(widget);

  // ===== LOGIC =====
  const btn = document.getElementById('tourz-chatbot-btn');
  const panel = document.getElementById('tourz-chatbot-panel');
  const closeBtn = document.getElementById('tourz-chat-close');
  const messagesDiv = document.getElementById('tourz-chat-messages');
  const inputEl = document.getElementById('tourz-chat-input');
  const sendBtn = document.getElementById('tourz-chat-send');
  const quickBtns = document.getElementById('tourz-quick-btns');

  btn.addEventListener('click', () => {
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) {
      btn.style.display = 'none';
      inputEl.focus();
    }
  });

  closeBtn.addEventListener('click', () => {
    panel.classList.remove('open');
    btn.style.display = 'flex';
  });

  // Quick buttons
  quickBtns.addEventListener('click', (e) => {
    const qBtn = e.target.closest('.tourz-quick-btn');
    if (qBtn) {
      sendMessage(qBtn.dataset.q);
      quickBtns.style.display = 'none';
    }
  });

  sendBtn.addEventListener('click', () => {
    const text = inputEl.value.trim();
    if (text) sendMessage(text);
  });

  inputEl.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      const text = inputEl.value.trim();
      if (text) sendMessage(text);
    }
  });

  function addMessage(text, isUser) {
    const div = document.createElement('div');
    div.className = 'tourz-msg ' + (isUser ? 'tourz-msg-user' : 'tourz-msg-bot');
    // Simple markdown: **bold** and [link](url)
    let html = text
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank">$1</a>')
      .replace(/\n/g, '<br>');
    div.innerHTML = html;
    messagesDiv.appendChild(div);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }

  function showTyping() {
    let t = document.getElementById('tourz-typing');
    if (!t) {
      t = document.createElement('div');
      t.className = 'tourz-typing';
      t.id = 'tourz-typing';
      t.innerHTML = '<span></span><span></span><span></span>';
      messagesDiv.appendChild(t);
    }
    t.style.display = 'block';
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }

  function hideTyping() {
    const t = document.getElementById('tourz-typing');
    if (t) t.style.display = 'none';
  }

  async function sendMessage(text) {
    addMessage(text, true);
    inputEl.value = '';
    showTyping();

    try {
      const res = await fetch('chatbot_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query: text })
      });
      const data = await res.json();
      hideTyping();
      addMessage(data.response || 'Sorry, something went wrong.', false);
    } catch (err) {
      hideTyping();
      addMessage("⚠️ Couldn't reach the server. Please try again.", false);
    }
  }
})();
