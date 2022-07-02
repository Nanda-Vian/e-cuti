<?= $this->extend('admin/layouts/app'); ?>

<?= $this->section('content'); ?>

<div class="container-fluid pb-3">
    <?php if (session('level') == '2') : ?>
    <div class="card shadow border-0">
        <div class="card-body">
            <h5 class="m-0">Selamat datang <?= session('nama') ?></h5>
        </div>
    </div>
    <?php endif; ?>
    <?php if (session('level') != '2') : ?>
    <div class="card shadow">
        <div class="card-body">
            <h5 class="m-0">Selamat datang <?= session('nama') ?></h5>
        </div>
    </div>
    <div class="row">

        <?php foreach ($cuti as $key) : ?>

        <div class="col-lg-2 col-md-3 col-xs-12">

            <div class="card shadow ">
                <div class="card-header text-center">
                    <?= $key['nama'] ?>
                </div>

                <div class="card-body text-center">
                    <h1><?= $key['jumlah'] ?></h2>
                        <p>Hari</p>
                </div>
            </div>

        </div>

        <?php endforeach; ?>
    </div>
    <div class="row">
        <div class="card shadow mb-0">
            <div class="card-body">
                <div class="table-responsive">
                    <div class="float-right ml-3">
                    </div>
                    <table id="listPengajuan" class="table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama / NIP</th>
                                <th>Jenis / Lama Cuti</th>
                                <th>Tgl Mulai / Selesai</th>
                                <th>File Lampiran</th>
                                <th>Status Pengajuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Nama / NIP</th>
                                <th>Jenis / Lama Cuti</th>
                                <th>Tgl Mulai / Selesai</th>
                                <th>File Lampiran</th>
                                <th>Status Pengajuan</th>
                                <th>Aksi</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection(); ?>
<?= $this->section('js'); ?>
<script src="<?= base_url('assets/js/page/admin/dashboard.js') ?>" defer></script>
<?= $this->endSection(); ?>