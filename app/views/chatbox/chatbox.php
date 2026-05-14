<?php
$messages = $messages ?? [];
$conversations = $conversations ?? [];
$activeConversationId = $activeConversationId ?? null;
?>

<style>
    .chat-shell {
        min-height: calc(100vh - 40px);
    }

    .chat-scroll {
        height: calc(100vh - 260px);
        min-height: 420px;
        overflow-y: auto;
    }

    .chat-scroll::-webkit-scrollbar,
    .history-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .chat-scroll::-webkit-scrollbar-thumb,
    .history-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .message-appear {
        animation: messageAppear .22s ease-out;
    }

    @keyframes messageAppear {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="chat-shell p-4 md:p-6">
    <div class="mx-auto grid max-w-7xl grid-cols-12 gap-5">
        <aside class="col-span-12 lg:col-span-4 xl:col-span-3">
            <div class="h-full rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-emerald-600">NutriAI</p>
                            <h2 class="mt-1 text-xl font-black text-slate-900">Lịch sử chat</h2>
                        </div>
                        
                    </div>
                    <button onclick="startNewConversation()"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-600">
                        <i class="fa fa-pen-to-square"></i>
                        Hội thoại mới
                    </button>
                </div>

                <div class="history-scroll max-h-[calc(100vh-240px)] overflow-y-auto p-3">
                    <?php if (!empty($conversations)): ?>
                        <div class="space-y-2">
                            <?php foreach ($conversations as $conversation): ?>
                                <?php $isActive = (int)$conversation['id'] === (int)$activeConversationId; ?>
                                <?php $isPinned = !empty($conversation['is_pinned']); ?>
                                <div class="group relative rounded-2xl border px-4 py-3 transition <?= $isActive ? 'border-emerald-200 bg-emerald-50' : 'border-transparent hover:border-slate-200 hover:bg-slate-50' ?>">
                                    <div class="flex items-start gap-3">
                                        <a href="index.php?controller=chatbox&action=index&conversation_id=<?= (int)$conversation['id'] ?>"
                                           class="flex min-w-0 flex-1 items-start gap-3">
                                            <div class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl <?= $isActive ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' ?>">
                                                <i class="fa <?= $isPinned ? 'fa-thumbtack' : 'fa-message' ?> text-sm"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold text-slate-800"><?= htmlspecialchars($conversation['title']) ?></p>
                                            <p class="mt-1 text-xs text-slate-400">
                                                <?= (int)$conversation['message_count'] ?> tin nhắn · <?= date('d/m H:i', strtotime($conversation['updated_at'])) ?>
                                            </p>
                                            </div>
                                        </a>
                                        <div class="relative mt-1 shrink-0">
                                            <button type="button"
                                                    id="conversation-menu-button-<?= (int)$conversation['id'] ?>"
                                                    onclick="toggleConversationMenu(<?= (int)$conversation['id'] ?>)"
                                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm transition hover:bg-slate-100 hover:text-slate-800 md:opacity-0 md:group-hover:opacity-100 <?= $isPinned ? 'md:opacity-100' : '' ?>"
                                                    title="Tùy chọn hội thoại">
                                                <i class="fa-solid <?= $isPinned ? 'fa-thumbtack text-amber-600' : 'fa-ellipsis-vertical' ?> text-xs"></i>
                                            </button>
                                            <div id="conversation-menu-<?= (int)$conversation['id'] ?>"
                                                 class="conversation-menu hidden fixed z-[9999] w-48 overflow-hidden rounded-2xl border border-slate-200 bg-white py-1 shadow-2xl shadow-slate-300">
                                                <button type="button"
                                                        onclick="togglePinConversation(<?= (int)$conversation['id'] ?>, <?= $isPinned ? 'false' : 'true' ?>)"
                                                        class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-bold text-slate-700 transition hover:bg-amber-50 hover:text-amber-700">
                                                    <i class="fa-solid fa-thumbtack w-4"></i>
                                                    <?= $isPinned ? 'Bỏ ghim' : 'Ghim' ?>
                                                </button>
                                                <button type="button"
                                                        onclick="deleteConversation(<?= (int)$conversation['id'] ?>)"
                                                        class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-bold text-red-600 transition hover:bg-red-50">
                                                    <i class="fa-solid fa-trash w-4"></i>
                                                    Xóa lịch sử
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="px-4 py-12 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <i class="fa fa-comments"></i>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-slate-500">Chưa có lịch sử chat</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-400">Lịch sử sẽ được lưu khi bạn bắt đầu gửi tin nhắn hoặc tải ảnh cho AI phân tích.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <section class="col-span-12 lg:col-span-8 xl:col-span-9">
            <div class="flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 bg-white p-5">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-100">
                                <i class="fa fa-robot text-xl"></i>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h1 class="text-2xl font-black text-slate-900">Trợ lý dinh dưỡng AI</h1>
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">OpenAI</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">Hỏi về calo, thực đơn, ảnh món ăn hoặc thông tin liên quan đến hồ sơ sức khỏe.</p>
                            </div>
                        </div>
                    </div>
                </header>

                <main id="chat-content" class="chat-scroll space-y-5 bg-slate-50/70 p-5 md:p-7">
                    <?php if (empty($messages)): ?>
                        <div class="message-appear flex justify-start">
                            <div class="max-w-[88%] rounded-3xl rounded-tl-lg border border-slate-200 bg-white px-5 py-4 text-slate-700 shadow-sm md:max-w-[72%]">
                                <p class="font-bold text-slate-900">Chào bạn, mình là NutriAI.</p>
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">Chào bạn, NutriAI có thể giúp gì cho bạn?</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($messages as $m): ?>
                        <?php $isUser = ($m['role'] ?? '') === 'user'; ?>
                        <div class="message-appear flex <?= $isUser ? 'justify-end' : 'justify-start' ?>">
                            <div class="max-w-[88%] space-y-2 md:max-w-[72%] <?= $isUser ? 'items-end' : 'items-start' ?>">
                                <?php if (!empty($m['image'])): ?>
                                    <img src="<?= htmlspecialchars($m['image']) ?>" class="max-h-72 w-full rounded-3xl object-cover shadow-sm border border-slate-200" alt="Ảnh đã gửi">
                                <?php endif; ?>
                                <?php if (!empty($m['message']) && !str_starts_with($m['message'], '[')): ?>
                                    <?php
                                        $displayMessage = preg_replace('/\*\*(.*?)\*\*/u', '$1', $m['message']);
                                        $displayMessage = preg_replace('/__(.*?)__/u', '$1', $displayMessage);
                                    ?>
                                    <div class="<?= $isUser ? 'rounded-tr-lg bg-emerald-600 text-white' : 'rounded-tl-lg border border-slate-200 bg-white text-slate-700' ?> rounded-3xl px-5 py-4 shadow-sm">
                                        <div class="whitespace-pre-line text-sm leading-relaxed"><?= htmlspecialchars($displayMessage) ?></div>
                                    </div>
                                <?php elseif (!empty($m['image']) && $isUser): ?>
                                    <div class="rounded-3xl rounded-tr-lg bg-emerald-600 px-5 py-4 text-sm font-semibold text-white shadow-sm">Đã gửi ảnh để AI phân tích</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </main>

                <div id="nutrition-panel" class="hidden border-t border-slate-100 bg-white px-5 py-4"></div>

                <footer class="border-t border-slate-100 bg-white p-4">
                    <div id="preview-area" class="mb-3 hidden"></div>
                    <div class="flex items-end gap-3">
                        <label class="flex h-[52px] w-[52px] shrink-0 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                               title="Chọn ảnh món ăn">
                            <i class="fa fa-image"></i>
                            <input type="file" id="imageInput" class="hidden" accept="image/*" onchange="handleImage(this)">
                        </label>
                        <textarea id="msg"
                                  rows="1"
                                  class="max-h-36 min-h-[52px] flex-1 resize-none rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                  placeholder="Nhập câu hỏi cho AI..."></textarea>
                        <button onclick="sendMsg()"
                                class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-100 transition hover:bg-emerald-700 active:scale-95">
                            <i class="fa fa-paper-plane"></i>
                        </button>
                    </div>
                </footer>
            </div>
        </section>
    </div>
</div>

<script>
const chat = document.getElementById('chat-content');
const input = document.getElementById('msg');
let selectedImageFile = null;
let selectedImagePreviewUrl = '';

function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[char]));
}

function cleanChatText(value) {
    return String(value ?? '')
        .replace(/\*\*(.*?)\*\*/g, '$1')
        .replace(/__(.*?)__/g, '$1')
        .replace(/^\s{0,3}#{1,6}\s*/gm, '')
        .replace(/\n{3,}/g, '\n\n');
}

function scrollChat() {
    chat.scrollTop = chat.scrollHeight;
}

function addMessage(role, message, image = '') {
    const isUser = role === 'user';
    const imageHtml = image ? `<img src="${escapeHTML(image)}" class="max-h-72 w-full rounded-3xl object-cover shadow-sm border border-slate-200" alt="Ảnh đã gửi">` : '';
    const displayMessage = cleanChatText(message);
    const textHtml = message ? `
        <div class="${isUser ? 'rounded-tr-lg bg-emerald-600 text-white' : 'rounded-tl-lg border border-slate-200 bg-white text-slate-700'} rounded-3xl px-5 py-4 shadow-sm">
            <div class="whitespace-pre-line text-sm leading-relaxed">${escapeHTML(displayMessage)}</div>
        </div>` : '';
    chat.insertAdjacentHTML('beforeend', `
        <div class="message-appear flex ${isUser ? 'justify-end' : 'justify-start'}">
            <div class="max-w-[88%] space-y-2 md:max-w-[72%]">
                ${imageHtml}${textHtml}
            </div>
        </div>
    `);
    scrollChat();
}

function showTyping() {
    if (document.getElementById('typing-indicator')) return;
    chat.insertAdjacentHTML('beforeend', `
        <div id="typing-indicator" class="message-appear flex justify-start">
            <div class="rounded-3xl rounded-tl-lg border border-slate-200 bg-white px-5 py-4 text-slate-500 shadow-sm">
                <span class="inline-flex items-center gap-2 text-sm font-semibold">
                    <span class="h-2 w-2 animate-bounce rounded-full bg-emerald-500"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-emerald-500 [animation-delay:120ms]"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-emerald-500 [animation-delay:240ms]"></span>
                    AI đang suy nghĩ...
                </span>
            </div>
        </div>
    `);
    scrollChat();
}

function hideTyping() {
    document.getElementById('typing-indicator')?.remove();
}

function setSendingState(isSending) {
    input.disabled = isSending;
    document.querySelector('button[onclick="sendMsg()"]').disabled = isSending;
}

function maybeRefreshHistory(data) {
    if (data && data.conversation_id && !<?= $activeConversationId ? 'true' : 'false' ?>) {
        setTimeout(() => {
            window.location.href = `index.php?controller=chatbox&action=index&conversation_id=${data.conversation_id}`;
        }, 500);
    }
}

function sendMsg() {
    const text = input.value.trim();
    if (!text && !selectedImageFile) return;

    const imageToSend = selectedImageFile;
    const imagePreview = selectedImagePreviewUrl;
    const userText = text || 'Đã gửi ảnh món ăn để AI phân tích';

    addMessage('user', userText, imagePreview);
    input.value = '';
    clearSelectedImage();
    clearNutritionCard();
    showTyping();
    setSendingState(true);

    const request = imageToSend
        ? sendImageRequest(imageToSend, text)
        : fetch('index.php?controller=chatbox&action=send', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({message: text})
        });

    request
    .then(res => res.json())
    .then(data => {
        hideTyping();
        setSendingState(false);
        if (data.error) {
            addMessage('ai', data.error);
            return;
        }
        addMessage('ai', data.reply || 'AI chưa có phản hồi.');
        if (data.nutrition) renderNutritionCard(data.nutrition);
        maybeRefreshHistory(data);
    })
    .catch(() => {
        hideTyping();
        setSendingState(false);
        addMessage('ai', 'Lỗi kết nối. Vui lòng thử lại.');
    });
}

function handleImage(fileInput) {
    const file = fileInput.files[0];
    if (!file) return;
    if (selectedImagePreviewUrl) {
        URL.revokeObjectURL(selectedImagePreviewUrl);
    }
    selectedImageFile = file;
    selectedImagePreviewUrl = URL.createObjectURL(file);
    renderImagePreview();
    fileInput.value = '';
}

function renderImagePreview() {
    const preview = document.getElementById('preview-area');
    if (!selectedImageFile) {
        preview.innerHTML = '';
        preview.classList.add('hidden');
        return;
    }

    preview.innerHTML = `
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-3">
            <img src="${escapeHTML(selectedImagePreviewUrl)}" class="h-20 w-20 rounded-xl object-cover border border-white shadow-sm" alt="Ảnh xem trước">
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-slate-800">${escapeHTML(selectedImageFile.name)}</p>
                <p class="mt-1 text-xs text-slate-500">
                    Hãy nhập tin nhắn nếu muốn, rồi bấm gửi.
                </p>
            </div>
            <button onclick="clearSelectedImage()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-slate-500 transition hover:text-red-500">
                <i class="fa fa-xmark"></i>
            </button>
        </div>
    `;
    preview.classList.remove('hidden');
}

function clearSelectedImage() {
    if (selectedImagePreviewUrl) {
        URL.revokeObjectURL(selectedImagePreviewUrl);
    }
    selectedImageFile = null;
    selectedImagePreviewUrl = '';
    renderImagePreview();
}

function sendImageRequest(file, message) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('message', message || '');
    return fetch('index.php?controller=chatbox&action=upload', {method: 'POST', body: formData});
}

function renderNutritionCard(nutrition) {
    const panel = document.getElementById('nutrition-panel');
    if (!nutrition || !nutrition.totals) {
        clearNutritionCard();
        return;
    }

    const rows = (nutrition.items || []).map(item => `
        <div class="flex items-center justify-between gap-4 rounded-2xl bg-white px-4 py-3 text-sm">
            <div>
                <p class="font-bold text-slate-800">${escapeHTML(item.name)}</p>
                <p class="text-xs text-slate-400">${escapeHTML(item.gram)}g</p>
            </div>
            <b class="text-emerald-600">${escapeHTML(item.calo)} kcal</b>
        </div>
    `).join('');

    panel.innerHTML = `
        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-4">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-black text-emerald-900">Bảng dinh dưỡng AI phân tích</h3>
                <button onclick="clearNutritionCard()" class="text-sm font-bold text-slate-500 hover:text-slate-900">Đóng</button>
            </div>
            <div class="grid gap-2 md:grid-cols-2">${rows}</div>
            <div class="mt-3 grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
                <div class="rounded-2xl bg-white p-3"><b>${escapeHTML(nutrition.totals.calo)}</b><p class="text-xs text-slate-400">kcal</p></div>
                <div class="rounded-2xl bg-white p-3"><b>${escapeHTML(nutrition.totals.protein)}g</b><p class="text-xs text-slate-400">protein</p></div>
                <div class="rounded-2xl bg-white p-3"><b>${escapeHTML(nutrition.totals.carb)}g</b><p class="text-xs text-slate-400">carb</p></div>
                <div class="rounded-2xl bg-white p-3"><b>${escapeHTML(nutrition.totals.fat)}g</b><p class="text-xs text-slate-400">fat</p></div>
            </div>
        </div>
    `;
    panel.classList.remove('hidden');
}

function clearNutritionCard() {
    const panel = document.getElementById('nutrition-panel');
    panel.innerHTML = '';
    panel.classList.add('hidden');
}

function startNewConversation() {
    fetch('index.php?controller=chatbox&action=reset', {method: 'POST'})
        .then(() => {
            window.location.href = 'index.php?controller=chatbox&action=index';
        });
}

function closeConversationMenus(exceptId = null) {
    document.querySelectorAll('.conversation-menu').forEach(menu => {
        if (exceptId && menu.id === `conversation-menu-${exceptId}`) return;
        menu.classList.add('hidden');
    });
}

function toggleConversationMenu(conversationId) {
    const menu = document.getElementById(`conversation-menu-${conversationId}`);
    const button = document.getElementById(`conversation-menu-button-${conversationId}`);
    if (!menu) return;
    const isHidden = menu.classList.contains('hidden');
    closeConversationMenus(conversationId);
    menu.classList.toggle('hidden', !isHidden);

    if (isHidden && button) {
        const rect = button.getBoundingClientRect();
        const menuWidth = 192;
        const left = Math.max(12, Math.min(window.innerWidth - menuWidth - 12, rect.right - menuWidth));
        menu.style.left = `${left}px`;
        menu.style.top = `${rect.bottom + 8}px`;
    }
}

function togglePinConversation(conversationId, pinned) {
    closeConversationMenus();
    fetch('index.php?controller=chatbox&action=pinConversation', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            conversation_id: conversationId,
            pinned: pinned
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            notify(data.error || 'Không thể cập nhật ghim hội thoại', 'error');
        }
    })
    .catch(() => notify('Lỗi kết nối khi cập nhật ghim hội thoại', 'error'));
}

function deleteConversation(conversationId) {
    closeConversationMenus();
    if (!confirm('Xóa lịch sử hội thoại này? Hành động này không thể hoàn tác.')) {
        return;
    }

    fetch('index.php?controller=chatbox&action=deleteConversation', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({conversation_id: conversationId})
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            notify(data.error || 'Không thể xóa lịch sử chat', 'error');
            return;
        }
        const activeConversationId = <?= (int)($activeConversationId ?? 0) ?>;
        window.location.href = activeConversationId === conversationId
            ? 'index.php?controller=chatbox&action=index'
            : window.location.href;
    })
    .catch(() => notify('Lỗi kết nối khi xóa lịch sử chat', 'error'));
}

document.addEventListener('click', event => {
    if (!event.target.closest('.conversation-menu') && !event.target.closest('button[title="Tùy chọn hội thoại"]')) {
        closeConversationMenus();
    }
});

input.addEventListener('keydown', event => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMsg();
    }
});

input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 144) + 'px';
});

scrollChat();
</script>

