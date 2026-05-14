<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? [];
$userName = $user['name'] ?? 'Người dùng';
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
                                    <div class="text-right text-slate-500 text-[12px]">Cập nhật: <span class="font-semibold"><?= date('d/m/Y', strtotime($item['last_update'])) ?></span></div>
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
            <?php if (empty($grouped)): ?>
                <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-dashed border-slate-200 text-center">
                    <p class="text-slate-500 text-sm mb-6">Hiện chưa có hồ sơ bệnh án nào trong lịch sử.</p>
                    <a href="index.php?controller=benh_an&action=add" class="inline-flex items-center gap-2 rounded-2xl bg-purple-600 px-6 py-3 text-white font-bold hover:bg-purple-700 transition">
                        <i class="fa fa-plus"></i>
                        Tạo hồ sơ mới
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($grouped as $loai => $items): ?>
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
                            <div>
                                
                                <h2 class="text-2xl font-black text-slate-900">Hồ sơ bệnh án</h2>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-slate-600 text-sm font-semibold">
                                <i class="fa fa-history"></i>
                                <?= count($items) ?> mục
                            </span>
                        </div>

                        <div class="space-y-4">
                            <?php foreach ($items as $record): ?>
                                <div class="rounded-[2rem] border border-slate-100 p-5 hover:shadow-md transition">
                                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                                        <div>
                                            <p class="text-slate-500 text-[11px] uppercase tracking-[2px] mb-2">Hồ sơ bệnh án</p>
                                            <p class="text-lg font-bold text-slate-900 truncate"><?= date('d/m/Y \l\ú\c H:i', strtotime($record['ngay_cap_nhat'])) ?></p>
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
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
