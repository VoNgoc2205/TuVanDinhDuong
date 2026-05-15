<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$record = $record ?? [];
$isEditing = ($_GET['mode'] ?? '') === 'edit';
$recordImage = '';
if (!empty($record['hinh_anh'])) {
    $recordImage = strpos($record['hinh_anh'], 'public/') === 0
        ? $record['hinh_anh']
        : 'public/uploads/avatar/' . $record['hinh_anh'];
}

$displayContent = trim((string)($record['gia_tri'] ?? ''));
if ($displayContent !== '') {
    $displayContent = preg_replace('/(?:\r?\n){2,}V[aă]n b[aả]n OCR:.*$/isu', '', $displayContent);
    $displayContent = preg_replace('/^V[aă]n b[aả]n OCR:.*$/isu', '', $displayContent);

    $lines = preg_split('/\r?\n/u', trim($displayContent));
    $filteredLines = [];
    $seenValues = [];

    foreach ($lines as $line) {
        $line = trim((string)$line);
        if ($line === '') {
            continue;
        }

        $value = $line;
        $colonPos = strpos($line, ':');
        if ($colonPos !== false) {
            $value = trim(substr($line, $colonPos + 1));
        }

        $dedupeKey = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value)), 'UTF-8');
        if ($dedupeKey !== '' && isset($seenValues[$dedupeKey])) {
            continue;
        }

        if ($dedupeKey !== '') {
            $seenValues[$dedupeKey] = true;
        }
        $filteredLines[] = $line;
    }

    $displayContent = implode("\n\n", $filteredLines);
}
?>

<div class="space-y-8">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <p class="text-slate-400 uppercase tracking-[3px] font-black text-xs mb-2">Hồ sơ bệnh án</p>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Chi tiết hồ sơ bệnh án</h1>
            <p class="text-slate-500 mt-2">Xem chi tiết, sửa hoặc quay lại lịch sử hồ sơ bệnh án.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="index.php?controller=benh_an&action=index" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-slate-700 font-bold hover:bg-slate-50 transition">
                <i class="fa fa-arrow-left"></i>
                Trở về lịch sử
            </a>
            
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-8 bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 self-start">
            <?php if ($isEditing): ?>
                <form id="benhAnEditForm" action="index.php?controller=benh_an&action=update" method="post">
                    <input type="hidden" name="id" value="<?= intval($record['id'] ?? 0) ?>">
            <?php endif; ?>
            

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="rounded-[1.5rem] bg-slate-50 p-5 border border-slate-100">
                    <p class="text-slate-500 text-[11px] uppercase tracking-[3px] mb-2">Tên mục</p>
                    <?php if ($isEditing): ?>
                        <input name="ten_muc" type="text" value="<?= htmlspecialchars($record['ten_muc'] ?? 'Hồ sơ bệnh án') ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 font-bold outline-none focus:border-emerald-500" required>
                    <?php else: ?>
                        <p class="text-slate-900 font-bold text-lg"><?= htmlspecialchars($record['ten_muc'] ?? 'Hồ sơ bệnh án') ?></p>
                    <?php endif; ?>
                </div>
                <div class="rounded-[1.5rem] bg-slate-50 p-5 border border-slate-100">
                    <p class="text-slate-500 text-[11px] uppercase tracking-[3px] mb-2">Thời gian cập nhật</p>
                    <p class="text-slate-900 font-bold text-lg"><?= !empty($record['ngay_cap_nhat']) ? date('d/m/Y H:i', strtotime($record['ngay_cap_nhat'])) : 'Chưa cập nhật' ?></p>
                </div>
            </div>

            <?php if (false && $recordImage && file_exists($recordImage)): ?>
                <button type="button"
                    class="group relative mb-8 block w-full overflow-hidden rounded-[2.5rem] border border-slate-100 bg-slate-50 text-left shadow-sm cursor-zoom-in"
                    onclick="openBenhAnImageModal('<?= htmlspecialchars($recordImage, ENT_QUOTES, 'UTF-8') ?>')">
                    <img src="<?= htmlspecialchars($recordImage) ?>" alt="Ảnh hồ sơ bệnh án" class="w-full h-auto max-h-[420px] object-cover transition duration-300 group-hover:scale-[1.01]">
                    <span class="absolute bottom-4 right-4 inline-flex items-center gap-2 rounded-2xl bg-slate-900/85 px-4 py-2 text-xs font-bold text-white shadow-lg backdrop-blur">
                        <i class="fa fa-up-right-and-down-left-from-center"></i>
                        Xem ảnh đầy đủ
                    </span>
                </button>
            <?php endif; ?>

            <div class="rounded-[1.75rem] bg-slate-50 p-6 border border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-slate-500 text-[11px] uppercase tracking-[3px]">Nội dung đọc được từ hình ảnh và kết quả AI phân tích</p>
                    <span class="text-slate-400 text-xs uppercase tracking-[2px]">ID: <?= intval($record['id'] ?? 0) ?></span>
                </div>
                <?php if ($isEditing): ?>
                    <textarea name="gia_tri" rows="14" class="w-full rounded-[2rem] border border-slate-200 bg-white px-5 py-4 text-slate-800 leading-relaxed outline-none focus:border-emerald-500"><?= htmlspecialchars($displayContent !== '' ? $displayContent : ($record['gia_tri'] ?? '')) ?></textarea>
                <?php else: ?>
                    <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed">
                        <?= nl2br(htmlspecialchars($displayContent !== '' ? $displayContent : 'Không có dữ liệu phân tích')) ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($isEditing): ?>
                </form>
            <?php endif; ?>
        </div>

        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                <p class="text-slate-400 text-[11px] uppercase tracking-[3px] mb-4">Hành động </p>
                <div class="space-y-3">
                    <?php if ($isEditing): ?>
                        <button type="submit" form="benhAnEditForm" class="block w-full rounded-2xl bg-emerald-600 px-5 py-4 text-white font-bold text-center hover:bg-emerald-700 transition">Lưu thay đổi</button>
                        <a href="index.php?controller=benh_an&action=view&id=<?= intval($record['id']) ?>" class="block rounded-2xl border border-slate-200 bg-white px-5 py-4 text-slate-700 font-bold text-center hover:bg-slate-50 transition">Hủy</a>
                    <?php else: ?>
                        <a href="index.php?controller=benh_an&action=view&id=<?= intval($record['id']) ?>&mode=edit" class="block rounded-2xl bg-emerald-600 px-5 py-4 text-white font-bold text-center hover:bg-emerald-700 transition">Chỉnh sửa</a>
                        <a href="index.php?controller=benh_an&action=delete&id=<?= intval($record['id']) ?>" data-confirm="Bạn có chắc muốn xóa hồ sơ này?" data-confirm-title="Xóa hồ sơ" data-confirm-ok="Xóa" class="block rounded-2xl bg-red-600 px-5 py-4 text-white font-bold text-center hover:bg-red-700 transition">Xóa hồ sơ</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Bạn có thể lưu lại những hồ sơ quan trọng để tham khảo, phân tích và đối chiếu với kết quả dinh dưỡng.</p>
            </div>
            <?php if (false && $isEditing): ?>
                <div class="bg-white rounded-[2.5rem] p-5 shadow-sm border border-slate-100">
                    <p class="text-slate-400 text-[11px] uppercase tracking-[3px] mb-4">Cập nhật ảnh</p>
                    <input name="hinh_anh" form="benhAnEditForm" type="file" accept="image/*" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-emerald-500">
                    <p class="mt-3 text-xs text-slate-500">Chọn ảnh mới nếu muốn thay ảnh của đúng hồ sơ này.</p>
                </div>
            <?php endif; ?>
            <?php if ($recordImage && file_exists($recordImage)): ?>
                <div class="bg-white rounded-[2.5rem] p-5 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-slate-400 text-[11px] uppercase tracking-[3px]">Ảnh hồ sơ</p>
                        <button type="button" onclick="openBenhAnImageModal('<?= htmlspecialchars($recordImage, ENT_QUOTES, 'UTF-8') ?>')" class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors">
                            <i class="fa fa-up-right-and-down-left-from-center text-sm"></i>
                        </button>
                    </div>
                    <button type="button"
                        class="group relative block w-full overflow-hidden rounded-[2rem] border border-slate-100 bg-slate-50 cursor-zoom-in"
                        onclick="openBenhAnImageModal('<?= htmlspecialchars($recordImage, ENT_QUOTES, 'UTF-8') ?>')">
                        <img src="<?= htmlspecialchars($recordImage) ?>" alt="Ảnh hồ sơ bệnh án" class="w-full h-auto max-h-[360px] object-contain bg-slate-100 transition duration-300 group-hover:scale-[1.01]">
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="benhAnImageModal" class="hidden fixed inset-0 z-[60] bg-black/85 backdrop-blur-sm p-4 md:p-8">
    <button type="button" onclick="closeBenhAnImageModal()" class="fixed right-4 top-4 md:right-8 md:top-8 z-[61] w-12 h-12 rounded-2xl bg-white/95 hover:bg-white text-slate-800 shadow-xl flex items-center justify-center transition-colors">
        <i class="fa fa-xmark text-xl"></i>
    </button>
    <div class="h-full w-full overflow-auto rounded-[2rem]" onclick="if (event.target === this) closeBenhAnImageModal()">
        <img id="benhAnImageModalImg" src="" alt="Ảnh hồ sơ bệnh án đầy đủ" class="mx-auto h-auto max-w-full rounded-[1.5rem] bg-white shadow-2xl">
    </div>
</div>

<script>
    function openBenhAnImageModal(src) {
        const modal = document.getElementById('benhAnImageModal');
        const image = document.getElementById('benhAnImageModalImg');
        if (!modal || !image || !src) return;

        image.src = src;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeBenhAnImageModal() {
        const modal = document.getElementById('benhAnImageModal');
        const image = document.getElementById('benhAnImageModalImg');
        if (!modal || !image) return;

        modal.classList.add('hidden');
        image.src = '';
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBenhAnImageModal();
        }
    });
</script>
