<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->addRedirect('/', '/admin/login', 302);

$routes->get('admin/login', 'Admin\AuthController::login');
$routes->post('admin/login', 'Admin\AuthController::attemptLogin');
$routes->get('login', 'Admin\AuthController::login');
$routes->post('login', 'Admin\AuthController::attemptLogin');
$routes->get('forgot-password', 'Admin\AuthController::forgotPassword');
$routes->post('forgot-password', 'Admin\AuthController::sendResetLink');
$routes->get('reset-password/(:segment)', 'Admin\AuthController::resetPassword/$1');
$routes->post('reset-password/(:segment)', 'Admin\AuthController::saveNewPassword/$1');
$routes->post('admin/logout', 'Admin\AuthController::logout');
$routes->group('admin', ['filter' => 'adminAuth'], static function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index');
    $routes->get('passengers/(:num)/bookings', 'Admin\ResourcesController::passengerBookings/$1');
    $routes->get('(:segment)', 'Admin\ResourcesController::index/$1');
    $routes->get('(:segment)/new', 'Admin\ResourcesController::new/$1');
    $routes->post('(:segment)', 'Admin\ResourcesController::create/$1');
    $routes->get('(:segment)/(:num)/edit', 'Admin\ResourcesController::edit/$1/$2');
    $routes->post('(:segment)/(:num)', 'Admin\ResourcesController::update/$1/$2');
    $routes->post('(:segment)/(:num)/deactivate', 'Admin\ResourcesController::deactivate/$1/$2');
});
