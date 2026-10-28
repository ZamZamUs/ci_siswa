<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['nis', 'nama', 'kelas'];

    public function getSiswa(string $sort = 'id', string $direction = 'ASC'): array
    {
        $columns = ['nis', 'nama', 'kelas'];
        if (! in_array($sort, $columns, true)) {
            $sort = 'id';
        }

        $direction = strtoupper($direction);
        if (! in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }

        $this->orderBy($sort, $direction);
        if ($sort !== 'id') {
            $this->orderBy('id', 'ASC');
        }

        return $this->paginate(7);
    }

    public function tambahSiswa(array $data): bool
    {
        return $this->insert($data) !== false;
    }
}
