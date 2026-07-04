/**
 * chat.js
 * ──────────────────────────────────────────────────────────
 * MENU  : Global (semua halaman — widget AI chat ARSY)
 * FILE  : resources/js/chat.js
 * SCOPE : Aktif jika #earsipchat-btn ada di DOM
 */
(() => {
    "use strict";

    /* ── Guard: only run when widget is in DOM ── */
    const widget = document.getElementById("chat-widget");
    if (!widget) return;

    const CSRF =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "";
    const panel = document.getElementById("chat-panel");
    const toggleBtn = document.getElementById("chat-toggle");
    const closeBtn = document.getElementById("chat-close");
    const clearBtn = document.getElementById("chat-clear");
    const messagesEl = document.getElementById("chat-messages");
    const inputEl = document.getElementById("chat-input");
    const sendBtn = document.getElementById("chat-send");
    const suggestEl = document.getElementById("chat-suggestions");
    const iconOpen = document.getElementById("chat-icon-open");
    const iconClose = document.getElementById("chat-icon-close");

    let history = []; // [{role, content}] — in-memory, survives nav via sessionStorage
    let isStreaming = false;
    let isOpen = false;

    const STORAGE_KEY = "earsip_arsy_history";

    /* ════════════════════════════════
       SESSION HISTORY
    ════════════════════════════════ */
    function loadHistory() {
        try {
            history = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || "[]");
        } catch {
            history = [];
        }
    }

    function saveHistory() {
        try {
            sessionStorage.setItem(
                STORAGE_KEY,
                JSON.stringify(history.slice(-20))
            );
        } catch {}
    }

    /* ════════════════════════════════
       PANEL OPEN / CLOSE
    ════════════════════════════════ */
    function openPanel() {
        isOpen = true;
        panel.classList.remove("chat-panel-hidden");
        panel.classList.add("chat-panel-visible");
        iconOpen.classList.add("d-none");
        iconClose.classList.remove("d-none");
        toggleBtn.classList.add("chat-toggle-active");
        scrollToBottom();
        setTimeout(() => inputEl?.focus(), 120);
    }

    function closePanel() {
        isOpen = false;
        panel.classList.remove("chat-panel-visible");
        panel.classList.add("chat-panel-hidden");
        iconOpen.classList.remove("d-none");
        iconClose.classList.add("d-none");
        toggleBtn.classList.remove("chat-toggle-active");
    }

    toggleBtn?.addEventListener("click", () =>
        isOpen ? closePanel() : openPanel()
    );
    closeBtn?.addEventListener("click", closePanel);

    /* ════════════════════════════════
       RENDER HELPERS
    ════════════════════════════════ */
    function escapeHtml(str) {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function formatMarkdown(text) {
        return escapeHtml(text)
            .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
            .replace(/\*(.*?)\*/g, "<em>$1</em>")
            .replace(/`([^`]+)`/g, "<code>$1</code>")
            .replace(/^(\d+)\.\s(.+)$/gm, "<li>$2</li>")
            .replace(/^[-•]\s(.+)$/gm, "<li>$1</li>")
            .replace(/(<li>[\s\S]+?<\/li>)(?=\s*(?:<li>|$))/g, "<ul>$&</ul>")
            .replace(/<\/ul>\s*<ul>/g, "") // merge consecutive lists
            .replace(/\n{2,}/g, "<br><br>")
            .replace(/\n/g, "<br>");
    }

    function scrollToBottom() {
        if (messagesEl) messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function hideSuggestions() {
        if (suggestEl) suggestEl.style.display = "none";
    }

    /* Create a message wrapper and append to messages area.
       Returns the bubble element (for streaming text injection). */
    function appendMessage(role) {
        hideSuggestions();
        const wrap = document.createElement("div");
        wrap.className = `chat-msg chat-msg-${role}`;

        const bubble = document.createElement("div");
        bubble.className = "chat-bubble";
        wrap.appendChild(bubble);

        messagesEl.appendChild(wrap);
        scrollToBottom();
        return bubble;
    }

    function showTyping() {
        const wrap = document.createElement("div");
        wrap.id = "chat-typing";
        wrap.className = "chat-msg chat-msg-bot";
        wrap.innerHTML = `<div class="chat-bubble chat-typing-bubble">
            <span></span><span></span><span></span>
        </div>`;
        messagesEl.appendChild(wrap);
        scrollToBottom();
    }

    function hideTyping() {
        document.getElementById("chat-typing")?.remove();
    }

    /* ════════════════════════════════
       SEND MESSAGE
    ════════════════════════════════ */
    async function sendMessage(text) {
        text = text?.trim();
        if (!text || isStreaming) return;

        isStreaming = true;
        inputEl.value = "";
        inputEl.style.height = "auto";
        sendBtn.disabled = true;
        inputEl.disabled = true;

        /* render user bubble */
        const userBubble = appendMessage("user");
        userBubble.innerHTML = escapeHtml(text);

        /* add to history */
        history.push({ role: "user", content: text });
        saveHistory();

        showTyping();

        try {
            const res = await fetch("/chat/ask", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": CSRF,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    message: text,
                    history: history.slice(-8),
                    page: window.location.pathname,
                }),
            });

            hideTyping();

            if (!res.ok) throw new Error("HTTP " + res.status);

            const botBubble = appendMessage("bot");
            const contentType = res.headers.get("content-type") || "";

            let fullReply = "";

            if (contentType.includes("text/event-stream")) {
                /* ── STREAMING ── */
                const reader = res.body.getReader();
                const decoder = new TextDecoder();
                let buffer = "";

                botBubble.innerHTML = '<span class="chat-cursor">▋</span>';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split("\n");
                    buffer = lines.pop(); // keep incomplete line

                    for (const line of lines) {
                        if (!line.startsWith("data: ")) continue;
                        const data = line.slice(6).trim();
                        if (data === "[DONE]") continue;
                        try {
                            const json = JSON.parse(data);
                            const token =
                                json.choices?.[0]?.delta?.content || "";
                            fullReply += token;
                            botBubble.innerHTML =
                                formatMarkdown(fullReply) +
                                '<span class="chat-cursor">▋</span>';
                            scrollToBottom();
                        } catch {}
                    }
                }

                botBubble.innerHTML = formatMarkdown(fullReply);
            } else {
                /* ── FALLBACK: regular JSON ── */
                const data = await res.json();
                fullReply = data.reply || "Maaf, tidak ada respons.";
                botBubble.innerHTML = formatMarkdown(fullReply);
            }

            scrollToBottom();

            if (fullReply) {
                history.push({ role: "assistant", content: fullReply });
                saveHistory();
            }
        } catch (err) {
            hideTyping();
            const errBubble = appendMessage("bot");
            errBubble.innerHTML =
                "⚠️ Maaf, saya sedang tidak bisa merespons. Silakan coba lagi.";
            console.error("[Arsy]", err);
        } finally {
            isStreaming = false;
            inputEl.disabled = false;
            sendBtn.disabled = !inputEl.value.trim();
            inputEl.focus();
            scrollToBottom();
        }
    }

    /* ════════════════════════════════
       INPUT EVENTS
    ════════════════════════════════ */
    inputEl?.addEventListener("input", () => {
        sendBtn.disabled = !inputEl.value.trim() || isStreaming;
        // auto-resize
        inputEl.style.height = "auto";
        inputEl.style.height = Math.min(inputEl.scrollHeight, 110) + "px";
    });

    inputEl?.addEventListener("keydown", (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage(inputEl.value);
        }
    });

    sendBtn?.addEventListener("click", () => sendMessage(inputEl.value));

    /* ════════════════════════════════
       CLEAR HISTORY
    ════════════════════════════════ */
    clearBtn?.addEventListener("click", () => {
        history = [];
        saveHistory();

        // Keep only the welcome message
        const welcome = messagesEl.querySelector(".chat-msg");
        messagesEl.innerHTML = "";
        if (welcome) messagesEl.appendChild(welcome);

        if (suggestEl) suggestEl.style.display = "";
    });

    /* ════════════════════════════════
       SUGGESTION CHIPS
    ════════════════════════════════ */
    document.querySelectorAll(".chat-chip").forEach((chip) => {
        chip.addEventListener("click", () => {
            if (isStreaming) return;
            sendMessage(chip.dataset.text);
        });
    });

    /* ════════════════════════════════
       INIT
    ════════════════════════════════ */
    loadHistory();
})();
