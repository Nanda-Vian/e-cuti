<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    public function __construct()
    {
        $this->req = \Config\Services::request();
        $this->masterModel = new \App\Models\MasterModel();
        $this->db = \Config\Database::connect();
    }

    private function dataTables($option, $array = false)
    {
        try {
            $this->masterModel->table = $option['table'] ?? '';
            $this->masterModel->columnOrder = $option['columnOrder'] ?? [];
            $this->masterModel->columnSearch = $option['columnSearch'] ?? [];
            $this->masterModel->selectData = $option['selectData'] ?? '';
            $this->masterModel->tableJoin = $option['join'] ?? [];
            $this->masterModel->order = $option['order'] ?? ['id' => 'desc'];
            $this->masterModel->whereData = $option['whereData'] ?? [];
            $this->masterModel->groupByData = $option['groupByData'] ?? [];
            $field = $option['field'] ?? [];
            $listData = $this->masterModel->get_datatables();
            // echo $this->db->getLastQuery();
            $data = [];
            foreach ($listData as $field_) {
                $row = [];
                $row['id'] = Enc($field_['id']);
                foreach ($field as $key) {
                    $row[$key] = $field_[$key];
                }
                $data[] = $row;
            }
            $draw = isset($_POST['draw']) ? $_POST['draw'] : null;
            $output = [
                'draw' => $draw,
                'recordsTotal' => $this->masterModel->count_all(),
                'recordsFiltered' => $this->masterModel->count_filtered(),
                'data' => $data,
            ];
            $result = $output;
        } catch (\Throwable $th) {
            $result = [
                'status' => 'fail',
                'message' => $th->getMessage() . ', Line : ' . $th->getLine() . ', File : ' . $th->getFile() . ', Query : ' . $this->db->getLastQuery(),
            ];
        } catch (\Exception $ex) {
            $result = [
                'status' => 'fail',
                'message' => $ex->getMessage(),
            ];
        } finally {
            if ($array == true) return $result;
            echo json_encode($result);
        }
    }

    private function getRowTable($option = ['table' => '', 'select' => '', 'where' => [], 'guard' => []], $array = false)
    {
        try {
            $data = $this->db->table($option['table'])->select(isset($option['select']) ? $option['select'] : '*')->where($option['where'])->get()->getRowArray();
            if (!$data) {
                throw new \Exception('no data');
            }
            $guard = ['id:hash', 'token', 'password'];
            if (!empty($option['guard'])) {
                $guard = array_merge($guard, $option['guard']);
            }

            if ($option['table'] == 'users') {
                $data = array_merge($data, [
                    'sisa_tahun_min2' => getSisaJatahTahunan(date('Y') - 2, $data['id']),
                    'sisa_tahun_min1' => getSisaJatahTahunan(date('Y') - 1, $data['id'])
                ]);
            }

            $data = Guard($data, $guard);

            $result = [
                'status' => 'ok',
                'data' => $data,
            ];
        } catch (\Throwable $th) {
            $result = [
                'status' => 'fail',
                'message' => $th->getMessage(),
            ];
        } catch (\Exception $ex) {
            $result = [
                'status' => 'fail',
                'message' => $ex->getMessage(),
            ];
        } finally {
            if ($array == true) return $result;
            echo json_encode($result);
        }
    }

    public function getDataOption($data = '')
    {
        try {
            if ($data == '') {
                throw new \Exception('no param');
            }
            $table = [
                'users' => [
                    'table' => 'users u',
                    'select' => 'u.id, u.nama, u.nip, ja.nama jabatan, ja.visible_all, u.unit_kerja_id',
                    'protected' => ['id:hash'],
                    'join' => [
                        'jabatan ja' => 'ja.id = u.jabatan_id'
                    ]
                ],
                'jabatan' => [
                    'table' => 'jabatan',
                    'protected' => ['id:hash'],
                ],
                'unit-kerja' => [
                    'table' => 'unit_kerja',
                    'protected' => ['id:hash'],
                ],
            ];
            if (!array_key_exists($data, $table)) {
                throw new \Exception('nothing there');
            }
            $builder = $this->db->table($table[$data]['table']);

            if (!empty($table[$data]['join'])) {
                foreach ($table[$data]['join'] as $key => $value) {
                    $builder->join($key, $value, 'left');
                }
            }

            if (!empty($table[$data]['select'])) {
                $builder->select($table[$data]['select']);
            }

            if (isset($_REQUEST['where'])) {
                $builder->where($_REQUEST['where']);
            }
            if (isset($_REQUEST['order'])) {
                $builder->orderBy(key($_REQUEST['order']), $_REQUEST['order'][key($_REQUEST['order'])]);
            }
            $data_ = $builder->get()->getResultArray();

            if ($data == 'users') {
                $dataApproval3 = $this->db->table('users u')
                    ->select('u.id, u.nama, u.nip, ja.nama jabatan, ja.visible_all, u.unit_kerja_id')
                    ->join('jabatan ja', 'ja.id = u.jabatan_id', 'left')
                    ->where('visible_all', '1')
                    ->get()->getResultArray();
                $data_ = array_unique(array_merge($data_, $dataApproval3), SORT_REGULAR);
            }

            $resultData = [];
            foreach ($data_ as $rows) {
                $rows = Guard($rows, $table[$data]['protected']);
                unset($rows['created_at']);
                $resultData[] = $rows;
            }
            $result = [
                'status' => 'ok',
                'data' => $resultData,
            ];
        } catch (\Throwable $th) {
            $result = [
                'status' => 'fail',
                'message' => $th->getMessage(),
            ];
        } catch (\Exception $ex) {
            $result = [
                'status' => 'fail',
                'message' => $ex->getMessage(),
            ];
        } finally {
            echo json_encode($result);
        }
    }

    public function setUserStatus()
    {
        try {
            $this->db = \Config\Database::connect();
            $postToken = $this->req->getPost('token');
            $userId = $this->req->getPost('userId');
            $status = $this->req->getPost('status');
            if (!$postToken) {
                throw new \Exception('no access token');
            }
            $user = $this->db->table('users')->select('username, token')->where('token', $postToken)->get()->getRow();
            if ($postToken != $user->token) {
                throw new \Exception('token invalid');
            }
            if (!$userId) {
                throw new \Exception('no user id');
            }
            if (!$status && !$status == 0) {
                throw new \Exception('no status');
            }
            if (!intval($status) && !$status == 0) {
                throw new \Exception('invalid status code');
            }
            if (intval($status) < 0 || intval($status) > 1) {
                throw new \Exception('invalid status code only 1 or 0');
            }
            if (!Update('users', ['online' => $status], [EncKey('id') => $userId])) {
                throw new \Exception('Gagal merubah status !');
            }
            // if ($status == 0) Update('users', ['token' => ""], [EncKey('id') => $userId]);

            $result = [
                'status' => 'ok',
                'message' => $status == 1 ? 'User Online ' . $user->username : 'User Offline ' . $user->username,
            ];
        } catch (\Throwable $th) {
            $result = [
                'status' => 'fail',
                'message' => $th->getMessage(),
            ];
        } catch (\Exception $ex) {
            $result = [
                'status' => 'fail',
                'message' => $ex->getMessage(),
            ];
        } finally {
            echo json_encode($result);
        }
    }

    public function dataKaryawan()
    {
        return $this->dataTables([
            'table' => 'users u',
            'selectData' => 'u.id, u.nama, u.email, u.nrk, u.nip, u.level, u.active, unit.nama unit_kerja, jab.nama jabatan',
            'field' => ['nama', 'email', 'level', 'unit_kerja', 'jabatan', 'active', 'nrk', 'nip'],
            'columnOrder' => [null, 'nama', 'email', 'level', 'unit_kerja', 'jabatan', 'nrk', 'nip'],
            'columnSearch' => ['u.nama', 'email', 'level', 'unit.nama', 'jab.nama'],
            'join' => [
                'unit_kerja unit' => 'u.unit_kerja_id = unit.id',
                'jabatan jab' => 'u.jabatan_id = jab.id',
            ],
            'order' => ['u.id' => 'desc'],
        ]);
    }

    public function dataUnitKerja()
    {
        return $this->dataTables([
            'table' => 'unit_kerja',
            'selectData' => 'id, nama',
            'field' => ['nama'],
            'columnOrder' => [null, 'nama'],
            'columnSearch' => ['nama'],
            'order' => ['id' => 'desc'],
        ]);
    }

    public function dataJabatan()
    {
        return $this->dataTables([
            'table' => 'jabatan',
            'selectData' => 'id, nama',
            'field' => ['nama'],
            'columnOrder' => [null, 'nama'],
            'columnSearch' => ['nama'],
            'order' => ['id' => 'desc'],
        ]);
    }

    public function dataPengajuan()
    {
        $whereData = ['pe.user_id' => session('userId')];

        if (session('level') == 2) {
            $whereData = [];
        }

        return $this->dataTables([
            'table' => 'pengajuan pe',
            'selectData' => 'pe.id, u.nama, u.nip, pe.tgl_mulai, pe.tgl_selesai, pe.jenis_cuti, pe.file_lampiran, pe.approval, pe.lama, pe.alasan, pe.alamat_cuti',
            'field' => ['nama', 'nip', 'tgl_mulai', 'tgl_selesai', 'jenis_cuti', 'file_lampiran', 'approval', 'lama'],
            'columnOrder' => [null, 'nama', 'jenis_cuti', 'tgl_mulai', null, 'approval'],
            'columnSearch' => ['nama', 'alasan', 'alamat_cuti'],
            'join' => [
                'users u' => 'u.id = pe.user_id',
            ],
            'whereData' => $whereData,
            'order' => ['pe.id' => 'desc'],
        ]);
    }

    public function dataPengajuanDashboard()
    {
        $whereData = [
            'pe.user_id' => session('userId'),
            'pe.approval' => '1'
        ];

        if (session('level') == 2) {
            $whereData = [
                'pe.approval' => '1'
            ];
        }

        return $this->dataTables([
            'table' => 'pengajuan pe',
            'selectData' => 'pe.id, u.nama, u.nip, pe.tgl_mulai, pe.tgl_selesai, pe.jenis_cuti, pe.file_lampiran, pe.approval, pe.lama, pe.alasan, pe.alamat_cuti',
            'field' => ['nama', 'nip', 'tgl_mulai', 'tgl_selesai', 'jenis_cuti', 'file_lampiran', 'approval', 'lama'],
            'columnOrder' => [null, 'nama', 'jenis_cuti', 'tgl_mulai', null, 'approval'],
            'columnSearch' => ['nama', 'alasan', 'alamat_cuti'],
            'join' => [
                'users u' => 'u.id = pe.user_id',
            ],
            'whereData' => $whereData,
            'order' => ['pe.id' => 'desc'],
        ]);
    }

    public function dataApproval()
    {
        return $this->dataTables([
            'table' => 'approval_pengajuan ap',
            'selectData' => 'ap.id, u.nama, u.nip, pe.tgl_mulai, pe.tgl_selesai, pe.jenis_cuti, pe.file_lampiran, pe.approval, pe.lama, pe.alasan, pe.alamat_cuti, ap.urut, ap.status',
            'field' => ['nama', 'nip', 'tgl_mulai', 'tgl_selesai', 'jenis_cuti', 'file_lampiran', 'approval', 'lama', 'urut', 'status'],
            'columnOrder' => [null, 'nama', 'jenis_cuti', 'tgl_mulai', null, 'approval'],
            'columnSearch' => ['nama', 'alasan', 'alamat_cuti'],
            'join' => [
                'pengajuan pe' => 'pe.id = ap.pengajuan_id',
                'users u' => 'u.id = pe.user_id',
            ],
            'whereData' => [
                'ap.user_id' => session('userId'),
                'ap.status' => '0',
            ],
            'groupByData' => [
                'ap.user_id', 'ap.pengajuan_id'
            ],
            'order' => ['ap.id' => 'desc'],
        ]);
    }


    public function dataProfile()
    {
        try {
            $user = $this->db->table('users')->select('*')->where('id', session('userId'))->get()->getRowArray();

            if (!$user) {
                throw new \Exception('User Not Found !');
            }
            $result = [
                'status' => 'ok',
                'data' => Guard($user, ['id:hash', 'password']),
            ];
        } catch (\Throwable $th) {
            $result = [
                'status' => 'fail',
                'message' => $th->getMessage(),
            ];
        } catch (\Exception $ex) {
            $result = [
                'status' => 'ok',
                'message' => $ex->getMessage(),
            ];
        } finally {
            echo json_encode($result);
        }
    }

    public function getRowUsers($id)
    {
        return $this->getRowTable([
            'table' => 'users',
            'where' => [EncKey('id') => $id],
            'guard' => [
                'unit_kerja_id:hash',
                'jabatan_id:hash',
            ],
        ]);
    }

    public function getRowUnitKerja($id)
    {
        return $this->getRowTable([
            'table' => 'unit_kerja',
            'where' => [EncKey('id') => $id],
        ]);
    }
    public function getRowJabatan($id)
    {
        return $this->getRowTable([
            'table' => 'jabatan',
            'where' => [EncKey('id') => $id],
        ]);
    }

    public function getRowApproval($id)
    {
        return $this->getRowTable([
            'table' => 'approval_pengajuan',
            'where' => [EncKey('id') => $id],
        ]);
    }

    public function getRowPengajuan($id)
    {
        $dataPengajuan =  $this->getRowTable([
            'table' => 'pengajuan',
            'where' => [EncKey('id') => $id],
        ], true);

        $dataApproval = $this->db->table('approval_pengajuan')->where([EncKey('pengajuan_id') => $id])->get()->getResult();

        foreach ($dataApproval as $key) {
            $user = $this->db->table('users')->select('nip, nama')->where('id', $key->user_id)->get()->getRow();
            $dataPengajuan['data']["approval_" . $key->urut] = "$user->nip - $user->nama";
        }

        return json_encode($dataPengajuan);
    }

    public function getApprovalUser()
    {
        try {
            $user = $this->db->table('users')->select('approval_1, approval_2, approval_3')->where(['id' => session('userId')])->get()->getRow();

            $app1Data = $this->db->table('users')->select('*')->where([EncKey('id') => $user->approval_1])->get()->getRow();
            $app2Data = $this->db->table('users')->select('*')->where([EncKey('id') => $user->approval_2])->get()->getRow();
            $app3Data = $this->db->table('users')->select('*')->where([EncKey('id') => $user->approval_3])->get()->getRow();

            $approval = [
                'approval_1' => $app1Data->nip . " - " . $app1Data->nama,
                'approval_2' => $app2Data->nip . " - " . $app2Data->nama,
                'approval_3' => $app3Data->nip . " - " . $app3Data->nama,
            ];
        } catch (\Throwable | \Exception $th) {
            $approval = [
                'approval_1' => "Belum diatur",
                'approval_2' => "Belum diatur",
                'approval_3' => "Belum diatur",
            ];
        } finally {
            return json_encode($approval);
        }
    }

    public function getYears()
    {
        try {
            for ($i = 2007; $i < intval(date('Y')); ++$i) {
            }
        } catch (\Throwable $th) {
            $result = [
                'status' => 'fail',
                'message' => $th->getMessage,
            ];
        } catch (\Exception $ex) {
            $result = [
                'status' => 'fail',
                'message' => $ex->getMessage,
            ];
        } finally {
            echo json_encode($result);
        }
    }
}