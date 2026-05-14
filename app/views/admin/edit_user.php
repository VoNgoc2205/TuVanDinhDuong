<?php
$user = $user ?? [];
$name = $user['name'] ?? '';
$role = $user['role'] ?? 'user';
$status = $user['status'] ?? 'active';
$isSelf = (int)($user['id'] ?? 0) === (int)($_SESSION['user']['id'] ?? 0);
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-600">Người dùng</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Chỉnh sửa người dùng</h1>
            <p class="mt-2 text-sm text-slate-500">Cập nhật thông tin, vai trò và trạng thái tài khoản.</p>
        </div>
        <a href="index.php?controller=admin&action=user" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
            <i class="fa fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="index.php?controller=admin&action=updateUser" method="POST" class="max-w-4xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <input type="hidden" name="id" value="<?= (int)($user['id'] ?? 0) ?>">

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-600">Họ và tên</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-600">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-600">Vai trò</label>
                <select name="role" class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-600">Trạng thái</label>
                <select name="status" class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100" <?= $isSelf ? 'disabled' : '' ?>>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                    <option value="locked" <?= $status === 'locked' ? 'selected' : '' ?>>Đã khóa</option>
                </select>
                <?php if ($isSelf): ?>
                    <input type="hidden" name="status" value="active">
                    <p class="mt-2 text-xs font-semibold text-slate-400">Không thể khóa chính tài khoản đang đăng nhập.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="inline-flex min-h-[52px] items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 text-sm font-bold text-white transition hover:bg-emerald-700">
                <i class="fa fa-save"></i> Lưu thay đổi
            </button>
            <a href="index.php?controller=admin&action=user" class="inline-flex min-h-[52px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Hủy</a>
        </div>
    </form>
</div>
