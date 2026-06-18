<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambah field master data karyawan sesuai FORMAT DATABASE ENLULU (5 bagian).
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // A. BIODATA
            $table->string('tempat_lahir')->nullable()->after('nama_lengkap')->comment('Tempat Lahir');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir')->comment('Tanggal Lahir');
            $table->string('jenis_kelamin')->nullable()->after('tanggal_lahir')->comment('Pria / Wanita');
            $table->string('agama')->nullable()->after('jenis_kelamin')->comment('Agama');
            $table->string('pendidikan')->nullable()->after('agama')->comment('Pendidikan terakhir');
            $table->string('status_pernikahan')->nullable()->after('pendidikan')->comment('Single / Menikah / Duda / Janda');
            $table->unsignedSmallInteger('jumlah_anak')->nullable()->after('status_pernikahan')->comment('Jumlah Anak');
            $table->text('alamat')->nullable()->after('jumlah_anak')->comment('Alamat Tinggal');
            $table->string('kelurahan')->nullable()->after('alamat')->comment('Kelurahan');
            $table->string('kecamatan')->nullable()->after('kelurahan')->comment('Kecamatan');
            $table->string('kota')->nullable()->after('kecamatan')->comment('Kota/Kabupaten');
            $table->string('propinsi')->nullable()->after('kota')->comment('Propinsi');
            $table->string('status_tempat_tinggal')->nullable()->after('propinsi')->comment('Rumah sendiri / Sewa');
            $table->string('no_hp')->nullable()->after('status_tempat_tinggal')->comment('No. HP');
            $table->string('no_kk')->nullable()->after('no_hp')->comment('No. KK');
            $table->string('email')->nullable()->after('no_kk')->comment('e-mail');
            $table->string('no_bpjs_tk')->nullable()->after('email')->comment('No. BPJS Ketenagakerjaan');
            $table->string('no_bpjs_kesehatan')->nullable()->after('no_bpjs_tk')->comment('No. BPJS Kesehatan');
            $table->text('keterangan_biodata')->nullable()->after('no_bpjs_kesehatan')->comment('Keterangan Lain Biodata');

            // B. EMERGENCY CONTACT (Tidak Serumah)
            $table->string('ec_nama')->nullable()->after('keterangan_biodata')->comment('Emergency Contact: Nama');
            $table->string('ec_alamat')->nullable()->after('ec_nama')->comment('Emergency Contact: Tempat Tinggal');
            $table->string('ec_no_hp')->nullable()->after('ec_alamat')->comment('Emergency Contact: No. HP');
            $table->string('ec_hubungan')->nullable()->after('ec_no_hp')->comment('Emergency Contact: Hubungan');

            // D. FOTO WAJAH
            $table->string('foto')->nullable()->after('nama_di_rekening')->comment('Path foto wajah');

            // E. PENEMPATAN KERJA
            $table->string('nama_customer')->nullable()->after('kode_vendor')->comment('Nama Customer');
            $table->date('tanggal_masuk')->nullable()->after('area_kerja')->comment('Tanggal Masuk');
            $table->date('tanggal_keluar')->nullable()->after('tanggal_masuk')->comment('Tanggal Keluar');
            $table->date('tanggal_perpanjangan_terakhir')->nullable()->after('tanggal_keluar')->comment('Tanggal Perpanjangan Terakhir');
            $table->text('keterangan_perpanjangan')->nullable()->after('tanggal_perpanjangan_terakhir')->comment('Keterangan Perpanjangan');
            $table->string('no_pks_masuk')->nullable()->after('keterangan_perpanjangan')->comment('No. PKS Masuk');
            $table->string('no_pks_perpanjangan')->nullable()->after('no_pks_masuk')->comment('No. PKS Perpanjangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'pendidikan',
                'status_pernikahan', 'jumlah_anak', 'alamat', 'kelurahan', 'kecamatan',
                'kota', 'propinsi', 'status_tempat_tinggal', 'no_hp', 'no_kk', 'email',
                'no_bpjs_tk', 'no_bpjs_kesehatan', 'keterangan_biodata',
                'ec_nama', 'ec_alamat', 'ec_no_hp', 'ec_hubungan',
                'foto',
                'nama_customer', 'tanggal_masuk', 'tanggal_keluar', 'tanggal_perpanjangan_terakhir',
                'keterangan_perpanjangan', 'no_pks_masuk', 'no_pks_perpanjangan',
            ]);
        });
    }
};
