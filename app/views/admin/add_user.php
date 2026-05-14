<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-50 p-6">

    <!-- BACK -->
    <a href="?controller=admin&action=user"
        class="inline-flex items-center gap-2 text-green-700 font-medium hover:text-green-900 transition mb-6">
        <i class="fa fa-arrow-left"></i> Quay lại danh sách
    </a>

    <!-- TITLE -->
    <h2 class="text-2xl font-bold text-green-800 mb-6 flex items-center gap-2">
        <i class="fa fa-user-plus"></i> Thêm người dùng
    </h2>

    <div class="max-w-3xl bg-white rounded-2xl shadow-sm border border-green-100 p-6">

        <form method="POST" action="index.php?controller=admin&action=storeUser">

            <!-- NAME -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-green-700 mb-2">
                    Tên
                </label>
                <input type="text" name="ten"
                    class="w-full px-4 py-3 rounded-xl border border-green-200
                              focus:ring-2 focus:ring-green-300 outline-none"
                    required>
            </div>

            <!-- EMAIL -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-green-700 mb-2">
                    Email
                </label>
                <input type="email" name="email"
                    class="w-full px-4 py-3 rounded-xl border border-green-200
                              focus:ring-2 focus:ring-green-300 outline-none"
                    required>
            </div>

            <!-- ROLE -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-green-700 mb-2">
                    Vai trò
                </label>

                <select name="vai_tro"
                    class="w-full px-4 py-3 rounded-xl border border-green-200
                               focus:ring-2 focus:ring-green-300 outline-none">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- BUTTONS -->
            <div class="flex gap-3">

                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3
                               rounded-xl font-semibold shadow-sm transition flex items-center gap-2">
                    <i class="fa fa-save"></i> Lưu
                </button>

                <a href="?controller=admin&action=user"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3
                          rounded-xl font-semibold shadow-sm transition flex items-center gap-2">
                    <i class="fa fa-times"></i> Hủy
                </a>

            </div>

        </form>

    </div>
</div>