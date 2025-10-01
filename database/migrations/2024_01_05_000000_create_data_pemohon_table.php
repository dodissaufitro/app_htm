<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_pemohon', function (Blueprint $table) {
            $table->id();
            $table->string('id_pendaftaran', 255)->unique();
            $table->string('username', 100);
            $table->char('nik', 16)->nullable();
            $table->char('kk', 16)->nullable();
            $table->char('nama', 100)->nullable();
            $table->char('pendidikan', 100)->nullable();
            $table->char('npwp', 100)->nullable();
            $table->string('nama_npwp', 255)->nullable();
            $table->integer('validasi_npwp')->default(0);
            $table->integer('status_npwp')->default(0);
            $table->char('no_hp', 100)->nullable();
            $table->char('chkDomisili', 100)->nullable();
            $table->char('provinsi2_ktp', 100)->nullable();
            $table->char('kabupaten_ktp', 100)->nullable();
            $table->char('kecamatan_ktp', 100)->nullable();
            $table->char('kelurahan_ktp', 100)->nullable();
            $table->char('provinsi_dom', 100)->nullable();
            $table->char('kabupaten_dom', 100)->nullable();
            $table->char('kecamatan_dom', 100)->nullable();
            $table->char('kelurahan_dom', 100)->nullable();
            $table->char('alamat_dom', 100)->nullable();
            $table->char('sts_rumah', 100)->nullable();
            $table->char('korespondensi', 1)->nullable();
            $table->char('pekerjaan', 100)->nullable();
            $table->decimal('gaji', 15, 2)->nullable();
            $table->integer('status_kawin')->nullable()->default(0);
            $table->char('nik2', 100)->nullable();
            $table->char('nama2', 100)->nullable();
            $table->char('no_hp2', 100)->nullable();
            $table->boolean('is_couple_dki')->nullable();
            $table->boolean('is_have_booking_kpr_dpnol')->nullable();
            $table->string('tipe_unit', 255)->nullable();
            $table->decimal('harga_unit', 15, 2)->nullable();
            $table->char('chkDomisili2', 100)->nullable();
            $table->char('provinsi2', 100)->nullable();
            $table->char('kabupaten2', 100)->nullable();
            $table->char('kecamatan2', 100)->nullable();
            $table->char('kelurahan2', 100)->nullable();
            $table->string('alamat2', 255)->nullable();
            $table->char('pendidikan2', 100)->nullable();
            $table->char('pekerjaan2', 100)->nullable();
            $table->decimal('gaji2', 15, 2)->nullable();
            $table->char('chkPengajuan', 100)->nullable()->default('on');
            $table->char('foto_ektp', 100)->nullable();
            $table->char('foto_npwp', 100)->nullable();
            $table->char('foto_kk', 100)->nullable();
            $table->string('lokasi_rumah', 255)->nullable();
            $table->string('tipe_rumah', 255)->nullable();
            $table->string('nama_blok', 255)->nullable();
            $table->text('bapenda')->nullable();
            $table->text('bapenda_pasangan');
            $table->text('bapenda_pasangan_pbb');
            $table->text('reason_of_choose_location')->nullable();
            $table->text('aset_hunian')->nullable();
            $table->longText('booking_files')->nullable();
            $table->smallInteger('count_of_vehicle1')->nullable();
            $table->smallInteger('count_of_vehicle2')->nullable();
            $table->boolean('is_have_saving_bank')->nullable();
            $table->boolean('is_have_home_credit')->nullable();
            $table->integer('atpid')->nullable();
            $table->decimal('mounthly_expense1', 20, 0)->nullable();
            $table->decimal('mounthly_expense2', 20, 0)->nullable();
            $table->string('status_permohonan', 255);
            $table->string('id_bank', 32)->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('created_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_pemohon');
    }
};
