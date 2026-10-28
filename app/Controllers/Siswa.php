<?php

namespace App\Controllers;

use App\Models\SiswaModel;
use CodeIgniter\HTTP\RedirectResponse;

class Siswa extends BaseController
{
    public function index(): string
    {
        $sort = $this->request->getGet('sort');
        $sort = is_string($sort) && in_array($sort, ['nis', 'nama', 'kelas'], true) ? $sort : 'id';
        $direction = $this->request->getGet('direction');
        $direction = is_string($direction) && in_array(strtoupper($direction), ['ASC', 'DESC'], true)
            ? strtoupper($direction)
            : 'ASC';

        $siswaModel = new SiswaModel();
        $data['totalSiswa'] = $siswaModel->countAllResults();
        $data['siswa'] = $siswaModel->getSiswa($sort, $direction);
        $data['pager'] = $siswaModel->pager;
        $data['sort'] = $sort;
        $data['direction'] = $direction;

        return view('siswa_view', $data);
    }

    public function tambah(): RedirectResponse
    {
        if (!$this->request->is('post')) {
            return redirect()->to(base_url())->withInput();
        }

        $siswaModel = new SiswaModel();
        $data = [
            'nis' => trim((string)$this->request->getPost('nis')),
            'nama' => trim((string)$this->request->getPost('nama')),
            'kelas' => trim((string)$this->request->getPost('kelas')),
        ];

        if (empty($data['nis']) || empty($data['nama']) || empty($data['kelas'])) {
            return redirect()->to(base_url())->withInput()->with('error', 'Semua kolom formulir wajib diisi.');
        }

        if ($siswaModel->tambahSiswa($data)) {
            return redirect()->to(base_url())->with('success', 'Data siswa baru berhasil ditambahkan.');
        }

        return redirect()->to(base_url())->withInput()->with('error', 'Gagal menambahkan data siswa.');
    }
}
