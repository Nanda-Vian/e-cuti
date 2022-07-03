<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PengajuanCuti extends BaseController
{
    public function __construct()
    {
        $this->table = 'pengajuan';
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data = [
            'title' => 'Pengajuan Cuti',
            'menu' => 'cuti',
            'subMenu' => 'pengajuan',
            'roti' => [
                'App' => '',
                'Dashboard' => base_url(ADMIN_PATH . '/dashboard'),
                'Pengajuan' => '',
                'Cuti:active' => '',
            ],
        ];

        return View('admin/pengajuan/vCuti', $data);
    }

    public function buat()
    {
        $data = [
            'title' => 'Buat Pengajuan Cuti',
            'menu' => 'cuti',
            'subMenu' => 'buat-pengajuan',
            'roti' => [
                'App' => '',
                'Dashboard' => base_url(ADMIN_PATH . '/dashboard'),
                'Pengajuan' => '',
                'Cuti' => base_url(ADMIN_PATH . '/pengajuan-cuti'),
                'Form Pengajuan Cuti:active' => '',
            ],
        ];

        return View('admin/pengajuan/vBuat', $data);
    }

    public function store()
    {
        try {

            $validate = Validate([
                'jenis_cuti' => 'required|number',
                'alasan' => 'required',
                'tgl_mulai' => 'required|date',
                'tgl_selesai' => 'required|date',
                'alamat_cuti' => 'required',
                'kontak' => 'required|number',
            ], [
                'user_id' => session('userId'),
                'lama' => count(getBetweenDates(Input_('tgl_mulai'), Input_('tgl_selesai')))
            ]);

            $lamaCuti = count(getBetweenDates(Input_('tgl_mulai'), Input_('tgl_selesai')));

            $tglMulai = date('Y-m-d', strtotime(Input_('tgl_mulai')));
            $tglSelesai = date('Y-m-d', strtotime(Input_('tgl_selesai')));


            if ($tglMulai > $tglSelesai) {
                $validate = ValidateAdd($validate, 'tgl_selesai', 'Tanggal Selesai tidak boleh kurang dari tanggal mulai');
            }

            $batasCutiTahunan = Where('users', ['id' => session('userId')])['cuti_tahun_jatah'];

            $sisaCuti = $batasCutiTahunan - $lamaCuti;

            if (Input_('jenis_cuti') == "0") {
                if ($lamaCuti > $batasCutiTahunan) {
                    $validate = ValidateAdd($validate, 'tgl_selesai', "$lamaCuti hari melebihi dari $batasCutiTahunan hari jatah cuti tahunan");
                }
            }

            if (!$validate['success']) throw new \Exception("Isi form dengan benar");

            if (!Create($this->table, $validate['data'])) throw new \Exception("Gagal mengirim pengajuan");

            $pengajuanId = $this->db->insertID();

            // $userApproval = $this->db->table('users')->select('approval_1, approval_2, approval_3')->where(['id' => session('userId')])->get()->getRow();

            // $_approval[] = $userApproval->approval_1;
            // $_approval[] = $userApproval->approval_2;
            // $_approval[] = $userApproval->approval_3;

            // foreach ($_approval as $key => $value) {
            //     $approval[] = [
            //         'urut' => $key + 1,
            //         'pengajuan_id' => $pengajuanId,
            //         'user_id' => $this->db->table('users')->select('id')->where([EncKey('id') => $value])->get()->getRow()->id
            //     ];
            // }

            foreach ($_REQUEST as $key => $value) {
                if (substr($key, 0, 9) == 'approval_') {
                    $approval[] = [
                        'urut' => substr($key, 9, 1),
                        'pengajuan_id' => $pengajuanId,
                        'user_id' => $this->db->table('users')->select('id')->where([EncKey('id') => $value])->get()->getRow()->id
                    ];
                }
            }

            // Print_($approval, true);

            if (!Create('approval_pengajuan', $approval)) throw new \Exception("Gagal menambah approval");
            if (Input_('jenis_cuti') == "0") {
                Update('users', ['cuti_tahun_jatah' => $sisaCuti], ['id' => session('userId')]);
            }

            $message = [
                'status' => 'ok',
                'message' => "Berhasil membuat pengajuan",
                'pengajuanId' => Enc($pengajuanId)
            ];
        } catch (\Throwable | \Exception $error) {
            $message = [
                'status' => 'fail',
                'message' => $error->getMessage()
            ];
        } finally {
            $message = array_merge($message, ['validate' => $validate]);
            echo json_encode($message);
        }
    }

    public function approval()
    {
        $data = [
            'title' => 'Approval Pengajuan Cuti',
            'menu' => 'cuti',
            'subMenu' => 'approval',
            'roti' => [
                'App' => '',
                'Dashboard' => base_url(ADMIN_PATH . '/dashboard'),
                'Pengajuan' => '',
                'Approval:active' => '',
            ],
        ];

        return View('admin/pengajuan/vApproval', $data);
    }

    public function viewPengajuan($id)
    {
        $pengajuanData = $this->db->table($this->table . ' pe')
            ->select('u.nama, u.nip')
            ->join('users u', 'u.id = pe.user_id')
            ->where([EncKey('pe.id') => $id])->get()->getRow();
        $nama = $pengajuanData->nama ?? "-";
        $nip = $pengajuanData->nip ?? "-";
        $data = [
            'title' => "Pengajuan $nama",
            'menu' => 'cuti',
            'subMenu' => 'pengajuan',
            'roti' => [
                'App' => '',
                'Dashboard' => base_url(ADMIN_PATH . '/dashboard'),
                'Pengajuan' => '',
                'Cuti' => base_url(ADMIN_PATH . '/pengajuan-cuti'),
                "$nip - $nama:active" => '',
            ],
            'pengajuanId' => $id,
            'nama' => $nama,
            'nip' => $nip
        ];

        return View('admin/pengajuan/vView', $data);
    }

    public function printPengajuan($id)
    {
        $pengajuanData = $this->db->table($this->table . ' pe')
            ->select('u.nama, u.nip, u.tahun_masuk, u.approval_1, u.approval_2, u.approval_3, pe.*, pe.created_at pengajuan_dibuat, u.jabatan_id, u.unit_kerja_id, u.id user_id')
            ->join('users u', 'u.id = pe.user_id')
            ->where([EncKey('pe.id') => $id])->get()->getRow();

        $nama = $pengajuanData->nama ?? "-";
        $jabatan = (object) Where('jabatan', ['id' => $pengajuanData->jabatan_id]);
        $unitKerja = (object) Where('unit_kerja', ['id' => $pengajuanData->unit_kerja_id]);

        $approve2 = Where('approval_pengajuan', ['urut' => '2', 'pengajuan_id' => $pengajuanData->id]);
        $approve2User = Where('users', ['id' => $approve2['user_id']]);
        $approve2Jabatan = Where('jabatan', ['id' => $approve2User['jabatan_id']]);
        $approval2['signature'] = $approve2['signature'];
        $approval2['user'] = $approve2User;
        $approval2['jabatan'] = $approve2Jabatan;

        $approve3 = Where('approval_pengajuan', ['urut' => '3', 'pengajuan_id' => $pengajuanData->id]);
        $approve3User = Where('users', ['id' => $approve3['user_id']]);
        $approve3Jabatan = Where('jabatan', ['id' => $approve3User['jabatan_id']]);
        $approval3['signature'] = $approve3['signature'];
        $approval3['user'] = $approve3User;
        $approval3['jabatan'] = $approve3Jabatan;

        $jmlCuti['cuti_besar'] = $this->db->table('pengajuan')->select('SUM(lama) jml')->where(['jenis_cuti' => '1', 'approval' => '1', 'user_id' => session('userId')])->get()->getRow()->jml ?? '-';
        $jmlCuti['cuti_sakit'] = $this->db->table('pengajuan')->select('SUM(lama) jml')->where(['jenis_cuti' => '2', 'approval' => '1', 'user_id' => session('userId')])->get()->getRow()->jml ?? '-';
        $jmlCuti['cuti_melahirkan'] = $this->db->table('pengajuan')->select('SUM(lama) jml')->where(['jenis_cuti' => '3', 'approval' => '1', 'user_id' => session('userId')])->get()->getRow()->jml ?? '-';
        $jmlCuti['cuti_alasan_penting'] = $this->db->table('pengajuan')->select('SUM(lama) jml')->where(['jenis_cuti' => '4', 'approval' => '1', 'user_id' => session('userId')])->get()->getRow()->jml ?? '-';
        $jmlCuti['cuti_tanggungan'] = $this->db->table('pengajuan')->select('SUM(lama) jml')->where(['jenis_cuti' => '5', 'approval' => '1', 'user_id' => session('userId')])->get()->getRow()->jml ?? '-';

        $days = count(getBetweenDates($pengajuanData->tahun_masuk, date('Y-m-d')));

        $start_date = new \DateTime();
        $end_date = (new $start_date)->add(new \DateInterval("P{$days}D"));
        $dd = date_diff($start_date, $end_date);
        $masaKerja = $dd->y . " Tahun " . $dd->m . " Bulan ";

        $nomorPengajuan = '0000';
        $nomorPengajuan = substr($nomorPengajuan, 0, strlen($nomorPengajuan) - strlen($pengajuanData->id)) . $pengajuanData->id;


        // Print_($pengajuanData, false, false);
        // Print_($jmlCuti, false, false);
        // Print_($masaKerja, false, false);
        // Print_($approval2, false, false);
        // Print_($approval3, false, false);
        $data = [
            'title' => "Print Pengajuan $nama",
            'data' => $pengajuanData,
            'jabatan' => $jabatan,
            'nomorPengajuan' => $nomorPengajuan,
            'masaKerja' => $masaKerja,
            'unitKerja' => $unitKerja,
            'approval2' => $approval2,
            'approval3' => $approval3,
            'jmlCuti' => $jmlCuti
        ];

        if ($pengajuanData->approval != '1') return "Belum tersedia";
        return View('admin/pengajuan/vPrint', $data);
    }

    public function viewPengajuanApproval($id)
    {
        $pengajuanData = $this->db->table('approval_pengajuan ap')
            ->select('u.nama, u.nip, pe.id id_pengajuan')
            ->join('pengajuan pe', 'pe.id = ap.pengajuan_id')
            ->join('users u', 'u.id = pe.user_id')
            ->where([EncKey('ap.id') => $id])->get()->getRow();
        // Print_($pengajuanData, true);
        $nama = $pengajuanData->nama ?? "-";
        $nip = $pengajuanData->nip ?? "-";
        $allowAcc = $this->db->table('approval_pengajuan')
            ->select('id, user_id')
            ->orderBy('urut', 'asc')
            ->where('status', '0')
            ->where('pengajuan_id', $pengajuanData->id_pengajuan)
            ->get()->getRow();
        $data = [
            'title' => "Pengajuan $nama",
            'menu' => 'cuti',
            'subMenu' => 'approval',
            'roti' => [
                'App' => '',
                'Dashboard' => base_url(ADMIN_PATH . '/dashboard'),
                'Pengajuan' => '',
                'Approval' => base_url(ADMIN_PATH . '/pengajuan-cuti/approval'),
                "$nip - $nama:active" => '',
            ],
            'pengajuanId' => Enc($pengajuanData->id_pengajuan),
            'approvalId' => $id,
            'nama' => $nama,
            'nip' => $nip,
            'allowAcc' => $allowAcc
        ];

        return View('admin/pengajuan/vViewApproval', $data);
    }

    public function signPengajuanApproval($id)
    {
        $pengajuanData = $this->db->table('approval_pengajuan ap')
            ->select('u.nama, u.nip, pe.id id_pengajuan')
            ->join('pengajuan pe', 'pe.id = ap.pengajuan_id')
            ->join('users u', 'u.id = pe.user_id')
            ->where([EncKey('ap.id') => $id])->get()->getRow();
        // Print_($pengajuanData, true);
        $nama = $pengajuanData->nama ?? "-";
        $nip = $pengajuanData->nip ?? "-";
        $data = [
            'title' => "Pengajuan $nama",
            'menu' => 'cuti',
            'subMenu' => 'approval',
            'roti' => [
                'App' => '',
                'Dashboard' => base_url(ADMIN_PATH . '/dashboard'),
                'Pengajuan' => '',
                'Approval' => base_url(ADMIN_PATH . '/pengajuan-cuti/approval'),
                "$nip - $nama:active" => '',
            ],
            'pengajuanId' => Enc($pengajuanData->id_pengajuan),
            'approvalId' => $id,
            'nama' => $nama,
            'nip' => $nip
        ];

        return View('admin/pengajuan/vSign', $data);
    }

    public function approvalSet()
    {
        try {
            if (!Update('approval_pengajuan', ['status' => Input_('status'), 'signature' => Input_('signature'), 'alasan' => Input_('reason')], [EncKey('id') => Input_('approval')])) throw new \Exception("Gagal merubah status !", 1);
            $idPengajuan = $this->db->table('approval_pengajuan')->where([EncKey('id') => Input_('approval')])->get()->getRow()->pengajuan_id;
            if (Input_('status') != '1') {
                // Update('approval_pengajuan', ['status' => Input_('status')], ['pengajuan_id' => $idPengajuan]);
                $cuti = $this->db->table('pengajuan')->select('lama, jenis_cuti, user_id')->where(['id' => $idPengajuan])->get()->getRow();
                if ($cuti->jenis_cuti == '0') {
                    $tempUser = (object) Where('users', ['id' => $cuti->user_id]);
                    $sisaJatahCuti = $tempUser->cuti_tahun_jatah;
                    $total = $sisaJatahCuti + $cuti->lama;
                    Update('users', ['cuti_tahun_jatah' => $total], ['id' => $cuti->user_id]);
                }
                Update('pengajuan', ['approval' => Input_('status')], ['id' => $idPengajuan]);
                $this->db->table('approval_pengajuan')->set(['status' => Input_('status')])->where(['pengajuan_id' => $idPengajuan])->update();
            }

            if (count($this->db->table('approval_pengajuan')->where(['pengajuan_id' => $idPengajuan, 'status' => '1'])->get()->getResult()) == 3) {
                Update('pengajuan', ['approval' => '1'], ['id' => $idPengajuan]);
            }

            $response = [
                'status' => 'ok',
                'message' => 'Berhasil merubah status'
            ];
        } catch (\Throwable | \Exception $error) {
            $response = [
                'status' => 'fail',
                'message' => $error->getMessage()
            ];
        } finally {
            echo json_encode($response);
        }
    }
}