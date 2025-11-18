<?php
// views/admin/service_import.php
?>
<section class="admin-doctors">
    <h1>Import dịch vụ (CSV)</h1>
    <p class="subtitle">Tải lên file CSV để thêm hoặc cập nhật nhiều dịch vụ cùng lúc.</p>

    <?php if (!empty($errorView)): ?>
        <div class="form-error-box"><?= htmlspecialchars($errorView) ?></div>
    <?php endif; ?>

    <?php if (!empty($reportView)): ?>
        <div class="admin-panel">
            <div class="panel-header"><h2>Kết quả import</h2></div>
            <div style="padding:16px;">
                <p>Tổng dòng đọc: <strong><?= (int)$reportView['total'] ?></strong></p>
                <p>Thêm mới: <strong><?= (int)$reportView['inserted'] ?></strong></p>
                <p>Đã cập nhật: <strong><?= (int)$reportView['updated'] ?></strong></p>
                <p>Bỏ qua: <strong><?= (int)$reportView['skipped'] ?></strong></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="admin-import-box">
        <div class="import-container">
            <form method="post" enctype="multipart/form-data" class="import-form">
                <label>Chọn file CSV</label>
                <input type="file" name="csv_file" accept=".csv" required>

                <div class="hint">Lưu ý: file CSV phải có header theo đúng thứ tự dưới đây.</div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Tải lên và xử lý</button>
                    <a href="index.php?controller=admin&action=services" class="btn-secondary">Hủy</a>
                </div>

                <div style="margin-top:10px; display:flex; gap:8px; align-items:center;">
                    <a href="/dental_clinic/public/downloads/sample_services.csv" download class="btn-secondary btn-sm">Tải mẫu CSV</a>
                    <a href="#" class="btn-secondary btn-sm" onclick="alert('Đảm bảo file CSV có header: service_name,description,unit_price,unit,is_active');return false;">Xem hướng dẫn nhanh</a>
                </div>
            </form>

            <div class="import-guide">
                <h4>Hướng dẫn định dạng CSV</h4>
                <p class="hint">File CSV phải có header (dòng 1) và các cột theo thứ tự sau:</p>
                <table class="format-table">
                    <thead>
                        <tr>
                            <th>Column</th>
                            <th>Mô tả</th>
                            <th>Ví dụ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>service_name</td>
                            <td>Tên dịch vụ (bắt buộc)</td>
                            <td>Nạo vôi răng</td>
                        </tr>
                        <tr>
                            <td>description</td>
                            <td>Mô tả ngắn (có thể để trống)</td>
                            <td>Nạo vôi và đánh bóng</td>
                        </tr>
                        <tr>
                            <td>unit_price</td>
                            <td>Đơn giá bằng số (VNĐ) (bắt buộc)</td>
                            <td>200000</td>
                        </tr>
                        <tr>
                            <td>unit</td>
                            <td>Đơn vị tính (ví dụ: lần, răng)</td>
                            <td>lần</td>
                        </tr>
                        <tr>
                            <td>is_active</td>
                            <td>1 = đang dùng, 0 = ngừng dùng (mặc định 1 nếu bỏ trống)</td>
                            <td>1</td>
                        </tr>
                    </tbody>
                </table>

                <p class="hint" style="margin-top:12px;">Ví dụ nội dung CSV (dòng 1 là header):</p>
                <pre style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px;border-radius:8px;font-size:13px;">service_name,description,unit_price,unit,is_active
Nạo vôi răng,"Nạo vôi và đánh bóng",200000,lần,1
Trám răng,"Trám composite",350000,lần,1</pre>

                <p class="hint">Sau khi upload, hệ thống sẽ cố gắng cập nhật nếu tồn tại dịch vụ trùng tên, hoặc thêm mới nếu chưa có.</p>
            </div>
        </div>
    </div>
</section>
