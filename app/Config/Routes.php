<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Login');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
// $routes->get('/', 'App\Controllers\Home::index');
// $routes->get('/aboutus', 'Home::aboutus');

// $routes->get('/', 'Home::home');
// $routes->get('/aboutus', 'Home::aboutus');

$routes->group('/abouts', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Abouts::index');
});

$routes->group('/contact', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Contact::index');
});

$routes->group('/teams', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/(:any)', 'Teams::index/$1');
});

$routes->group('/news', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'News::index');
    $routes->get('page', 'News::index');
    $routes->get('page/(:num)', 'News::index/$1');
    $routes->get('category/(:any)/page/(:num)', 'News::byCategory/$1/$2');
    $routes->get('category/(:any)', 'News::byCategory/$1');
    $routes->get('tags/(:any)/page/(:num)', 'News::byTags/$1/$2');
    $routes->get('tags/(:any)', 'News::byTags/$1');
    $routes->get('read', 'News::index');
    $routes->get('(:any)', 'News::detailNews');
});

$routes->group('/product', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Product::index');
    $routes->get('/detail', 'Product::detail');
    // $routes->get('(:any)', 'ProductCatalog::detailProduct');
    $routes->get('/read', 'Product::read');
});

$routes->group('/faqs', ['namespae' => 'App\Controller'], function ($routes) {
    $routes->get('(:any)/(:any)', 'Faqs::index/$1/$2');
});

$routes->group('/sendmail', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->post('/', 'Imel::sendmail');
});

// Login routes
$routes->group('/app/login', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Login::index');
    $routes->post('action', 'Login::action');
    $routes->post('destroy', 'Login::destroy');
});

// Admin routes
$routes->group('app', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');

    // user profile routes
    $routes->group('profile', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'Profile::index');
        $routes->post('update', 'Profile::update');
        $routes->post('delete', 'Profile::delete');
        $routes->post('set-password', 'Profile::setPassword');
    });

    // User Management routes
    $routes->group('karyawan', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'Karyawan::index');
        $routes->post('store', 'Karyawan::store');
        $routes->post('delete', 'Karyawan::delete');
        $routes->post('update', 'Karyawan::update');
        $routes->post('reset/(:any)', 'Karyawan::reset_/$1');
        $routes->post('set/(:any)', 'Karyawan::set_/$1');
        $routes->post('delete-multiple', 'Karyawan::deleteMultiple');
        $routes->post('reset-multiple', 'Karyawan::resetMultiple');
        $routes->post('set-multiple', 'Karyawan::setMultiple');
    });

    // Jabatan Management routes
    $routes->group('jabatan', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'Jabatan::index');
        $routes->post('store', 'Jabatan::store');
        $routes->post('delete', 'Jabatan::delete');
        $routes->post('update', 'Jabatan::update');
        $routes->post('delete-multiple', 'Jabatan::deleteMultiple');
    });

    /// Unit Kerja Products Management routes
    $routes->group('unit-kerja', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'UnitKerja::index');
        $routes->post('store', 'UnitKerja::store');
        $routes->post('delete', 'UnitKerja::delete');
        $routes->post('update', 'UnitKerja::update');
        $routes->post('delete-multiple', 'UnitKerja::deleteMultiple');
    });

    // Pengajuan Cuti Management routes
    $routes->group('pengajuan-cuti', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'PengajuanCuti::index');
        $routes->get('buat', 'PengajuanCuti::buat');
        $routes->post('store', 'PengajuanCuti::store');
        $routes->get('store', 'PengajuanCuti::store');
        $routes->get('approval', 'PengajuanCuti::approval');
        $routes->post('approval/set', 'PengajuanCuti::approvalSet');
        $routes->get('view/(:any)', 'PengajuanCuti::viewPengajuan/$1');
        $routes->get('view-approval/(:any)', 'PengajuanCuti::viewPengajuanApproval/$1');
        $routes->get('sign-approval/(:any)', 'PengajuanCuti::signPengajuanApproval/$1');
        $routes->get('print/(:any)', 'PengajuanCuti::printPengajuan/$1');
    });
});

// Api routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->post('setuser/status', 'Admin::setUserStatus');

    $routes->group('data', ['namespace' => 'App\Controllers\Api'], function ($routes) {
        $routes->post('unit-kerja', 'Admin::dataUnitKerja');
        $routes->post('jabatan', 'Admin::dataJabatan');
        $routes->post('users', 'Admin::dataKaryawan');
        $routes->post('options/(:any)', 'Admin::getDataOption/$1');
        $routes->post('profile', 'Admin::dataProfile');
        $routes->post('pengajuan', 'Admin::dataPengajuan');
        $routes->post('pengajuan-dashboard', 'Admin::dataPengajuanDashboard');
        $routes->post('approval', 'Admin::dataApproval');
    });

    $routes->group('row', ['namespace' => 'App\Controllers\Api'], function ($routes) {
        $routes->post('users/(:any)', 'Admin::getRowUsers/$1');
        $routes->post('unit-kerja/(:any)', 'Admin::getRowUnitKerja/$1');
        $routes->post('jabatan/(:any)', 'Admin::getRowJabatan/$1');
        $routes->post('pengajuan/(:any)', 'Admin::getRowPengajuan/$1');
        $routes->post('approval/(:any)', 'Admin::getRowApproval/$1');
    });

    $routes->group('get', ['namespace' => 'App\Controllers\Api'], function ($routes) {
        $routes->get('approval', 'Admin::getApprovalUser');
    });
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}