<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-600">Người dùng</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Thêm người dùng</h1>
            <p class="mt-2 text-sm text-slate-500">Tài khoản mới sẽ dùng mật khẩu mặc định 123456.</p>
        </div>
        <a href="?controller=admin&action=user" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
            <i class="fa fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <form method="POST" action="index.php?controller=admin&action=storeUser" class="max-w-3xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="space-y-4">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-600">Tên</label>
                <input type="text" name="name" required class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-600">Email</label>
                <input type="email" name="email" required class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-600">Vai trò</label>
                <select name="role" class="min-h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="inline-flex min-h-[52px] items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 text-sm font-bold text-white transition hover:bg-emerald-700">
                <i class="fa fa-save"></i> Lưu người dùng
            </button>
            <a href="?controller=admin&action=user" class="inline-flex min-h-[52px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Hủy</a>
        </div>
    </form>
</div>
