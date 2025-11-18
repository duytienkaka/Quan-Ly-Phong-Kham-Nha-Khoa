<?php

class HomeController
{
    public function index()
    {
        $pageTitle = 'Phòng khám nha khoa - Trang chủ';
        $view = __DIR__ . '/../views/home/homepage.php';
        include __DIR__ . '/../views/layouts/public_layout.php';
    }
}
