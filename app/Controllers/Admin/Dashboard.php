<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $tahunMin1 = date('Y') - 1;
        $tahunMin2 = date('Y') - 2;
        $cuti = [
            [
                'nama' => "Sisa $tahunMin2",
                'jumlah' => getSisaJatahTahunan($tahunMin2, session('userId'))
            ],
            [
                'nama' => "Sisa $tahunMin1",
                'jumlah' => getSisaJatahTahunan($tahunMin1, session('userId'))
            ],
            [
                'nama' => "Total " . date('Y'),
                'jumlah' => getJatahTahunan(session('userId'))
            ]
        ];

        $data = [
            'title' => 'Dashboard',
            'menu' => 'dashboard',
            'roti' => [
                'App' => '',
                'Dashboard:active' => '',
            ],
            'cuti' => $cuti
        ];
        return View('admin/dashboard', $data);
    }
}