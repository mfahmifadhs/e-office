<!DOCTYPE html>
<html lang="en">

@foreach($usulan as $dataUsulan)

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $dataUsulan->id_form_usulan }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('dist_admin/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist_admin/css/adminlte.min.css') }}">
</head>

<body>
    <div class="">
        <div class="row">
            <div class="col-md-2">
                <h2 class="page-header ml-4">
                    <img src="{{ asset('dist_admin/img/logo-kemenkes-icon.png') }}">
                </h2>
            </div>
            <div class="col-md-8 text-center">
                <h2 class="page-header">
                    <h5 style="font-size: 30px;text-transform:uppercase;"><b>kementerian kesehatan republik indonesia</b></h5>
                    <h5 style="font-size: 30px;text-transform:uppercase;"><b>{{ $dataUsulan->unit_utama }}</b></h5>
                    <p style="font-size: 20px;"><i>Jl. H.R. Rasuna Said Blok X.5 Kav. 4-9, Blok A, 2nd Floor, Jakarta 12950<br>Telp.: (62-21) 5201587, 5201591 Fax. (62-21) 5201591</i></p>
                </h2>
            </div>
            <div class="col-md-2">
                <h2 class="page-header">
                    <img src="{{ asset('dist_admin/img/logo-germas.png') }}" style="width: 128px; height: 128px;">
                </h2>
            </div>
            <div class="col-md-12">
                <hr style="border-width: medium;border-color: black;">
            </div>
        </div>
        <div class="row text-capitalize" style="font-size: 22px;">
            <div class="col-md-12 form-group">
                <div class="form-group row mb-0">
                    <div class="col-md-2">Nomor Surat</div>
                    <div class="col-md-10 text-uppercase">: {{ $dataUsulan->no_surat_usulan }}</div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-md-2">Pengusul</div>
                    <div class="col-md-10">: {{ ucfirst(strtolower($dataUsulan->nama_pegawai)) }}</div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-md-2">Jabatan</div>
                    <div class="col-md-9">: {{ ucfirst(strtolower($dataUsulan->keterangan_pegawai)) }}</div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-md-2">Unit Kerja</div>
                    <div class="col-md-9">: {{ ucfirst(strtolower($dataUsulan->unit_kerja)) }}</div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-md-2">Tanggal Usulan</div>
                    <div class="col-md-9">: {{ \Carbon\Carbon::parse($dataUsulan->tanggal_usulan)->isoFormat('DD MMMM Y') }}</div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-md-2">Total Pengajuan</div>
                    <div class="col-md-9">: {{ $dataUsulan->total_pengajuan }} kendaraan</div>
                </div>
                @if($dataUsulan->rencana_pengguna != null)
                <div class="form-group row mb-0">
                    <div class="col-md-2">Rencana Pengguna</div>
                    <div class="col-md-9">: {{ $dataUsulan->rencana_pengguna }}</div>
                </div>
                @endif
            </div>
            <div class="col-12 table-responsive mt-4 mb-5">
                @if($dataUsulan->jenis_form == '1')
                <table class="table table-bordered m-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis AADB</th>
                            <th>Jenis Kendaraan</th>
                            <th>Merk/Tipe</th>
                            <th>Tahun Perolehan</th>
                        </tr>
                    </thead>
                    <?php $no = 1; ?>
                    <tbody>
                        @foreach($dataUsulan->usulanKendaraan as $dataKendaraan)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $dataKendaraan->jenis_aadb }}</td>
                            <td>{{ ucfirst(strtolower($dataKendaraan->jenis_kendaraan)) }}</td>
                            <td>{{ $dataKendaraan->merk_tipe_kendaraan }}</td>
                            <td>{{ $dataKendaraan->tahun_kendaraan }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @elseif($dataUsulan->jenis_form == '2')
                <table class="table table-bordered m-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kendaraan</th>
                            <th>Kilometer Terakhir</th>
                            <th>Tanggal Servis Terakhir</th>
                            <th>Jatuh Tempo Servis</th>
                            <th>Tanggal Ganti Oli Terakhir</th>
                            <th>Jatuh Tempo Ganti Oli</th>
                        </tr>
                    </thead>
                    <?php $no = 1; ?>
                    <tbody>
                        @foreach($dataUsulan->usulanServis as $dataServis)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $dataServis->merk_tipe_kendaraan }}</td>
                            <td>{{ $dataServis->kilometer_terakhir }}</td>
                            <td>{{ $dataServis->tgl_servis_terakhir }}</td>
                            <td>{{ $dataServis->jatuh_tempo_servis }}</td>
                            <td>{{ $dataServis->tgl_ganti_oli_terakhir }}</td>
                            <td>{{ $dataServis->jatuh_tempo_ganti_oli }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @elseif($dataUsulan->jenis_form == '3')
                <table class="table table-bordered m-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kendaraan</th>
                            <th>No. Plat</th>
                            <th>Masa Berlaku STNK</th>
                        </tr>
                    </thead>
                    <?php $no = 1; ?>
                    <tbody>
                        @foreach($dataUsulan->usulanSTNK as $dataSTNK)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $dataSTNK->merk_tipe_kendaraan }}</td>
                            <td>{{ $dataSTNK->no_plat_kendaraan }}</td>
                            <td>{{ \Carbon\Carbon::parse($dataSTNK->mb_stnk_lama)->isoFormat('DD MMMM Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @elseif($dataUsulan->jenis_form == '4')
                <table class="table table-bordered m-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kendaraan</th>
                            <th>Voucher 25</th>
                            <th>Voucher 50</th>
                            <th>Voucher 100</th>
                            <th>Total</th>
                            <th>Bulan Pengadaan</th>
                        </tr>
                    </thead>
                    <?php $no = 1; ?>
                    <tbody>
                        @foreach($dataUsulan->usulanVoucher as $dataVoucher)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $dataVoucher->merk_tipe_kendaraan }}</td>
                            <td>{{ $dataVoucher->voucher_25 }}</td>
                            <td>{{ $dataVoucher->voucher_50 }}</td>
                            <td>{{ $dataVoucher->voucher_100 }}</td>
                            <td>Rp {{ number_format($dataVoucher->total_biaya, 0, ',', '.') }}</td>
                            <td>{{ \Carbon\Carbon::parse($dataVoucher->bulan_pengadaan)->isoFormat('MMMM Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            <div class="col-md-12">
                <div class="row text-center">
                    <label class="col-sm-6">Yang Mengusulkan, <br> {{ ucfirst(strtolower($dataUsulan->keterangan_pegawai)) }}</label>
                    @if ($dataUsulan->otp_usulan_kabag != null)
                    <label class="col-sm-6">Disetujui Oleh, <br> {{ ucfirst(strtolower($pimpinan->keterangan_pegawai)) }}</label>
                    @endif
                </div>
            </div>
            <div class="col-md-12 mt-4">
                <div class="row text-center">
                    <label class="col-sm-6">{!! QrCode::size(100)->generate('https://siporsat.app/surat/usulan-aadb/'.$dataUsulan->otp_usulan_pengusul) !!}</label>
                    @if ($dataUsulan->otp_usulan_kabag != null)
                    <label class="col-sm-6">{!! QrCode::size(100)->generate('https://siporsat.app/surat/usulan-aadb/'.$dataUsulan->otp_usulan_kabag) !!}</label>
                    @endif
                </div>
            </div>
            <div class="col-md-12 mt-4">
                <div class="row text-center">
                    <label class="col-sm-6">{{ ucfirst(strtolower($dataUsulan->nama_pegawai)) }}</label>
                    @if ($dataUsulan->otp_usulan_kabag != null)
                    <label class="col-sm-6">{{ ucfirst(strtolower($pimpinan->nama_pegawai)) }}</label>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- ./wrapper -->
    <!-- Page specific script -->
    <script>
        window.addEventListener("load", window.print());
    </script>
</body>

</html>
@endforeach
