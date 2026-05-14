<?php
$users = $users ?? [];
$totalUsers = $totalUsers ?? count($users);
$currentAdminId = (int)($_SESSION['user']['id'] ?? 0);
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-600">Người dùng</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Quản lý người dùng</h1>
            <p class="mt-2 text-sm text-slate-500">Tìm kiếm, phân quyền và khóa tài khoản người dùng.</p>
        </div>
        <a href="index.php?controller=admin&action=addUser" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-600">
            <i class="fa fa-user-plus"></i> Thêm người dùng
        </a>
    </div>

    <form method="GET" action="index.php" class="grid gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm xl:grid-cols-[auto_1fr_auto_auto_auto]">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="user">

        <div class="inline-flex items-center justify-center rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-700">
            Tổng: <?= number_format($totalUsers) ?>
        </div>

        <input type="text" name="keyword" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" placeholder="Tìm theo tên hoặc email..." class="min-h-[48px] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">

        <select name="role" class="min-h-[48px] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
            <option value="">Tất cả vai trò</option>
            <option value="admin" <?= ($_GET['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="user" <?= ($_GET['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
        </select>

        <select name="status" class="min-h-[48px] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
            <option value="">Tất cả trạng thái</option>
            <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
            <option value="locked" <?= ($_GET['status'] ?? '') === 'locked' ? 'selected' : '' ?>>Đã khóa</option>
        </select>

        <button class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700">
            <i class="fa fa-filter"></i> Lọc
        </button>
    </form>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-4">Người dùng</th>
                        <th class="px-5 py-4">Email</th>
                        <th class="px-5 py-4">Vai trò</th>
                        <th class="px-5 py-4">Trạng thái</th>
                        <th class="px-5 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <?php
                                $id = (int)($u['id'] ?? 0);
                                $initial = strtoupper(mb_substr($u['name'] ?? 'U', 0, 1));
                                $role = $u['role'] ?? 'user';
                                $status = $u['status'] ?? 'active';
                                $isLocked = $status === 'locked';
                                $isSelf = $id === $currentAdminId;
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 font-black text-emerald-700"><?= htmlspecialchars($initial) ?></div>
                                        <div>
                                            <p class="font-black text-slate-900"><?= htmlspecialchars($u['name'] ?? 'Không rõ') ?></p>
                                            <p class="text-xs text-slate-400">ID #<?= $id ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="index.php?controller=admin&action=updateUserRole">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <select name="role" onchange="this.form.submit()" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-black uppercase text-slate-700 outline-none transition focus:border-emerald-400 disabled:cursor-not-allowed disabled:opacity-50" <?= $isSelf ? 'disabled' : '' ?>>
                                            <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-black <?= $isLocked ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' ?>">
                                        <?= $isLocked ? 'ĐÃ KHÓA' : 'HOẠT ĐỘNG' ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="index.php?controller=admin&action=editUser&id=<?= $id ?>" class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition hover:bg-amber-100" title="Sửa">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form method="POST" action="index.php?controller=admin&action=toggleUserStatus" onsubmit="return confirm('<?= $isLocked ? 'Mở khóa tài khoản này?' : 'Khóa tài khoản này?' ?>')">
                                            <input type="hidden" name="id" value="<?= $id ?>">
                                            <input type="hidden" name="status" value="<?= $isLocked ? 'active' : 'locked' ?>">
                                            <button class="flex h-10 w-10 items-center justify-center rounded-xl <?= $isLocked ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?> transition disabled:cursor-not-allowed disabled:opacity-40" title="<?= $isLocked ? 'Mở khóa' : 'Khóa' ?>" <?= $isSelf ? 'disabled' : '' ?>>
                                                <i class="fa <?= $isLocked ? 'fa-lock-open' : 'fa-lock' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="index.php?controller=admin&action=deleteUser" onsubmit="return confirm('Xóa người dùng này?')">
                                            <input type="hidden" name="id" value="<?= $id ?>">
                                            <button class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600 transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-40" title="Xóa" <?= $isSelf ? 'disabled' : '' ?>>
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">Không có dữ liệu người dùng</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
