<?php

namespace App\Controllers;

use App\Models\SiswaModel;
use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
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

    public function hapus($id)
    {
        $siswaModel = new SiswaModel();

        // Hapus data berdasarkan ID
        if ($siswaModel->delete($id)) {
            return redirect()->to(base_url())->with('success', 'Data siswa berhasil dihapus.');
        }

        return redirect()->to(base_url())->with('error', 'Gagal menghapus data siswa.');
    }

    public function edit($id): RedirectResponse
    {
        if (!$this->request->is('post')) {
            return redirect()->to(base_url())->withInput();
        }

        $siswaModel = new SiswaModel();
        if ($siswaModel->find($id) === null) {
            return redirect()->to(base_url())->with('error', 'Data siswa tidak ditemukan.');
        }

        $data = [];
        foreach (['nis', 'nama', 'kelas'] as $field) {
            $value = $this->request->getPost($field);
            if (!is_string($value) || trim($value) === '') {
                return redirect()->to(base_url())->withInput()->with('error', 'Semua kolom formulir wajib diisi.');
            }

            $data[$field] = trim($value);
        }

        if (!$siswaModel->update($id, $data)) {
            return redirect()->to(base_url())->with('error', 'Gagal memperbarui data siswa.');
        }

        return redirect()->to(base_url())->with('success', 'Perubahan data siswa berhasil disimpan.');
    }
}
