<?php
// ❌ Tắt lỗi hiển thị ra giao diện
error_reporting(0);
ini_set('display_errors', 0);
?>

<style>
body {
    background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
    font-family: 'Segoe UI', sans-serif;
}

/* TITLE */
.title {
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 25px;
    color: #2e7d32;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* GRID */
.food-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

/* CARD */
.food-card {
    position: relative;
    background: #fff;
    border-radius: 15px;
    padding: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: 0.3s;
}

.food-card:hover {
    transform: translateY(-5px);
}

/* IMAGE */
.food-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 10px;
}

/* SEARCH */
.search-box {
    width: 420px;
    margin-bottom: 25px;
}

/* DELETE BUTTON */
.btn-delete {
    position: absolute;
    bottom: 12px;   /* 👈 xuống dưới */
    right: 12px;    /* 👈 sát phải */

    width: 36px;
    height: 36px;

    background: linear-gradient(135deg, #ff5252, #e53935);
    color: white;

    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;

    z-index: 10;
    transition: 0.3s;
}

.btn-delete:hover {
    transform: scale(1.15);
}

.btn-delete:hover {
    transform: scale(1.15);
}

/* TEXT */
.food-card h5 {
    margin-top: 10px;
    font-weight: 600;
}

.food-card p {
    color: #666;
    margin: 5px 0;
}
</style>

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- TITLE -->
<div class="title">
    <i class="fa-solid fa-fire"></i> Thực phẩm phổ biến
</div>

<!-- SEARCH -->
<form method="GET" class="search-box">
    <input type="hidden" name="controller" value="admin">
    <input type="hidden" name="action" value="popular">

    <div class="input-group">
        <input type="text" name="keyword" class="form-control"
               placeholder="🔍 Tìm tên thực phẩm..."
               value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">

        <button class="btn btn-success">
            <i class="fa fa-search"></i>
        </button>
    </div>
</form>

<!-- GRID -->
<div class="food-grid">

<?php foreach ($foods as $food): ?>

    <div class="food-card">

        <!-- DELETE -->
       <a href="index.php?controller=admin&action=deletePopular&ten=<?= urlencode($food['ten_mon']) ?>"
   class="btn-delete"
   onclick="return confirm('Xóa món này?')">
    <i class="fa fa-trash"></i>
</a>

        <!-- IMAGE -->
        <img src="/tuvandinhduong/<?= !empty($food['hinh_anh']) ? $food['hinh_anh'] : 'public/uploads/default.jpg' ?>">

        <!-- NAME -->
        <h5><?= htmlspecialchars($food['ten_mon']) ?></h5>

        <!-- CALO -->
        <p><?= $food['calo'] ?> kcal / 100g</p>

        <!-- TAG -->
        <span class="badge bg-success">P: <?= $food['protein'] ?>g</span>
        <span class="badge bg-primary">Thực phẩm</span>

    </div>

<?php endforeach; ?>

</div>