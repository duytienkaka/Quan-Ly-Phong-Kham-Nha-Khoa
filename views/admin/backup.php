<section class="admin-doctors">
    <h1>Sao lưu / Import dữ liệu</h1>
    <p class="subtitle">
        Xuất tất cả dữ liệu quản lý ra file CSV để sao lưu hoặc phân tích.
    </p>

    <div class="admin-panel">
        <div class="panel-header">
            <h2>Sao lưu (Export CSV)</h2>
        </div>

        <div class="backup-grid">
            <div class="backup-card">
                <div class="backup-icon">👥</div>
                <h3>Tài khoản / Người dùng</h3>
                <p>Xuất danh sách tất cả tài khoản (admin, bác sĩ, lễ tân, bệnh nhân).</p>
                <a href="index.php?controller=admin&action=exportCsv&table=users"
                   class="btn-primary">
                    ⬇️ Tải CSV tài khoản
                </a>
            </div>

            <div class="backup-card">
                <div class="backup-icon">👨‍⚕️</div>
                <h3>Bác sĩ</h3>
                <p>Xuất thông tin chi tiết về bác sĩ (chuyên khoa, kinh nghiệm).</p>
                <a href="index.php?controller=admin&action=exportCsv&table=doctors"
                   class="btn-primary">
                    ⬇️ Tải CSV bác sĩ
                </a>
            </div>

            <div class="backup-card">
                <div class="backup-icon">🏥</div>
                <h3>Bệnh nhân</h3>
                <p>Xuất danh sách bệnh nhân (họ tên, liên hệ, địa chỉ).</p>
                <a href="index.php?controller=admin&action=exportCsv&table=patients"
                   class="btn-primary">
                    ⬇️ Tải CSV bệnh nhân
                </a>
            </div>

            <div class="backup-card">
                <div class="backup-icon">💼</div>
                <h3>Dịch vụ / Giá</h3>
                <p>Xuất danh sách dịch vụ nha khoa với đơn giá hiện tại.</p>
                <a href="index.php?controller=admin&action=exportCsv&table=services"
                   class="btn-primary">
                    ⬇️ Tải CSV dịch vụ
                </a>
            </div>

            <div class="backup-card">
                <div class="backup-icon">📅</div>
                <h3>Lịch hẹn</h3>
                <p>Xuất tất cả lịch hẹn với trạng thái (đợi, hoàn thành, hủy).</p>
                <a href="index.php?controller=admin&action=exportCsv&table=appointments"
                   class="btn-primary">
                    ⬇️ Tải CSV lịch hẹn
                </a>
            </div>

            <div class="backup-card">
                <div class="backup-icon">📋</div>
                <h3>Hồ sơ y tế</h3>
                <p>Xuất lịch sử khám bệnh của các bệnh nhân.</p>
                <a href="index.php?controller=admin&action=exportCsv&table=medical_records"
                   class="btn-primary">
                    ⬇️ Tải CSV hồ sơ y tế
                </a>
            </div>

            <div class="backup-card">
                <div class="backup-icon">💰</div>
                <h3>Hóa đơn</h3>
                <p>Xuất tất cả hóa đơn thanh toán để lưu trữ hoặc kiểm toán.</p>
                <a href="index.php?controller=admin&action=exportCsv&table=invoices"
                   class="btn-primary">
                    ⬇️ Tải CSV hóa đơn
                </a>
            </div>
        </div>
    </div>
</section>
