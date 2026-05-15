<?php
require_once "app/config/database.php";
require_once "app/helpers/SessionHelper.php";

SessionHelper::start();

$conn = mysqli_connect("localhost", "root", "", "tuvandinhduong");
$user = SessionHelper::user();
$user_id = $user['id'] ?? 0;

$filterDate = trim($_GET['date'] ?? '');
$filterMeal = trim($_GET['bua'] ?? '');
$filterFood = trim($_GET['food'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = ["m.user_id = ?"];
$params = [$user_id];
$types = "i";

if ($filterDate !== '') {
    $where[] = "DATE(m.thoigian) = ?";
    $params[] = $filterDate;
    $types .= "s";
}

if ($filterMeal !== '') {
    $where[] = "LOWER(m.bua) = LOWER(?)";
    $params[] = $filterMeal;
    $types .= "s";
}

if ($filterFood !== '') {
    $where[] = "EXISTS (
        SELECT 1 FROM meal_items mi2
        WHERE mi2.meal_id = m.id AND mi2.ten_mon LIKE ?
    )";
    $params[] = "%" . $filterFood . "%";
    $types .= "s";
}

$whereSql = implode(" AND ", $where);

function bindMealHistoryParams(mysqli_stmt $stmt, string $types, array &$params): void
{
    if ($types === '') {
        return;
    }

    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    $stmt->bind_param($types, ...$refs);
}

$countSql = "
SELECT COUNT(*) AS total FROM (
    SELECT DATE(m.thoigian), m.bua
    FROM meals m
    JOIN meal_items mi ON m.id = mi.meal_id
    WHERE {$whereSql}
    GROUP BY DATE(m.thoigian), m.bua
) grouped_meals
";

$countStmt = $conn->prepare($countSql);
bindMealHistoryParams($countStmt, $types, $params);
$countStmt->execute();
$totalRows = intval($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$sql = "
SELECT
    DATE(m.thoigian) as ngay,
    m.bua,
    COALESCE(SUM(mi.calo), 0) as total_calo,
    COALESCE(SUM(mi.protein), 0) as total_protein,
    COALESCE(SUM(mi.carb), 0) as total_carb,
    COALESCE(SUM(mi.fat), 0) as total_fat
FROM meals m
JOIN meal_items mi ON m.id = mi.meal_id
WHERE {$whereSql}
GROUP BY DATE(m.thoigian), m.bua
ORDER BY ngay DESC
LIMIT ? OFFSET ?
";

$listParams = array_merge($params, [$perPage, $offset]);
$listTypes = $types . "ii";
$stmt = $conn->prepare($sql);
bindMealHistoryParams($stmt, $listTypes, $listParams);
$stmt->execute();
$result = $stmt->get_result();

$meals = [];
while ($row = $result->fetch_assoc()) {
    $meals[] = $row;
}

$mealOptions = ['Sáng', 'Trưa', 'Tối', 'Bữa phụ'];
$queryBase = $_GET;
unset($queryBase['page']);
?>

<div>
    <div class="mb-6">
        <p class="text-sm font-black uppercase tracking-widest text-emerald-600">Lịch sử bữa ăn</p>
        <h1 class="text-4xl font-black text-slate-800 flex items-center gap-3">
            Nhật ký dinh dưỡng
        </h1>
        <p class="text-slate-500 mt-2">
            Theo dõi các bữa ăn và lượng calo hằng ngày của bạn.
        </p>
    </div>

    <form id="meal-date-filter-form" method="GET" class="mb-5 grid gap-3 md:grid-cols-5">
        <input type="hidden" name="controller" value="meal">
        <input type="hidden" name="action" value="history">

        <label class="text-sm font-bold text-slate-800">
            Ngày
            <input id="meal_date_picker" type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>"
                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none focus:border-emerald-400">
        </label>

        <label class="text-sm font-bold text-slate-800">
            Bữa ăn
            <select name="bua" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none focus:border-emerald-400">
                <option value="">Tất cả</option>
                <?php foreach ($mealOptions as $option): ?>
                    <option value="<?= htmlspecialchars($option) ?>" <?= $filterMeal === $option ? 'selected' : '' ?>>
                        <?= htmlspecialchars($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="text-sm font-bold text-slate-800 md:col-span-2">
            Món ăn
            <input type="search" name="food" value="<?= htmlspecialchars($filterFood) ?>" placeholder="Tìm theo tên món ăn..."
                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 placeholder:text-slate-400 outline-none focus:border-emerald-400">
        </label>

        <div class="flex items-end gap-2">
            <button type="button" onclick="handleMealFilter()" class="inline-flex h-[46px] flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 text-sm font-bold text-white hover:bg-emerald-700">
                <i class="fa fa-filter"></i>
                Lọc
            </button>
            <a href="index.php?controller=meal&action=history"
               class="inline-flex h-[46px] w-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
               title="Xóa lọc">
                <i class="fa fa-rotate-left"></i>
            </a>
        </div>
    </form>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50 text-slate-500 text-sm">
                    <tr>
                        <th class="px-6 py-4 text-left">Ngày</th>
                        <th class="px-6 py-4 text-left">Buổi ăn</th>
                        <th class="px-6 py-4 text-left">Năng lượng</th>
                        <th class="px-6 py-4 text-left">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($meals)): ?>
                        <?php foreach ($meals as $m): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-700">
                                    <?= date('d/m/Y', strtotime($m['ngay'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-sm font-semibold">
                                        <?= htmlspecialchars($m['bua']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold text-emerald-600">
                                    <?= (int)$m['total_calo'] ?> kcal
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">
                                        Đạt mục tiêu
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="index.php?controller=meal&action=detail&date=<?= urlencode($m['ngay']) ?>&bua=<?= urlencode($m['bua']) ?>"
                                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-emerald-600 border border-emerald-200 rounded-xl hover:bg-emerald-50 transition">
                                        <i class="fa fa-eye"></i>
                                        Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-10 text-slate-400 font-medium">
                                Chưa có dữ liệu phù hợp
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm font-semibold text-slate-500">
                    Hiển thị tối đa 20 dòng/trang · Trang <?= $page ?> / <?= $totalPages ?>
                </p>
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
    </div>
</div>

<script>
function openMealDatePicker(id) {
    const input = document.getElementById(id);
    if (!input) return;
    if (typeof input.showPicker === 'function') {
        input.showPicker();
    } else {
        input.focus();
        input.click();
    }
}

function handleMealFilter() {
    const form = document.getElementById('meal-date-filter-form');
    const dateInput = document.getElementById('meal_date_picker');
    const mealInput = form?.querySelector('[name="bua"]');
    const foodInput = form?.querySelector('[name="food"]');
    if (!form || !dateInput) return;

    const hasAnyFilter = Boolean(dateInput.value || mealInput?.value || foodInput?.value.trim());
    if (!hasAnyFilter) {
        openMealDatePicker('meal_date_picker');
        return;
    }

    form.submit();
}
</script>
