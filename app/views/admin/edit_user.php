<script src="https://cdn.tailwindcss.com"></script>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-lime-50 py-10 px-4">

    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-8">

        <!-- TITLE -->
        <div class="flex items-center gap-3 mb-8">
            <i class="fa fa-user-edit text-2xl text-green-700"></i>
            <h1 class="text-2xl font-bold text-green-700">
                Chỉnh sửa người dùng
            </h1>
        </div>

        <form action="index.php?controller=admin&action=updateUser" method="POST" class="space-y-6">

            <input type="hidden" name="id" value="<?= $user['id'] ?>">

            <!-- ROW 1 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-2">
                        Họ và tên
                    </label>
                    <input type="text" name="ten"
                        value="<?= htmlspecialchars($user['ten']) ?>"
                        class="w-full h-12 px-4 rounded-xl border border-green-200 focus:border-green-600 focus:ring-2 focus:ring-green-200 outline-none transition"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-2">
                        Email
                    </label>
                    <input type="email" name="email"
                        value="<?= htmlspecialchars($user['email']) ?>"
                        class="w-full h-12 px-4 rounded-xl border border-green-200 focus:border-green-600 focus:ring-2 focus:ring-green-200 outline-none transition"
                        required>
                </div>

            </div>

            <!-- ROW 2 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-2">
                        Vai trò
                    </label>
                    <select name="vai_tro"
                        class="w-full h-12 px-4 rounded-xl border border-green-200 focus:border-green-600 focus:ring-2 focus:ring-green-200 outline-none transition">

                        <option value="user" <?= $user['vai_tro'] == 'user' ? 'selected' : '' ?>>
                            User
                        </option>

                        <option value="admin" <?= $user['vai_tro'] == 'admin' ? 'selected' : '' ?>>
                            Admin
                        </option>

                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-2">
                        Trạng thái
                    </label>
                    <select name="status"
                        class="w-full h-12 px-4 rounded-xl border border-green-200 focus:border-green-600 focus:ring-2 focus:ring-green-200 outline-none transition">

                        <option value="active" <?= ($user['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>
                            Hoạt động
                        </option>

                        <option value="inactive" <?= ($user['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>
                            Bị khóa
                        </option>

                    </select>
                </div>

            </div>

            <!-- BUTTONS -->
            <div class="flex flex-col sm:flex-row gap-4 pt-4">

                <button type="submit"
                    class="flex items-center justify-center gap-2 bg-gradient-to-r from-green-600 to-green-800 text-white font-semibold px-6 h-12 rounded-xl hover:opacity-90 transition">
                    <i class="fa fa-save"></i>
                    Lưu thay đổi
                </button>

                <a href="index.php?controller=admin&action=user"
                    class="flex items-center justify-center h-12 px-6 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
                    Hủy bỏ
                </a>

            </div>

        </form>
    </div>
</div>