<?php

class HomeController
{
    public function index()
    {
        $pageTitle = 'Phòng khám nha khoa - Trang chủ';
        $view = __DIR__ . '/../views/home/homepage.php';
        include __DIR__ . '/../views/layouts/public_layout.php';
    }
    public function services()
    {
        $pageTitle = 'Phòng khám nha khoa - Dịch vụ';
        $view = __DIR__ . '/../views/home/services.php';
        include __DIR__ . '/../views/layouts/public_layout.php';
    }
    public function doctors()
    {
        $pageTitle = 'Phòng khám nha khoa - Bác sĩ';
        $view = __DIR__ . '/../views/home/doctors.php';
        include __DIR__ . '/../views/layouts/public_layout.php';
    }
    public function contact()
    {
        $pageTitle = 'Phòng khám nha khoa - Liên hệ';
        $view = __DIR__ . '/../views/home/contact.php';
        include __DIR__ . '/../views/layouts/public_layout.php';
    }
}
