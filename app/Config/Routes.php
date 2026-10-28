<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('home', 'Home::index');
$routes->get('siswa', 'Siswa::index');
$routes->post('home/tambah', 'Home::tambah');
$routes->post('siswa/tambah', 'Siswa::tambah');
$routes->get('home/hapus/(:num)', 'Home::hapus/$1');
$routes->post('home/edit/(:num)', 'Home::edit/$1');