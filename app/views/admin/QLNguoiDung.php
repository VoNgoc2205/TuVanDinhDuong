<h3 class="text-2xl font-bold mb-6 text-slate-800">
    👥 Quản lý người dùng
</h3>

<!-- TOOLBAR -->
<form method="GET" action="index.php"
    class="flex flex-wrap items-center gap-3 mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-100">

    <input type="hidden" name="controller" value="admin">
    <input type="hidden" name="action" value="user">

    <!-- TOTAL -->
    <div class="px-4 py-2 rounded-xl bg-slate-50 border text-slate-700 font-semibold">
        Tổng: <span class="text-green-600"><?= isset($users) ? count($users) : 0 ?></span>
    </div>

    <!-- SEARCH -->
    <input type="text" name="keyword"
        value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>"
        placeholder="🔍 Tìm kiếm tên hoặc email..."
        class="flex-1 min-w-[200px] px-4 py-2 rounded-xl border border-slate-200
                  focus:ring-2 focus:ring-green-200 focus:border-green-500 outline-none">

    <!-- ROLE -->
    <select name="role"
        class="px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-green-200">
        <option value="">Tất cả vai trò</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
    </select>

    <!-- BUTTON -->
    <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl
                   font-semibold shadow-sm transition">
        Lọc
    </button>
</form>

<!-- LIST -->
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">

    <?php if (!empty($users)): ?>
        <?php foreach ($users as $u): ?>

            <?php
            $colors = ['bg-green-500', 'bg-blue-500', 'bg-purple-500', 'bg-orange-500'];
            $color = $colors[$u['id'] % count($colors)];
            ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100
                        p-6 text-center hover:shadow-md transition group">

                <!-- AVATAR -->
                <div class="w-16 h-16 mx-auto <?= $color ?> text-white flex items-center justify-center
                            rounded-2xl text-xl font-bold mb-3 shadow-sm group-hover:scale-105 transition">
                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                </div>

                <!-- NAME -->
                <h4 class="font-bold text-slate-800">
                    <?= htmlspecialchars($u['name']) ?>
                </h4>

                <!-- EMAIL -->
                <p class="text-sm text-slate-500 mt-1 break-words">
                    <?= htmlspecialchars($u['email']) ?>
                </p>

                <!-- ROLE -->
                <div class="mt-3">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                        <?= $u['role'] == 'admin'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-blue-100 text-blue-700' ?>">
                        <?= strtoupper($u['role']) ?>
                    </span>
                </div>

                <!-- ACTION -->
                <div class="flex justify-center gap-3 mt-5">

                    <form method="POST"
                        action="index.php?controller=admin&action=deleteUser"
                        onsubmit="return confirm('Xóa user này?')">

                        <input type="hidden" name="id" value="<?= $u['id'] ?>">

                        <button class="w-9 h-9 flex items-center justify-center rounded-xl
                                       bg-red-50 text-red-500 hover:bg-red-100 transition">
                            <i class="fa fa-trash"></i>
                        </button>

                    </form>

                </div>

            </div>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full text-center text-slate-500 py-10">
            Không có dữ liệu người dùng
        </div>
    <?php endif; ?>

</div>