<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Grocery+ AI Assistant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        grocery: { 50:'#f3f6f8', 100:'#e3ebf0', 900:'#003b5c', 950:'#0d1114' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .msg-enter { animation: slideUp .25s ease forwards; }
        @keyframes slideUp { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
        .dot-1 { animation: bounce 1s infinite 0s; }
        .dot-2 { animation: bounce 1s infinite .15s; }
        .dot-3 { animation: bounce 1s infinite .3s; }
        #messages { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.15) transparent; }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-grocery-950 via-[#003b5c] to-[#001d2e]">

@php
    $topics = [
        'Session Overview' => [
            ['title' => 'Laravel AI SDK (what & why)', 'hint' => 'Introduction + Installation'],
            ['title' => 'Configuration', 'hint' => 'Providers, base URLs, options'],
        ],
        'Agents' => [
            ['title' => 'Agent basics', 'hint' => 'instructions, prompting, tools'],
            ['title' => 'Messages & conversation context', 'hint' => 'messages() vs remembered context'],
            ['title' => 'Remembering conversations', 'hint' => 'RemembersConversations'],
            ['title' => 'Structured output', 'hint' => 'schemas + validation'],
            ['title' => 'Attachments & streaming', 'hint' => 'files + streaming responses'],
            ['title' => 'Middleware', 'hint' => 'pre/post hooks, guardrails'],
            ['title' => 'Sub-agents', 'hint' => 'delegate tasks safely'],
        ],
        'Tools' => [
            ['title' => 'Tools basics', 'hint' => 'function calling'],
            ['title' => 'Schema', 'hint' => 'tool input contract'],
            ['title' => 'Similarity search', 'hint' => 'embeddings + retrieval'],
            ['title' => 'Provider tools', 'hint' => 'web search / fetch / file search'],
        ],
        'Embeddings' => [
            ['title' => 'Embeddings (concept)', 'hint' => 'vectors + semantic search'],
            ['title' => 'Querying embeddings', 'hint' => 'search & retrieval'],
            ['title' => 'Caching embeddings', 'hint' => 'performance + cost'],
        ],
        'Other' => [
            ['title' => 'Images / Audio / Transcriptions', 'hint' => 'multimodal'],
            ['title' => 'Vector stores', 'hint' => 'files + stores'],
            ['title' => 'Failover & testing', 'hint' => 'reliability'],
            ['title' => 'Events', 'hint' => 'observability'],
        ],
    ];
@endphp

<div class="h-screen max-w-6xl mx-auto px-4 py-4">
    <div class="h-full flex gap-4 min-h-0">

        {{-- Sidebar (topics) --}}
        <aside class="hidden lg:flex lg:w-[320px] shrink-0 min-h-0">
            <div class="w-full rounded-2xl bg-white/10 backdrop-blur border border-white/15 overflow-hidden flex flex-col min-h-0">
                <div class="px-4 py-3 border-b border-white/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-white text-sm font-bold">Session topics</div>
                            <div class="text-white/50 text-xs">Laravel AI SDK · Agents · Tools · Schema · Middleware</div>
                        </div>
                        <a href="https://laravel.com/docs/13.x/ai-sdk"
                           target="_blank"
                           class="text-xs text-emerald-200 hover:text-emerald-100 underline underline-offset-4">
                            Docs
                        </a>
                    </div>
                </div>

                <div class="px-2 py-2 overflow-y-auto min-h-0">
                    @foreach($topics as $section => $items)
                        <div class="px-2 py-2">
                            <div class="text-[11px] uppercase tracking-wide text-white/45 font-semibold px-2 mb-2">
                                {{ $section }}
                            </div>
                            <div class="space-y-1">
                                @foreach($items as $item)
                                    <div class="group px-3 py-2 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 transition cursor-default">
                                        <div class="text-white text-xs font-semibold leading-snug">
                                            {{ $item['title'] }}
                                        </div>
                                        <div class="text-white/45 text-[11px] leading-snug">
                                            {{ $item['hint'] }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-4 py-3 border-t border-white/10 text-[11px] text-white/40">
                    Tip: اكتب سؤالك في الشات وهنمشي Topic by Topic.
                </div>
            </div>
        </aside>

        {{-- Main chat --}}
        <main class="flex-1 min-h-0">
            <div class="flex flex-col h-full gap-3 min-h-0">

    {{-- Header --}}
    <header class="flex items-center justify-between px-5 py-3 rounded-2xl bg-white/10 backdrop-blur border border-white/15 shrink-0">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-400 text-grocery-950 font-black text-lg shadow-lg">
                G+
            </div>
            <div>
                <h1 class="font-bold text-white text-sm leading-tight">Grocery+ Assistant</h1>
                <p class="text-xs text-white/50">Powered by <span class="text-emerald-300 font-semibold"> AI</span> · Real database</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="resetChat()" title="New conversation"
                    class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white/70 hover:text-white text-xs px-3 py-1.5 rounded-full transition">
                ↺ New chat
            </button>
            <div class="flex items-center gap-2 bg-emerald-400/20 px-3 py-1 rounded-full">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-emerald-200 text-xs font-bold">Live</span>
            </div>
        </div>
    </header>

    {{-- Tool badge --}}
    <div class="shrink-0 rounded-xl bg-grocery-950/70 border border-emerald-500/30 px-4 py-2 font-mono text-xs text-emerald-300 backdrop-blur overflow-x-auto whitespace-nowrap">
        <span class="text-white/40">// </span>
        <span class="text-blue-300">SearchProducts</span>
        <span class="text-white/60"> · </span>
        <span class="text-blue-300">CheckOffers</span>
        <span class="text-white/60"> · </span>
        <span class="text-blue-300">ListCategories</span>
        <span class="text-white/50"> → real MySQL database · function calling enabled</span>
    </div>

    {{-- Messages --}}
    <div id="messages" class="flex-1 overflow-y-auto space-y-3 pr-1 min-h-0">

        {{-- Welcome --}}
        <div class="flex gap-3 msg-enter">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-grocery-950 text-xs font-black">AI</div>
            <div class="max-w-[85%] bg-white rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
                <p class="text-sm text-gray-800 leading-relaxed">
                    👋 <strong>مرحباً! Welcome to Grocery+!</strong><br>
                    I'm your AI shopping assistant connected to the live store. I can:
                </p>
                <ul class="mt-2 text-sm text-gray-700 space-y-1">
                    <li>🔍 Search real products from the database</li>
                    <li>💰 Show active promo codes &amp; discounts</li>
                    <li>📂 Browse categories and recommendations</li>
                    <li>🛒 Help you plan your shopping</li>
                </ul>
                <p class="mt-2 text-xs text-gray-400">Ask me anything in English or Arabic!</p>
            </div>
        </div>
    </div>

    {{-- Suggestions --}}
    <div id="suggestions" class="shrink-0 flex flex-wrap gap-2">
        @foreach([
            ["What's on sale today?", '💰'],
            ['Show me fresh vegetables', '🥦'],
            ['What promo codes do you have?', '🎟️'],
            ['أفضل المنتجات المتاحة', '⭐'],
            ['Show me featured products', '🔥'],
        ] as [$text, $emoji])
        <button onclick="sendMessage('{{ $text }}')"
                class="shrink-0 flex items-center gap-1.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-medium px-3 py-1.5 rounded-full transition backdrop-blur">
            <span>{{ $emoji }}</span><span>{{ $text }}</span>
        </button>
        @endforeach
    </div>

    {{-- Input --}}
    <form id="chatForm" onsubmit="handleSubmit(event)" class="shrink-0 flex gap-2">
        <textarea id="messageInput"
                  rows="1"
                  placeholder="Ask about any product, deal, or category…"
                  autocomplete="off"
                  class="flex-1 bg-white/10 backdrop-blur border border-white/20 text-white placeholder-white/40 rounded-xl px-4 py-3 text-sm outline-none focus:border-emerald-400/60 focus:ring-2 focus:ring-emerald-400/20 transition resize-none leading-relaxed"></textarea>
        <button type="submit" id="sendBtn"
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-r from-emerald-500 to-cyan-500 text-white shadow-lg shadow-emerald-500/30 transition hover:brightness-110 disabled:opacity-40 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </button>
    </form>

    {{-- Footer --}}
    <p class="text-center text-[10px] text-white/25 shrink-0">
        Grocery+ · Gemini function calling · SearchProducts · CheckOffers · ListCategories · Real MySQL
    </p>
            </div>
        </main>

<script>
    let isLoading = false;

    const messagesEl  = document.getElementById('messages');
    const inputEl     = document.getElementById('messageInput');
    const sendBtn     = document.getElementById('sendBtn');
    const suggestionsEl = document.getElementById('suggestions');
    const csrf        = document.querySelector('meta[name="csrf-token"]').content;

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function addUserMessage(text) {
        const div = document.createElement('div');
        div.className = 'flex justify-end msg-enter';
        div.innerHTML = `
            <div class="max-w-[80%] bg-grocery-900 text-white rounded-2xl rounded-tr-sm px-4 py-3 text-sm leading-relaxed shadow-md">
                ${escapeHtml(text)}
            </div>`;
        messagesEl.appendChild(div);
        scrollToBottom();
    }

    function addAiMessage() {
        const wrap = document.createElement('div');
        wrap.className = 'flex gap-3 msg-enter';
        wrap.innerHTML = `
            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-grocery-950 text-xs font-black">AI</div>
            <div class="max-w-[85%] bg-white rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm min-w-[80px]">
                <div class="flex gap-1 items-center" id="typing-dots">
                    <span class="w-2 h-2 rounded-full bg-gray-400 dot-1"></span>
                    <span class="w-2 h-2 rounded-full bg-gray-400 dot-2"></span>
                    <span class="w-2 h-2 rounded-full bg-gray-400 dot-3"></span>
                </div>
                <div class="text-sm text-gray-800 leading-relaxed hidden" id="ai-text"></div>
            </div>`;
        messagesEl.appendChild(wrap);
        scrollToBottom();
        return wrap;
    }

    function setLoading(state) {
        isLoading = state;
        sendBtn.disabled = state;
        inputEl.disabled = state;
    }

    async function handleSubmit(e) {
        e.preventDefault();
        const text = inputEl.value.trim();
        if (!text || isLoading) return;
        await sendMessage(text);
    }

    async function sendMessage(text) {
        if (isLoading) return;

        inputEl.value = '';
        suggestionsEl.style.display = 'none';
        addUserMessage(text);
        setLoading(true);

        const wrap   = addAiMessage();
        const dotsEl = wrap.querySelector('#typing-dots');
        const textEl = wrap.querySelector('#ai-text');

        try {
            const res = await fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ message: text }),
            });

            const data = await res.json();

            if (!res.ok || data.error) {
                throw new Error(data.error || `HTTP ${res.status}`);
            }

            dotsEl.classList.add('hidden');
            textEl.classList.remove('hidden');
            textEl.innerHTML = markdownToHtml(data.answer || '(empty response)');

        } catch (err) {
            dotsEl.classList.add('hidden');
            textEl.classList.remove('hidden');
            textEl.innerHTML = `<span class="text-red-500">⚠️ ${escapeHtml(err.message)}</span>`;
        } finally {
            setLoading(false);
            scrollToBottom();
        }
    }

    async function resetChat() {
        await fetch('{{ route("chat.reset") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        });
        messagesEl.innerHTML = '';
        suggestionsEl.style.display = 'flex';
        inputEl.focus();
    }

    // ── Markdown renderer ──────────────────────────────────────────────────────

    function markdownToHtml(text) {
        const lines = text.split('\n');
        const result = [];
        let i = 0;

        while (i < lines.length) {
            const line = lines[i];

            // Table: header row followed by |---| separator
            if (line.trim().startsWith('|') && lines[i + 1] && /^\s*\|[\s\-:|]+\|/.test(lines[i + 1])) {
                const tableLines = [];
                while (i < lines.length && lines[i].trim().startsWith('|')) {
                    tableLines.push(lines[i]);
                    i++;
                }
                result.push(buildTable(tableLines));
                continue;
            }

            if (/^---+$/.test(line.trim())) {
                result.push('<hr class="border-gray-100 my-2">');
                i++; continue;
            }

            if (/^### (.+)/.test(line)) {
                result.push(`<div class="font-bold text-gray-800 text-sm mt-3 mb-1">${inline(line.replace(/^### /, ''))}</div>`);
                i++; continue;
            }
            if (/^## (.+)/.test(line)) {
                result.push(`<div class="font-bold text-gray-800 text-base mt-3 mb-1">${inline(line.replace(/^## /, ''))}</div>`);
                i++; continue;
            }

            if (/^[-*] (.+)/.test(line)) {
                const items = [];
                while (i < lines.length && /^[-*] (.+)/.test(lines[i])) {
                    items.push(`<li class="flex gap-2"><span class="text-emerald-500 shrink-0">•</span><span>${inline(lines[i].replace(/^[-*] /, ''))}</span></li>`);
                    i++;
                }
                result.push(`<ul class="space-y-0.5 my-1">${items.join('')}</ul>`);
                continue;
            }

            if (/^\d+\. (.+)/.test(line)) {
                const items = [];
                while (i < lines.length && /^\d+\. (.+)/.test(lines[i])) {
                    items.push(`<li class="flex gap-2"><span class="text-emerald-500 font-bold shrink-0 text-xs mt-0.5">→</span><span>${inline(lines[i].replace(/^\d+\. /, ''))}</span></li>`);
                    i++;
                }
                result.push(`<ol class="space-y-0.5 my-1">${items.join('')}</ol>`);
                continue;
            }

            if (line.trim() === '') { result.push('<div class="my-1.5"></div>'); i++; continue; }

            result.push(`<p class="leading-relaxed">${inline(line)}</p>`);
            i++;
        }

        return result.join('');
    }

    function inline(text) {
        return text
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`(.*?)`/g, '<code class="bg-gray-100 px-1 rounded text-xs font-mono">$1</code>');
    }

    function buildTable(lines) {
        const rows = lines
            .filter(l => !/^\s*\|[\s\-:|]+\|/.test(l))
            .map(l => l.trim().replace(/^\||\|$/g, '').split('|').map(c => c.trim()));

        if (!rows.length) return '';
        const [header, ...body] = rows;

        const ths = header.map(c =>
            `<th class="px-2 py-1.5 text-left text-xs font-semibold text-gray-600 whitespace-nowrap">${inline(c)}</th>`
        ).join('');

        const trs = body.map((row, ri) => {
            const bg = ri % 2 === 0 ? 'bg-white' : 'bg-gray-50/50';
            return `<tr class="${bg}">${row.map(c => `<td class="px-2 py-1.5 text-xs text-gray-800 whitespace-nowrap">${inline(c)}</td>`).join('')}</tr>`;
        }).join('');

        return `<div class="overflow-x-auto my-2 rounded-lg border border-gray-100">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-50 border-b border-gray-100"><tr>${ths}</tr></thead>
                <tbody>${trs}</tbody>
            </table></div>`;
    }

    function escapeHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function autosizeTextarea(el) {
        el.style.height = '0px';
        el.style.height = Math.min(el.scrollHeight, 160) + 'px';
    }

    inputEl.addEventListener('input', () => autosizeTextarea(inputEl));
    inputEl.addEventListener('keydown', async (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!isLoading) {
                await sendMessage(inputEl.value.trim());
            }
        }
    });

    autosizeTextarea(inputEl);
    inputEl.focus();
</script>

</div>
</div>

</body>
</html>
