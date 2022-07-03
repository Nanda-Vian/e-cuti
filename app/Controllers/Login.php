<?php

namespace App\Controllers;

class Login extends BaseController
{
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->table = 'users';
    }

    public function index()
    {
        if (session('userId')) return redirect()->to(ADMIN_PATH);
        return view('login', [
            'title' => 'Login',
        ]);
    }

    public function action()
    {
        try {
            $validate = Validate([
                'nrk' => 'required|min:6|max:6|number',
                'password' => 'required',
            ], [
                'password' => Enc(Input_('password')),
            ]);

            // cek apakah Validator success atau tidak
            if (!$validate['success']) {
                throw new \Exception('Error Processing Request');
            }
            // cek apakah user ada atau tidak
            // Print_($validate['data']);
            $user = $this->db->table($this->table)->where($validate['data']);
            $userData = $user->get()->getRow();
            if (empty($userData)) {
                throw new \Exception('NRK atau password salah !');
            }
            // Print_($userData);
            // cek apakah user aktif atau tidak
            // $userData = $builder->where($validate['data'])->get()->getRow();
            if ($userData->active == 0) {
                throw new \Exception('Akun tidak aktif, tidak dapat melanjutkan');
            }
            $session = [
                'userId' => $userData->id,
                'isAdmin' => $userData->level == '2' ? true : false,
                'nama' => $userData->nama,
                'email' => $userData->email,
                'nrk' => $userData->nrk,
                'level' => $userData->level,
                'unitKerja' => $userData->unit_kerja_id,
            ];

            cekTahun($userData->id);

            // Update($this->table, ['token' => $token], ['id' => $userData->id]);

            session()->set($session);

            $message = [
                'status' => 'ok',
                'message' => "Selamat datang $userData->nama",
            ];
        } catch (\Throwable $th) {
            $message = [
                'status' => 'fail',
                'message' => $th->getMessage(),
            ];
        } catch (\Exception $ex) {
            $message = [
                'status' => 'fail',
                'message' => $ex->getMessage(),
            ];
        } finally {
            $message = array_merge($message, ['validate' => $validate]);
            echo json_encode($message);
        }
    }

    public function destroy()
    {
        try {
            $token = Input_('_token');
            if (session('token') != $token) {
                throw new \Exception('invalid token');
            }
            session()->destroy();

            $message = [
                'status' => 'ok',
                'message' => 'Session destroyed',
            ];
        } catch (\Exception $ex) {
            $message = [
                'status' => 'fail',
                'message' => $ex->getMessage(),
            ];
        } catch (\Throwable $th) {
            $message = [
                'status' => 'fail',
                'message' => $th->getMessage(),
            ];
        } finally {
            echo json_encode($message);
        }
    }
}