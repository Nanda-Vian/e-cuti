<?= $this->extend('admin/layouts/app'); ?>

<?= $this->section('content') ?>
<div class="container-fluid pb-3">
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
                            <th>Approval</th>
                            <th>Status Pengajuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    </tbody>
                    <!-- <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Nama / NIP</th>
                            <th>Jenis / Lama Cuti</th>
                            <th>Tgl Mulai / Selesai</th>
                            <th>Approval</th>
                            <th>Status Pengajuan</th>
                            <th>Aksi</th>
                        </tr>
                    </tfoot> -->
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script src="<?= base_url('assets/js/page/admin/pengajuanApproval.js') ?>" defer></script>
<?= $this->endSection() ?>