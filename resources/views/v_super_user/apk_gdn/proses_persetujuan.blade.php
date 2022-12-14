@extends('v_super_user.layout.app')

@section('content')

<div class="content-header">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active"><a href="{{ url('super-user/gdn/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active"><a href="{{ url('super-user/gdn/usulan/daftar/seluruh-usulan') }}">Daftar Usulan</a></li>
                    <li class="breadcrumb-item active">Proses Persetujuan Usulan Gedung/Bangunan</li>
                </ol>
            </div>
        </div>
    </div>
</div>


<section class="content">
    <div class="container">
        <div class="col-md-12 form-group">
            @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p style="color:white;margin: auto;">{{ $message }}</p>
            </div>
            @elseif ($message = Session::get('failed'))
            <div class="alert alert-danger">
                <p style="color:white;margin: auto;">{{ $message }}</p>
            </div>
            @endif
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Persetujuan Usulan ATK </h3>
            </div>
            <div class="card-body">
                <form action="{{ url('super-user/gdn/usulan/proses-diterima/'. $usulan->id_form_usulan) }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">No. Surat Usulan</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control text-uppercase" value="{{ $usulan->no_surat_usulan }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Pengusul</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" value="{{ $usulan->nama_pegawai }}" readonly>
                        </div>
                        <label class="col-sm-2 col-form-label">Jabatan</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" value="{{ $usulan->keterangan_pegawai }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Tanggal Usulan</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" value="{{ $usulan->tanggal_usulan }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Bulan Pengadaan</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($usulan->tanggal_usulan)->isoFormat('MMMM Y') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Jumlah Barang</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $usulan->total_pengajuan }} Barang" readonly>
                        </div>
                    </div>
                    <div class="form-group row mt-4">
                        <label class="col-sm-2 col-form-label">Informasi Barang</label>
                        <div class="col-sm-10">
                            <table class="table table-bordered text-center">
                                <thead class="bg-secondary">
                                    <tr>
                                        <th>Lokasi Perbaikan</th>
                                        <th>Bidang Kerusakan</th>
                                        <th style="width: 20%;">Keterangan</th>
                                    </tr>
                                </thead>
                                <?php $no = 1; ?>
                                <tbody>
                                    @foreach($usulan->detailUsulanGdn as $dataPerbaikan)
                                    <tr>
                                        <td class="text-uppercase">{{ $dataPerbaikan->lokasi_bangunan.' / '.$dataPerbaikan->lokasi_spesifik }}</td>
                                        <td>{{ $dataPerbaikan->bid_kerusakan }}</td>
                                        <td>{{ $dataPerbaikan->keterangan }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">&nbsp;</label>
                        <div class="col-sm-10">
                            <button class="btn btn-success" id="btnSubmit" onclick="return confirm('Pengajuan Diterima ?')">
                                <i class="fas fa-check-circle"></i> Terima
                            </button>
                            <a href="{{ url('super-user/aadb/usulan/proses-ditolak/'. $usulan->id_form_usulan) }}" class="btn btn-danger" onclick="return confirm('Pengajuan Ditolak ?')">
                                <i class="fas fa-times-circle"></i> Tolak
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection
