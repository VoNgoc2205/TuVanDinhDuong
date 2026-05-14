<?php
$success = $success ?? '';
$error = $error ?? '';
$feedbacks = $feedbacks ?? [];

$categoryLabels = [
    'general' => 'Góp ý chung',
    'bug' => 'Báo lỗi',
    'ai' => 'Chất lượng AI',
    'ui' => 'Giao diện',
    'feature' => 'Đề xuất tính năng',
];
?>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="xl:col-span-2">
        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <p class="text-sm font-black uppercase tracking-widest text-emerald-600">Phản hồi hệ thống</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Gửi đánh giá cho quản trị hệ thống</h1>
            <p class="mt-2 text-slate-500">Mọi phản hồi của bạn sẽ được gửi về trang quản trị để quản trị viên theo dõi và xử lý.</p>

            <?php if ($success): ?>
                <div class="mt-5 flex items-center gap-3 rounded-[14px] border border-green-300 bg-green-50 px-5 py-4 text-sm font-extrabold text-emerald-700">
                    <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-emerald-600 text-[10px] text-white"><i class="fa fa-check"></i></span>
                    Phản hồi của bạn đã được gửi thành công.
                </div>
            <?php elseif ($error): ?>
                <div class="mt-5 flex items-center gap-3 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 text-sm font-extrabold text-red-700">
                    <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-600 text-[10px] text-white"><i class="fa fa-xmark"></i></span>
                    Vui lòng nhập đầy đủ tiêu đề và nội dung phản hồi.
                </div>
            <?php endif; ?>

            <form action="index.php?controller=feedback&action=submit" method="POST" class="mt-6 space-y-5">
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">Mức đánh giá</label>
                    <div class="grid grid-cols-5 gap-2">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="<?= $i ?>" class="peer hidden" <?= $i === 5 ? 'checked' : '' ?>>
                                <span class="flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-black text-slate-500 transition peer-checked:border-amber-300 peer-checked:bg-amber-50 peer-checked:text-amber-600">
                                    <?= $i ?> <i class="fa fa-star ml-1 text-xs"></i>
                                </span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Loại phản hồi</label>
                        <select name="category" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold outline-none focus:border-emerald-400 focus:bg-white">
                            <?php foreach ($categoryLabels as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Tiêu đề</label>
                        <input type="text" name="title" maxlength="180" required
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold outline-none focus:border-emerald-400 focus:bg-white"
                               placeholder="Ví dụ: AI phân tích món ăn chưa đúng">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">Nội dung chi tiết</label>
                    <textarea name="message" rows="7" required
                              class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium outline-none focus:border-emerald-400 focus:bg-white"
                              placeholder="Mô tả điều bạn hài lòng, chưa hài lòng hoặc tính năng bạn muốn hệ thống bổ sung..."></textarea>
                </div>

                <button class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-emerald-100 transition hover:bg-emerald-700">
                    <i class="fa fa-paper-plane"></i>
                    Gửi phản hồi
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-slate-900">Phản hồi gần đây</h2>
        <p class="mt-1 text-sm text-slate-500">Các phản hồi bạn đã gửi cho quản trị hệ thống.</p>

        <div class="mt-5 space-y-3">
            <?php if (!empty($feedbacks)): ?>
                <?php foreach ($feedbacks as $feedback): ?>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($feedback['title']) ?></p>
                                <p class="mt-1 text-xs text-slate-400"><?= $categoryLabels[$feedback['category']] ?? 'Góp ý' ?> · <?= date('d/m/Y H:i', strtotime($feedback['created_at'])) ?></p>
                            </div>
                            <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-black text-amber-700">
                                <?= intval($feedback['rating']) ?> <i class="fa fa-star"></i>
                            </span>
                        </div>
                        <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-slate-600"><?= htmlspecialchars($feedback['message']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="rounded-2xl bg-slate-50 p-5 text-center text-sm text-slate-400">Bạn chưa gửi phản hồi nào.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
