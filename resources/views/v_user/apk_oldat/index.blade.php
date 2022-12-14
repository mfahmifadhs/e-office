@extends('v_user.layout.app')

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0 ml-2">PEMELIHARAAN OLAH DATA & MEUBELAIR</h4>
            </div>
        </div>
    </div>
</div>

<section class="content text-capitalize">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 col-12 form-group">
                @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <p class="fw-light" style="margin: auto;">{{ $message }}</p>
                </div>
                @endif
                @if ($message = Session::get('failed'))
                <div class="alert alert-danger">
                    <p class="fw-light" style="margin: auto;">{{ $message }}</p>
                </div>
                @endif
            </div>
            <div class="col-md-3 col-12">
                <div class="form-group">
                    <a href="{{ url('unit-kerja/oldat/usulan/perbaikan/baru') }}" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-tools fa-2x"></i> <br>
                        Usulan <br> Perbaikan
                    </a>
                </div>
                <div class="form-group">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><small class="font-weight-bold">Usulan Perbaikan</small></h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-default btn-xs" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 form-group small">Menunggu Persetujuan</div>
                                <div class="col-md-4 form-group small text-right">{{ $usulan->where('jenis_form','perbaikan')->where('status_proses_id', 1)->count() }}</div>
                                <div class="col-md-12">
                                    <hr style="border: 1px solid grey;margin-top:-1vh;">
                                </div>
                                <div class="col-md-8 form-group small">Sedang Diproses</div>
                                <div class="col-md-4 form-group small text-right">{{ $usulan->where('jenis_form','perbaikan')->where('status_proses_id', 2)->count() }}</div>
                                <div class="col-md-12">
                                    <hr style="border: 1px solid grey;margin-top:-1vh;">
                                </div>
                                <div class="col-md-8 form-group small">Menunggu Konfirmasi</div>
                                <div class="col-md-4 form-group small text-right">{{ $usulan->where('jenis_form','perbaikan')->where('status_proses_id', 4)->count() }}</div>
                                <div class="col-md-12">
                                    <hr style="border: 1px solid grey;margin-top:-1vh;">
                                </div>
                                <div class="col-md-8 form-group small">Selesai</div>
                                <div class="col-md-4 form-group small text-right">{{ $usulan->where('jenis_form','perbaikan')->where('status_proses_id', 5)->count() }}</div>
                                <div class="col-md-12">
                                    <hr style="border: 1px solid grey;margin-top:-1vh;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><small class="font-weight-bold">Daftar Usulan</small></h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-default btn-sm" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="table-usulan" class="table table-bordered m-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Pengusul</th>
                                    <th>Usulan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <?php $no = 1;
                            $no2 = 1; ?>
                            <tbody>
                                @foreach($usulan as $dataUsulan)
                                <tr>
                                    <td class="text-center pt-3">{{ $no++ }}</td>
                                    <td class="small">
                                        {{ \Carbon\Carbon::parse($dataUsulan->tanggal_usulan)->isoFormat('DD MMMM Y') }} <br>
                                        No. Surat : {{ $dataUsulan->no_surat_usulan }} <br>
                                        Pengusul : {{ ucfirst(strtolower($dataUsulan->nama_pegawai)) }} <br>
                                        Unit Kerja : {{ ucfirst(strtolower($dataUsulan->unit_kerja)) }}
                                    </td>
                                    <td class="small text-capitalize">
                                        @if ($dataUsulan->jenis_form == 'perbaikan')
                                        @foreach($dataUsulan->detailPerbaikan as $dataPerbaikan)
                                        Kode Barang : {{ $dataPerbaikan->kode_barang.'.'.$dataPerbaikan->nup_barang }}<br>
                                        Nama Barang : {{ $dataPerbaikan->kategori_barang}}<br>
                                        Merk / Tipe : {{ $dataPerbaikan->merk_tipe_barang }}<br>
                                        Keterangan Perbaikan : <br> {{ $dataPerbaikan->keterangan_perbaikan }}<br>
                                        @endforeach
                                        @endif
                                    </td>
                                    <td class="pt-2 small">
                                        Status Pengajuan : <br>
                                        @if($dataUsulan->status_pengajuan_id == 1)
                                        <span class="badge badge-sm badge-pill badge-success">disetujui</span>
                                        @elseif($dataUsulan->status_pengajuan_id == 2)
                                        <span class="badge badge-sm badge-pill badge-danger">ditolak</span>
                                        @endif <br>
                                        Status Proses : <br>
                                        @if($dataUsulan->status_proses_id == 1)
                                        <span class="badge badge-sm badge-pill badge-warning">menunggu <br> persetujuan</span>
                                        @elseif ($dataUsulan->status_proses_id == 2)
                                        <span class="badge badge-sm badge-pill badge-warning">sedang <br> diproses ppk</span>
                                        @elseif ($dataUsulan->status_proses_id == 3)
                                        <span class="badge badge-sm badge-pill badge-warning">menunggu <br> konfirmasi pengusul</span>
                                        @elseif ($dataUsulan->status_proses_id == 4)
                                        <span class="badge badge-sm badge-pill badge-warning">sedang diproses <br> petugas gudang</span>
                                        @elseif ($dataUsulan->status_proses_id == 5)
                                        <span class="badge badge-sm badge-pill badge-success">selesai</span>
                                        @endif
                                    </td>
                                    <td class="text-center pt-4">
                                        <a type="button" class="btn btn-primary btn-sm" data-toggle="dropdown">
                                            <i class="fas fa-bars"></i>
                                        </a>
                                        <div class="dropdown-menu">
                                            @if ($dataUsulan->otp_usulan_pengusul != null)
                                            <a class="dropdown-item btn" href="{{ url('unit-kerja/surat/usulan-oldat/'. $dataUsulan->id_form_usulan) }}">
                                                <i class="fas fa-file"></i> Surat Usulan
                                            </a>
                                            @else
                                            <a class="dropdown-item btn" href="{{ url('unit-kerja/verif/usulan-oldat/'. $dataUsulan->id_form_usulan) }}">
                                                <i class="fas fa-file-signature"></i> Verifikasi
                                            </a>
                                            <a class="dropdown-item btn" href="{{ url('unit-kerja/oldat/usulan/proses-pembatalan/'. $dataUsulan->id_form_usulan) }}"
                                                onclick="return confirm('Apakah anda ingin membatalkan usulan ini ?')">
                                                <i class="fas fa-times-circle"></i> Batal
                                            </a>
                                            @endif
                                            @if ($dataUsulan->status_proses_id == 5)
                                            <a class="dropdown-item btn" href="{{ url('unit-kerja/surat/bast-oldat/'. $dataUsulan->id_form_usulan) }}">
                                                <i class="fas fa-file"></i> Surat Bast
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="content text-capitalize">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 col-12 form-group">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mt-1 font-weight-bold small">Filter Barang</h4>
                        <div class="card-tools">
                            <button type="button" class="btn btn-default btn-sm" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-sm-5">
                                <label>Nama Barang</label> <br>
                                <select name="barang" id="barang`+ i +`" class="form-control text-capitalize select2-1">
                                    <option value="">-- NAMA BARANG --</option>
                                </select>
                            </div>
                            <div class="col-sm-5">
                                <label>Kondisi</label> <br>
                                <select name="kondisi" id="kondisi`+ i +`" class="form-control text-capitalize select2-2">
                                    <option value="">-- KONDISI BARANG --</option>
                                </select>
                            </div>
                            <div class="col-sm-2 text-right">
                                <label>&nbsp;</label> <br>
                                <button id="searchChartData" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ url('unit-kerja/oldat/dashboard') }}" class="btn btn-danger">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div id="notif-konten-chart"></div>
            </div>
            <div class="col-md-4 col-12">
                <div class="card">
                    <div id="konten-chart-google-chart">
                        <div id="piechart" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-secondary loading" role="alert">
                            Sedang menyiapkan data ...
                        </div>
                        <table id="table-barang" class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>Id Barang</th>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Merk/Tipe</th>
                                    <th>Pengguna</th>
                                    <th>Kondisi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="small">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@section('js')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content')
    $(function() {
        $("#table-barang").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "Semua"]
            ],
            columnDefs: [{
                    targets: -1,
                    data: null,
                    defaultContent: `<a type="button" class="btn btn-primary" data-toggle="dropdown">
                                    <i class="fas fa-bars"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item btn btn-detail">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </a>
                                </div>`,
                },
                {
                    "bVisible": false,
                    "aTargets": [0]
                },
            ],
            order: [
                [1, 'asc']
            ],
            "bDestroy": true
        }).buttons().container().appendTo('#table-barang_wrapper .col-md-6:eq(0)');

        setTimeout(showTable(JSON.parse(`<?php echo $googleChartData; ?>`)), 1000);
    })

    let j = 0

    for (let i = 1; i <= 2; i++) {
        $(".select2-" + i).select2({
            ajax: {
                url: `{{ url('unit-kerja/oldat/select2-dashboard/` + i + `/barang') }}`,
                type: "post",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        _token: CSRF_TOKEN,
                        search: params.term // search term
                    };
                },
                processResults: function(response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }
        })
    }

    function showTable(data) {
        let dataTable = $('#table-barang').DataTable()
        console.log('start')
        let dataBarang = data.barang
        // console.log(dataBarang)

        dataTable.clear()
        let no = 1
        dataBarang.forEach(element => {
            dataTable.row.add([
                element.id_barang,
                no++,
                element.kategori_barang,
                element.barang,
                element.unit_kerja,
                element.kondisi_barang
            ])
        });
        dataTable.draw()
        $('.loading').hide()
        console.log('finish')
    }

    $('#table-barang tbody').on('click', '.btn-detail', function() {
        let dataTable = $('#table-barang').DataTable()
        let row = dataTable.row($(this).parents('tr')).data()
        // console.log(row)
        window.location.href = "/unit-kerja/oldat/barang/detail/" + row[0];
    })

    // =========================================================
    //                          CHART
    // =========================================================


    let chart
    let chartData = JSON.parse(`<?php echo $googleChartData; ?>`)
    let dataChart = chartData.all
    google.charts.load('current', {
        'packages': ['corechart']
    })
    google.charts.setOnLoadCallback(function() {
        drawChart(dataChart)
    })

    function drawChart(dataChart) {

        chartData = [
            ['Kategori Barang', 'Jumlah']
        ]
        console.log(dataChart)
        dataChart.forEach(data => {
            chartData.push(data)
        })

        var data = google.visualization.arrayToDataTable(chartData);

        var options = {
            title: 'Total Barang',
            legend: {
                'position': 'left',
                'alignment': 'center'
            },
        }

        chart = new google.visualization.PieChart(document.getElementById('piechart'));

        chart.draw(data, options);
    }

    $('body').on('click', '#searchChartData', function() {
        let barang = $('select[name="barang"').val()
        let kondisi = $('select[name="kondisi"').val()
        let url = ''

        $('.loading').show()
        let dataTable = $('#table-barang').DataTable()
        dataTable.clear()
        dataTable.draw()

        console.log(barang)
        if (barang || kondisi) {
            url =
                '<?= url("/unit-kerja/oldat/grafik?barang='+barang+'&kondisi='+kondisi+'") ?>'
        } else {
            url = '<?= url('/unit-kerja/oldat/grafik') ?>'
        }

        jQuery.ajax({
            url: url,
            type: "GET",
            success: function(res) {
                // console.log(res.message);
                let dataTable = $('#table-barang').DataTable()
                if (res.message == 'success') {
                    $('.notif-tidak-ditemukan').remove();
                    $('#konten-chart-google-chart').show();
                    let data = JSON.parse(res.data)
                    drawChart(data.chart)

                    dataTable.clear()
                    dataTable.draw()
                    let no = 1
                    console.log(res)
                    data.table.forEach(element => {
                        dataTable.row.add([
                            element.id_barang,
                            no++,
                            element.kategori_barang,
                            element.barang,
                            element.unit_kerja,
                            element.kondisi_barang
                        ])
                    });
                    dataTable.draw()
                    $('.loading').hide()

                } else {
                    dataTable.clear()
                    dataTable.draw()
                    $('.notif-tidak-ditemukan').remove();
                    $('#konten-chart-google-chart').hide();
                    var html = ''
                    html += '<div class="notif-tidak-ditemukan">'
                    html += '<div class="card bg-secondary py-4">'
                    html += '<div class="card-body text-white">'
                    html += '<h5 class="mb-4 font-weight-bold text-center">'
                    html += 'Data tidak dapat ditemukan'
                    html += '</h5>'
                    html += '</div>'
                    html += '</div>'
                    html += '</div>'
                    $('#notif-konten-chart').append(html)
                }
            },
        })
    })
</script>
@endsection

@endsection
