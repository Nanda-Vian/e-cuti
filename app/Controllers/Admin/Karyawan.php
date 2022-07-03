<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Karyawan extends BaseController
{

    function __construct()
    {
        $this->req = \Config\Services::request();
        $this->table = "users";
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data = [
            'title' => 'Pengguna',
            'menu' => 'master',
            'subMenu' => 'karyawan',
            'roti' => [
                'App' => '',
                'Dashboard' => base_url(ADMIN_PATH . '/dashboard'),
                'Master' => '',
                'Karyawan (<b class="merah">Menghapus user dapat menghapus semua data yang berhubungan dengan user tersebut !</b>):active' => '',
            ]
        ];
        return View('admin/users/vUsers', $data);
    }

    public function store()
    {
        try {
            $validate = Validate([
                'email' => 'required|email',
                'nama' => 'required|min:2|max:50|name',
                'nip' => 'required|max:50|number',
                'nrk' => 'required|max:50|number',
                'jabatan_id' => 'required',
                'unit_kerja_id' => 'required',
                'level' => 'required|number',
                'tahun_masuk' => 'required|date',
                'cuti_tahun_jatah' => 'required|number|max:2',
                'approval_1' => '',
                'approval_2' => '',
                'approval_3' => '',
            ], [
                'password' => Enc('123456'),
                'cuti_tahun' => date('Y')
            ]);

            $user = $this->db->table($this->table)->where('email', Input_('email'))->get()->getRow();
            if ($user) $validate = ValidateAdd($validate, 'email', 'Email ada yang sama');

            if (Input_('cuti_tahun_jatah') > 12) {
                $validate = ValidateAdd($validate, 'cuti_tahun_jatah', "Tidak boleh lebih dari 12 hari !");
            }

            if (!$validate['success']) throw new \Exception("Error Processing Request");
            $idJabatan = $this->db->table('jabatan')->select('id')->where([EncKey('id') => Input_('jabatan_id')])->get()->getRow()->id;
            $idUnitKerja = $this->db->table('unit_kerja')->select('id')->where([EncKey('id') => Input_('unit_kerja_id')])->get()->getRow()->id;
            $validate['data']['unit_kerja_id'] = $idUnitKerja;
            $validate['data']['jabatan_id'] = $idJabatan;
            if (!Create($this->table, $validate['data'])) throw new \Exception("Gagal memasukan data !");

            $message = [
                'status' => 'ok',
                'message' => "Berhasil memasukan data"
            ];
        } catch (\Throwable $th) {
            $message = [
                'status' => 'fail',
                'message' => $th->getMessage()
            ];
        } catch (\Exception $ex) {
            $message = [
                'status' => 'fail',
                'message' => $ex->getMessage()
            ];
        } finally {
            $message = array_merge($message, ['validate' => $validate]);
            echo json_encode($message);
        }
    }

    public function update()
    {
        try {
            $validate = Validate([
                'email' => 'required|email',
                'nama' => 'required|min:2|max:50|name',
                'nip' => 'required|max:50|number',
                'nrk' => 'required|max:50|number',
                'jabatan_id' => 'required',
                'unit_kerja_id' => 'required',
                'level' => 'required|number',
                'tahun_masuk' => 'required|date',
                'cuti_tahun_jatah' => 'required|number|max:2',
                'approval_1' => '',
                'approval_2' => '',
                'approval_3' => '',
            ]);
            if (Input_('cuti_tahun_jatah') > 12) {
                $validate = ValidateAdd($validate, 'cuti_tahun_jatah', "Tidak boleh lebih dari 12 hari !");
            }
            if (Input_('sisa_tahun_min1') > 12) {
                $validate = ValidateAdd($validate, 'sisa_tahun_min1', "Tidak boleh lebih dari 12 hari !");
            }
            if (Input_('sisa_tahun_min2') > 12) {
                $validate = ValidateAdd($validate, 'sisa_tahun_min2', "Tidak boleh lebih dari 12 hari !");
            }
            if (!$validate['success']) throw new \Exception("Error Processing Request");
            $idJabatan = $this->db->table('jabatan')->select('id')->where([EncKey('id') => Input_('jabatan_id')])->get()->getRow()->id;
            $idUnitKerja = $this->db->table('unit_kerja')->select('id')->where([EncKey('id') => Input_('unit_kerja_id')])->get()->getRow()->id;
            $idUser =  $this->db->table('users')->select('id')->where([EncKey('id') => Input_('id')])->get()->getRow()->id;
            $validate['data']['unit_kerja_id'] = $idUnitKerja;
            $validate['data']['jabatan_id'] = $idJabatan;
            $cekThunMin1 = Where('cuti_tahunan', [EncKey('user_id') => Input_('id'), 'tahun' => date('Y') - 1]);
            $cekThunMin2 = Where('cuti_tahunan', [EncKey('user_id') => Input_('id'), 'tahun' => date('Y') - 2]);

            if ($cekThunMin1)
                Update('cuti_tahunan', ['sisa' => Input_('sisa_tahun_min1')], [EncKey('user_id') => Input_('id'), 'tahun' => date('Y') - 1]);
            else
                Create('cuti_tahunan', ['user_id' => $idUser, 'tahun' => date('Y') - 1, 'sisa' => Input_('sisa_tahun_min1')]);

            if ($cekThunMin2)
                Update('cuti_tahunan', ['sisa' => Input_('sisa_tahun_min2')], [EncKey('user_id') => Input_('id'), 'tahun' => date('Y') - 2]);
            else
                Create('cuti_tahunan', ['user_id' => $idUser, 'tahun' => date('Y') - 2, 'sisa' => Input_('sisa_tahun_min2')]);

            // Print_($cekThunMin2);

            // if (!Update($this->table, Guard($validate['data'], ['id']), [EncKey('id') => Input_('id')])) throw new \Exception("Tidak ada perubahan");

            Update($this->table, Guard($validate['data'], ['id']), [EncKey('id') => Input_('id')]);

            $message = [
                'status' => 'ok',
                'message' => "Berhasil merubah data"
            ];
        } catch (\Throwable $th) {
            $message = [
                'status' => 'fail',
                'message' => $th->getMessage()
            ];
        } catch (\Exception $ex) {
            $message = [
                'status' => 'fail',
                'message' => $ex->getMessage()
            ];
        } finally {
            $message = array_merge($message, ['validate' => $validate, 'modalClose' => true]);
            echo json_encode($message);
        }
    }

    public function reset_($id)
    {
        try {

            if ($id == '') throw new \Exception("no param");

            if (Update($this->table, ['password' => Enc("123456")], [EncKey('id') => $id]) == false) throw new \Exception("Gagal mereset password");

            $message = [
                'status' => 'ok',
                'message' => 'Berhasil mereset password'
            ];
        } catch (\Throwable $th) {
            $message = [
                'status' => 'fail',
                'message' => $th->getMessage()
            ];
        } catch (\Exception $ex) {
            $message = [
                'status' => 'fail',
                'message' => $ex->getMessage()
            ];
        } finally {
            echo json_encode($message);
        }
    }

    public function set_($id = '')
    {
        try {

            if ($id == '') throw new \Exception("no param");

            $status = $this->req->getPost('status') == "on" ? '1' : '0';

            if (Update($this->table, ['active' => $status], [EncKey('id') => $id]) == false) throw new \Exception("Gagal merubah status");

            $message = [
                'status' => 'ok',
                'message' => 'Berhasil merubah status'
            ];
        } catch (\Throwable $th) {
            $message = [
                'status' => 'fail',
                'message' => $th->getMessage()
            ];
        } catch (\Exception $ex) {
            $message = [
                'status' => 'fail',
                'message' => $ex->getMessage()
            ];
        } finally {
            echo json_encode($message);
        }
    }

    public function delete()
    {
        try {

            if (!isset($_POST['id'])) throw new \Exception("no param");

            $id = Input_('id');

            if (Delete($this->table, [EncKey('id') => $id]) == false) throw new \Exception("Gagal menghapus data");

            $message = [
                'status' => 'ok',
                'message' => 'Berhasil menghapus data'
            ];
        } catch (\Throwable $th) {
            $message = [
                'status' => 'fail',
                'message' => $th->getMessage()
            ];
        } catch (\Exception $ex) {
            $message = [
                'status' => 'fail',
                'message' => $ex->getMessage()
            ];
        } finally {
            echo json_encode($message);
        }
    }

    public function deleteMultiple()
    {
        try {

            if (!isset($_POST['dataId'])) throw new \Exception("no param");

            $dataId = explode(",", Input_('dataId'));

            $jmlSukses = 0;
            foreach ($dataId as $key) {
                if (Delete($this->table, [EncKey('id') => $key])) $jmlSukses++;
            }

            $message = [
                'status' => 'ok',
                'message' => "Berhasil menghapus <b>$jmlSukses</b> data dari <b>" . count($dataId) . "</b> data"
            ];
        } catch (\Throwable $th) {
            $message = [
                'status' => 'fail',
                'message' => $th->getMessage()
            ];
        } catch (\Exception $ex) {
            $message = [
                'status' => 'fail',
                'message' => $ex->getMessage()
            ];
        } finally {
            echo json_encode($message);
        }
    }

    public function resetMultiple()
    {
        try {

            if (!isset($_POST['dataId'])) throw new \Exception("no param");

            $dataId = explode(",", Input_('dataId'));

            $jmlSukses = 0;
            foreach ($dataId as $key) {
                if (Update($this->table, ['password' => Enc("123456")], [EncKey('id') => $key])) $jmlSukses++;
            }

            $message = [
                'status' => 'ok',
                'message' => "Berhasil mereset <b>$jmlSukses</b> data dari <b>" . count($dataId) . "</b> data"
            ];
        } catch (\Throwable $th) {
            $message = [
                'status' => 'fail',
                'message' => $th->getMessage()
            ];
        } catch (\Exception $ex) {
            $message = [
                'status' => 'fail',
                'message' => $ex->getMessage()
            ];
        } finally {
            echo json_encode($message);
        }
    }

    public function setMultiple()
    {
        try {

            if (!isset($_POST['dataId'])) throw new \Exception("no param");
            if (!isset($_POST['action'])) throw new \Exception("missing param");

            $dataId = explode(",", Input_('dataId'));
            $status = Input_('action') == 'active' ? '1' : '0';
            $jmlSukses = 0;

            foreach ($dataId as $key) {
                if (Update($this->table, ['active' => $status], [EncKey('id') => $key])) $jmlSukses++;
            }

            $message = [
                'status' => 'ok',
                'message' => "Berhasil merubah status <b>$jmlSukses</b> data dari <b>" . count($dataId) . "</b> data"
            ];
        } catch (\Throwable $th) {
            $message = [
                'status' => 'fail',
                'message' => $th->getMessage()
            ];
        } catch (\Exception $ex) {
            $message = [
                'status' => 'fail',
                'message' => $ex->getMessage()
            ];
        } finally {
            echo json_encode($message);
        }
    }
}