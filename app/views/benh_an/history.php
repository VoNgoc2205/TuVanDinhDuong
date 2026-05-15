<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? [];
$userName = $user['name'] ?? 'Người dùng';

$allRecords = [];
foreach (($grouped ?? []) as $items) {
    foreach ($items as $item) {
        $allRecords[] = $item;
    }
}

usort($allRecords, function ($a, $b) {
    return strtotime($b['ngay_cap_nhat'] ?? 'now') <=> strtotime($a['ngay_cap_nhat'] ?? 'now');
});

$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

$filteredRecords = array_values(array_filter($allRecords, function ($record) use ($dateFrom, $dateTo) {
    $recordDate = date('Y-m-d', strtotime($record['ngay_cap_nhat'] ?? 'now'));

    if ($dateFrom !== '' && $recordDate < $dateFrom) {
        return false;
    }

    if ($dateTo !== '' && $recordDate > $dateTo) {
        return false;
    }

    return true;
}));

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 5;
$totalRecords = count($filteredRecords);
$totalPages = max(1, (int)ceil($totalRecords / $perPage));
$page = min($page, $totalPages);
$records = array_slice($filteredRecords, ($page - 1) * $perPage, $perPage);

$queryBase = $_GET;
unset($queryBase['page']);
?>

<div class="space-y-8">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <p class="text-slate-400 uppercase tracking-[3px] font-black text-xs mb-2">Hồ sơ bệnh án</p>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Lịch sử bệnh án của bạn</h1>
            <p class="text-slate-500 mt-2">Xem lại các bản ghi bệnh án, theo dõi kết quả AI và quản lý hồ sơ dinh dưỡng.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="index.php?controller=nutrition&action=list" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-slate-700 font-bold hover:bg-slate-50 transition">
                <i class="fa fa-arrow-left"></i>
                Quay lại hồ sơ dinh dưỡng
            </a>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                <h2 class="text-slate-500 text-xs font-black uppercase tracking-[3px] mb-6">Tổng quan</h2>
                <div class="space-y-4">
                    <div class="rounded-[2rem] bg-slate-50 p-5 border border-slate-100">
                        <p class="text-slate-400 text-xs uppercase tracking-widest">Người dùng</p>
                        <p class="text-slate-900 font-bold text-lg mt-2"><?= htmlspecialchars($userName) ?></p>
                    </div>
                    <?php if (!empty($stats)): ?>
                        <?php foreach ($stats as $item): ?>
                            <div class="rounded-[2rem] bg-slate-50 p-5 border border-slate-100">
                                <div class="flex justify-between gap-4 items-center">
                                    <div>
                                        <p class="text-slate-400 text-[10px] uppercase tracking-widest mb-2"><?= htmlspecialchars($item['loai']) ?></p>
                                        <p class="text-3xl font-black text-slate-900"><?= intval($item['count']) ?></p>
                                    </div>
                                    <div class="text-right text-slate-500 text-[12px]">
                                        Cập nhật:
                                        <span class="font-semibold">
                                            <?= !empty($item['last_update']) ? date('d/m/Y', strtotime($item['last_update'])) : '--' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="rounded-[2rem] bg-slate-50 p-5 border border-slate-100 text-slate-500">Chưa có hồ sơ bệnh án nào. Thêm hồ sơ để bắt đầu theo dõi.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                <h2 class="text-slate-500 text-xs font-black uppercase tracking-[3px] mb-6">Gợi ý</h2>
                <ul class="space-y-3 text-slate-600 text-sm">
                    <li class="flex items-start gap-3">
                        <span class="mt-1 w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Lưu lại dữ liệu bệnh án để AI hỗ trợ đề xuất dinh dưỡng chính xác hơn.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Sử dụng chức năng Xem chi tiết để kiểm tra kết quả phân tích và điều chỉnh hồ sơ.</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8 space-y-6">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900">Hồ sơ bệnh án</h2>
                        
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-slate-600 text-sm font-semibold">
                        <i class="fa fa-history"></i>
                        <?= $totalRecords ?> mục
                    </span>
                </div>

                <form id="medical-date-filter-form" method="GET" class="mb-6 grid gap-3 md:grid-cols-4">
                    <input type="hidden" name="controller" value="benh_an">
                    <input type="hidden" name="action" value="index">

                    <label class="text-sm font-bold text-slate-800">
                        Từ ngày
                        <input id="date_from_picker" type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"
                               class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none focus:border-emerald-400">
                    </label>

                    <label class="text-sm font-bold text-slate-800">
                        Đến ngày
                        <input id="date_to_picker" type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"
                               class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none focus:border-emerald-400">
                    </label>

                    <div class="flex items-end gap-2 md:col-span-2">
                        <button type="button" onclick="handleMedicalDateFilter()" class="inline-flex h-[46px] flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 text-sm font-bold text-white hover:bg-emerald-700">
                            <i class="fa fa-calendar-days"></i>
                            Lọc theo lịch
                        </button>
                        <a href="index.php?controller=benh_an&action=index"
                           class="inline-flex h-[46px] w-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
                           title="Xóa lọc">
                            <i class="fa fa-rotate-left"></i>
                        </a>
                    </div>
                </form>

                <?php if (empty($records)): ?>
                    <div class="rounded-[2rem] border border-dashed border-slate-200 p-10 text-center">
                        <p class="text-slate-500 text-sm">Không có hồ sơ bệnh án phù hợp.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($records as $record): ?>
                            <div class="rounded-[2rem] border border-slate-100 p-5 hover:shadow-md transition">
                                <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                                    <div class="min-w-0">
                                        <p class="text-slate-500 text-[11px] uppercase tracking-[2px] mb-2">Hồ sơ bệnh án</p>
                                        <p class="text-lg font-bold text-slate-900 truncate">
                                            <?= date('d/m/Y \l\ú\c H:i', strtotime($record['ngay_cap_nhat'])) ?>
                                        </p>
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <a href="index.php?controller=benh_an&action=view&id=<?= intval($record['id']) ?>" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                                            <i class="fa fa-eye"></i>
                                            Xem
                                        </a>
                                        <a href="index.php?controller=benh_an&action=view&id=<?= intval($record['id']) ?>&mode=edit" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700 transition">
                                            <i class="fa fa-pen"></i>
                                            Sửa
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm font-semibold text-slate-500">Trang <?= $page ?> / <?= $totalPages ?></p>
                            <div class="flex flex-wrap gap-2">
                                <?php $prevQuery = http_build_query(array_merge($queryBase, ['page' => max(1, $page - 1)])); ?>
                                <a href="index.php?<?= htmlspecialchars($prevQuery) ?>"
                                   class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-600 hover:bg-slate-50 <?= $page <= 1 ? 'pointer-events-none opacity-40' : '' ?>"
                                   title="Trang trước">
                                    <i class="fa fa-chevron-left"></i>
                                </a>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php $pageQuery = http_build_query(array_merge($queryBase, ['page' => $i])); ?>
                                    <a href="index.php?<?= htmlspecialchars($pageQuery) ?>"
                                       class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl px-3 text-sm font-bold <?= $i === $page ? 'bg-emerald-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                <?php $nextQuery = http_build_query(array_merge($queryBase, ['page' => min($totalPages, $page + 1)])); ?>
                                <a href="index.php?<?= htmlspecialchars($nextQuery) ?>"
                                   class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-600 hover:bg-slate-50 <?= $page >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>"
                                   title="Trang sau">
                                    <i class="fa fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function openDatePicker(id) {
    const input = document.getElementById(id);
    if (!input) return;
    if (typeof input.showPicker === 'function') {
        input.showPicker();
    } else {
        input.focus();
        input.click();
    }
}

function handleMedicalDateFilter() {
    const form = document.getElementById('medical-date-filter-form');
    const fromInput = document.getElementById('date_from_picker');
    const toInput = document.getElementById('date_to_picker');
    if (!form || !fromInput || !toInput) return;

    if (!fromInput.value) {
        openDatePicker('date_from_picker');
        return;
    }

    if (!toInput.value) {
        openDatePicker('date_to_picker');
        return;
    }

    form.submit();
}
</script>
