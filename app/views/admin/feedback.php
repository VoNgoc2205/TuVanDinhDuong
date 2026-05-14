<?php
$statusLabels = [
    'new' => ['label' => 'Mới', 'class' => 'bg-emerald-100 text-emerald-700'],
    'reviewed' => ['label' => 'Đã xem', 'class' => 'bg-blue-100 text-blue-700'],
    'resolved' => ['label' => 'Đã xử lý', 'class' => 'bg-slate-200 text-slate-700'],
];
$categoryLabels = [
    'general' => 'Góp ý chung',
    'bug' => 'Báo lỗi',
    'ai' => 'Chất lượng AI',
    'ui' => 'Giao diện',
    'feature' => 'Đề xuất tính năng',
];
?>

<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-black uppercase tracking-widest text-emerald-600">Admin</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Phản hồi người dùng</h1>
            <p class="mt-2 text-slate-500">Theo dõi đánh giá, báo lỗi và đề xuất được gửi từ người dùng.</p>
        </div>
        <div class="rounded-2xl bg-emerald-50 px-5 py-3 text-sm font-black text-emerald-700">
            <?= number_format($newCount ?? 0) ?> phản hồi mới
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <?php
        $filters = [
            '' => 'Tất cả',
            'new' => 'Mới',
            'reviewed' => 'Đã xem',
            'resolved' => 'Đã xử lý',
        ];
        ?>
        <?php foreach ($filters as $value => $label): ?>
            <a href="index.php?controller=admin&action=feedback<?= $value !== '' ? '&status=' . $value : '' ?>"
               class="rounded-2xl px-4 py-2 text-sm font-bold transition <?= ($status ?? '') === $value ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-100 hover:bg-slate-50' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="rounded-3xl border border-slate-100 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="px-5 py-4">Người dùng</th>
                        <th class="px-5 py-4">Đánh giá</th>
                        <th class="px-5 py-4">Nội dung</th>
                        <th class="px-5 py-4">Trạng thái</th>
                        <th class="px-5 py-4">Thời gian</th>
                        <th class="px-5 py-4 text-right">Xử lý</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($feedbacks)): ?>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <?php $statusInfo = $statusLabels[$feedback['status']] ?? $statusLabels['new']; ?>
                            <tr class="align-top hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-800"><?= htmlspecialchars($feedback['name'] ?? 'Người dùng') ?></p>
                                    <p class="mt-1 text-xs text-slate-400"><?= htmlspecialchars($feedback['email'] ?? '') ?></p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-sm font-black text-amber-600">
                                        <?= intval($feedback['rating']) ?> <i class="fa fa-star text-xs"></i>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="max-w-xl">
                                        <div class="mb-2 flex flex-wrap items-center gap-2">
                                            <p class="font-black text-slate-900"><?= htmlspecialchars($feedback['title']) ?></p>
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-500">
                                                <?= $categoryLabels[$feedback['category']] ?? 'Góp ý' ?>
                                            </span>
                                        </div>
                                        <p class="whitespace-pre-line text-sm leading-relaxed text-slate-600"><?= htmlspecialchars($feedback['message']) ?></p>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-black <?= $statusInfo['class'] ?>">
                                        <?= $statusInfo['label'] ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-500">
                                    <?= date('d/m/Y H:i', strtotime($feedback['created_at'])) ?>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <form action="index.php?controller=admin&action=updateFeedbackStatus" method="POST" class="inline-flex gap-2">
                                        <input type="hidden" name="id" value="<?= intval($feedback['id']) ?>">
                                        <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600">
                                            <?php foreach ($statusLabels as $value => $info): ?>
                                                <option value="<?= $value ?>" <?= $feedback['status'] === $value ? 'selected' : '' ?>><?= $info['label'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-600">
                                            Lưu
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">Chưa có phản hồi nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
