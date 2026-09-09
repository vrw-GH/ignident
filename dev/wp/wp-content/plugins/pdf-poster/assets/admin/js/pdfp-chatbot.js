(function () {
    'use strict';

    const kb          = pdfpChatbot.kb;
    const suggestions = pdfpChatbot.suggestions;
    const docsUrl     = pdfpChatbot.docsUrl;
    const pricingUrl  = pdfpChatbot.pricingUrl || 'https://bplugins.com/products/pdf-poster/pricing';
    const pluginName  = pdfpChatbot.pluginName;
    const STORAGE_KEY = 'pdfp_chatbot_open';

    // ── Fuse.js setup ─────────────────────────────────────────────────────────
    // ignoreLocation + a higher threshold make matching forgiving, so a keyword
    // can appear anywhere in the question/tags/answer and still match.
    const fuse = new Fuse(kb, {
        keys: [
            { name: 'q',    weight: 0.6 },
            { name: 'tags', weight: 0.35 },
            { name: 'a',    weight: 0.05 },
        ],
        threshold: 0.5,
        distance: 400,
        ignoreLocation: true,
        minMatchCharLength: 2,
        includeScore: true,
    });

    // Common filler words to drop before keyword matching.
    const STOPWORDS = {
        the:1, a:1, an:1, how:1, do:1, does:1, i:1, my:1, to:1, is:1, are:1,
        can:1, of:1, in:1, on:1, at:1, what:1, where:1, when:1, which:1, it:1,
        for:1, with:1, and:1, or:1, this:1, that:1, you:1, your:1, me:1, we:1,
        please:1, help:1, pdf:1, poster:1, want:1, need:1, get:1, set:1, use:1
    };

    // A per-word match only "counts" if it's reasonably strong. Fuse can return
    // weak combined scores above the threshold, so we gate on this to avoid
    // gibberish queries accidentally matching an unrelated answer.
    const WORD_MATCH_OK = 0.5;

    // Find the best-matching KB item for a free-form question.
    // 1) Try the whole phrase. 2) If weak, score each keyword individually and
    //    pick the item that matches the most keywords with the best combined
    //    score — so users don't need to type the exact question.
    function findBestMatch(query) {
        const direct = fuse.search(query);
        if (direct.length && direct[0].score <= 0.45) {
            return direct[0].item;
        }

        const words = query
            .toLowerCase()
            .replace(/[^a-z0-9\s]/g, ' ')
            .split(/\s+/)
            .filter(function (w) { return w.length > 2 && !STOPWORDS[w]; });

        if (!words.length) {
            return (direct.length && direct[0].score <= 0.45) ? direct[0].item : null;
        }

        const agg = {}; // refIndex -> { item, total, hits }
        words.forEach(function (w) {
            fuse.search(w).forEach(function (r) {
                if (r.score > WORD_MATCH_OK) return; // ignore weak/coincidental hits
                const k = r.refIndex;
                if (!agg[k]) agg[k] = { item: r.item, total: 0, hits: 0 };
                agg[k].total += (1 - r.score); // higher = better
                agg[k].hits  += 1;
            });
        });

        let best = null;
        Object.keys(agg).forEach(function (k) {
            const v = agg[k];
            // Reward items that match several keywords, not just one.
            const rank = v.total + v.hits * 0.2;
            if (!best || rank > best.rank) best = { item: v.item, rank: rank, hits: v.hits };
        });

        if (best) {
            if (best.hits >= 2) return best.item;          // matched multiple keywords
            if (best.hits === 1 && best.rank >= 0.85) return best.item; // one strong keyword
        }

        // Last resort: take the direct top hit only if it's clearly plausible.
        return (direct.length && direct[0].score <= 0.45) ? direct[0].item : null;
    }

    // ── DOM refs ──────────────────────────────────────────────────────────────
    const wrapper     = document.getElementById('pdfp-chatbot-wrapper');
    const chatWindow  = document.getElementById('pdfp-chatbot-window');
    const closeBtn    = document.getElementById('pdfp-chatbot-close');
    const input       = document.getElementById('pdfp-chatbot-input');
    const sendBtn     = document.getElementById('pdfp-chatbot-send');
    const messages    = document.getElementById('pdfp-chatbot-messages');
    const suggestBox  = document.getElementById('pdfp-chatbot-suggestions');
    const toggleBtn   = document.getElementById('pdfp-chatbot-toggle');

    if (!wrapper) return;

    let isOpen = false;

    // ── Helpers ───────────────────────────────────────────────────────────────

    function addMessage(html, sender, showFeedback = false) {
        const div = document.createElement('div');
        div.className = 'pdfp-msg pdfp-msg--' + sender;
        div.innerHTML = html;

        if (showFeedback) {
            const fb = document.createElement('div');
            fb.className = 'pdfp-feedback';
            fb.innerHTML = '<span>Was this helpful?</span>'
                + '<button class="pdfp-fb-btn pdfp-fb-yes" title="Yes">👍</button>'
                + '<button class="pdfp-fb-btn pdfp-fb-no" title="No">👎</button>';

            fb.querySelector('.pdfp-fb-yes').addEventListener('click', function () {
                saveFeedback(html, 'yes');
                fb.innerHTML = '<span class="pdfp-fb-thanks">Thanks! 😊</span>';
            });
            fb.querySelector('.pdfp-fb-no').addEventListener('click', function () {
                saveFeedback(html, 'no');
                fb.innerHTML = '<span class="pdfp-fb-thanks">Thanks. We\'ll improve this answer.</span>';
            });

            div.appendChild(fb);
        }

        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        return div;
    }

    function showTyping() {
        const div = document.createElement('div');
        div.className = 'pdfp-msg pdfp-msg--bot pdfp-typing';
        div.innerHTML = '<span></span><span></span><span></span>';
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        return div;
    }

    function getBotReply(query) {
        if (!query.trim()) return { html: '', pro: false };

        const item = findBestMatch(query);

        if (!item) {
            return {
                html: 'Sorry, I couldn\'t find a clear answer for that. '
                    + 'Please check the <a href="' + docsUrl + '" target="_blank">full documentation</a> '
                    + 'or visit the <a href="https://wordpress.org/support/plugin/pdf-poster/" target="_blank">support forum</a>.',
                pro: false
            };
        }

        let html = item.a;

        // Pro feature → always hand over the pricing link.
        if (item.pro) {
            html += '<div class="pdfp-pro-cta">💎 This is a <strong>Pro</strong> feature. '
                + '<a href="' + pricingUrl + '" target="_blank" rel="noopener">Upgrade to PDF Poster Pro →</a>'
                + '</div>';
        }

        return { html: html, pro: !!item.pro };
    }

    function handleSend(query) {
        const q = (query || input.value).trim();
        if (!q) return;

        // Hide suggestions after first message
        suggestBox.style.display = 'none';

        addMessage(escapeHtml(q), 'user');
        input.value = '';

        const typingEl = showTyping();

        setTimeout(function () {
            typingEl.remove();
            const reply = getBotReply(q);
            addMessage(reply.html, 'bot', true);
        }, 500);
    }

    // FIXED: Removed duplicate function definition or unused properties if any
    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    function saveFeedback(answer, value) {
        try {
            const existing = JSON.parse(localStorage.getItem('pdfp_chatbot_feedback') || '[]');
            existing.push({ answer: answer.substring(0, 80), value: value, time: Date.now() });
            localStorage.setItem('pdfp_chatbot_feedback', JSON.stringify(existing.slice(-50))); // keep last 50
        } catch (e) {}
    }

    function openChat() {
        isOpen = true;
        chatWindow.classList.add('pdfp-open');
        toggleBtn.classList.add('pdfp-active');
        try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}

        if (messages.childElementCount === 0) {
            addMessage(
                'Hi! 👋 I\'m your <strong>' + pluginName + '</strong> assistant.<br>Ask me anything about the plugin.',
                'bot'
            );
            renderSuggestions();
        }
        setTimeout(function () { input.focus(); }, 300);
    }

    function closeChat() {
        isOpen = false;
        chatWindow.classList.remove('pdfp-open');
        toggleBtn.classList.remove('pdfp-active');
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    }

    function renderSuggestions() {
        suggestBox.innerHTML = '';
        suggestions.forEach(function (text) {
            const chip = document.createElement('button');
            chip.className = 'pdfp-suggestion-chip';
            chip.textContent = text;
            chip.addEventListener('click', function () {
                handleSend(text);
            });
            suggestBox.appendChild(chip);
        });
        suggestBox.style.display = 'flex';
    }

    // ── Events ────────────────────────────────────────────────────────────────
    toggleBtn.addEventListener('click', function () {
        isOpen ? closeChat() : openChat();
    });

    closeBtn.addEventListener('click', closeChat);

    sendBtn.addEventListener('click', function () { handleSend(); });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') handleSend();
    });

    // ── Restore state ─────────────────────────────────────────────────────────
    try {
        if (localStorage.getItem(STORAGE_KEY) === '1') {
            openChat();
        }
    } catch (e) {}

}());
