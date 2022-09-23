<?php

namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\BarangExport;
use App\Models\AADB\JenisKendaraan;
use App\Models\AADB\UsulanAadb;
use App\Models\AADB\UsulanKendaraan;
use App\Models\AADB\Kendaraan;
use App\Models\AADB\RiwayatKendaraan;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Pegawai;
use App\Models\KondisiBarang;
use App\Models\TimKerja;
use App\Models\FormUsulan;
use App\Models\FormUsulanPengadaan;
use App\Models\FormUsulanPerbaikan;
use App\Models\UnitKerja;
use App\Models\RiwayatBarang;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use DB;
use \PDF;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Carbon\Carbon;

class SuperUserController extends Controller
{
    public function index()
    {
        return view('v_super_user.index');
    }

    // ===============================================
    //                   AADB
    // ===============================================
    public function aadb(Request $request, $aksi)
    {
        if ($aksi == 'kendaraan') {
            $kendaraan = Kendaraan::join('aadb_tbl_jenis_kendaraan','id_jenis_kendaraan','jenis_kendaraan_id')
                ->join('aadb_tbl_kondisi_kendaraan','id_kondisi_kendaraan','kondisi_kendaraan_id')
                ->orderBy('jenis_aadb','ASC')
                ->get();
            return view('v_super_user.apk_aadb.daftar_laporan', compact('kendaraan'));

        } elseif ($aksi == 'usulan-pengadaan') {
            $pengajuan = UsulanAadb::join('aadb_tbl_jenis_form_usulan','id_jenis_form_usulan','jenis_form')
                ->join('tbl_pegawai','id_pegawai','pegawai_id')
                ->get();

            return view('v_super_user.apk_aadb.daftar_pengajuan', compact('pengajuan'));

        } elseif ($aksi == 'rekapitulasi') {
            $unitKerja      = UnitKerja::get();
            $jenisKendaraan = JenisKendaraan::get();
            $dataKendaraan  = Kendaraan::join('aadb_tbl_jenis_kendaraan','id_jenis_kendaraan','jenis_kendaraan_id')
                ->join('tbl_unit_kerja','id_unit_kerja','unit_kerja_id')
                 ->get();

            foreach ($unitKerja as $unker) {
                foreach($jenisKendaraan as $jenis) {
                    $rekapUnker[$unker->unit_kerja][$jenis->jenis_kendaraan] =
                        $dataKendaraan->where('unit_kerja', $unker->unit_kerja)->where('jenis_kendaraan', $jenis->jenis_kendaraan)->count();
                }
            }

            return view('v_super_user.apk_aadb.daftar_rekap', compact('unitKerja','jenisKendaraan','rekapUnker'));

        } else {
            $unitKerja      = UnitKerja::get();
            $jenisKendaraan = JenisKendaraan::get();
            $merk           = Kendaraan::select('merk_kendaraan')->groupBy('merk_kendaraan')->get();
            $tahun          = Kendaraan::select('tahun_kendaraan')->groupBy('tahun_kendaraan')->get();
            $pengguna       = Kendaraan::select('pengguna')->groupBy('pengguna')->get();
            return view('v_super_user.apk_aadb.index', compact('unitKerja','jenisKendaraan','merk','tahun','pengguna'));
        }
    }

    public function pengajuanAadb(Request $request, $aksi, $id)
    {
        if ($aksi == 'proses' && $id == 'pengadaan') {
            $idFormUsulan = Carbon::now()->format('dmy').$request->id_usulan_pengadaan;

            $usulan = new UsulanAadb();
            $usulan->id_form_usulan     = $idFormUsulan;
            $usulan->pegawai_id         = Auth::user()->pegawai_id;
            $usulan->kode_form          = 'AADB_001';
            $usulan->jenis_form         = 1;
            $usulan->total_pengajuan    = 1;
            $usulan->tanggal_usulan     = $request->tanggal_usulan;
            $usulan->rencana_pengguna   = $request->rencana_pengguna;
            $usulan->status_proses      = 'belum proses';
            $usulan->kode_otp_usulan    = $request->kode_otp_usulan;
            $usulan->save();

            $usulanPengadaan = new UsulanKendaraan();
            $usulanPengadaan->id_form_usulan_pengadaan  = $request->id_usulan_pengadaan;
            $usulanPengadaan->form_usulan_id            = $idFormUsulan;
            $usulanPengadaan->jenis_aadb                = $request->jenis_aadb;
            $usulanPengadaan->jenis_kendaraan_id        = $request->jenis_kendaraan;
            $usulanPengadaan->merk_kendaraan            = $request->merk_kendaraan;
            $usulanPengadaan->tipe_kendaraan            = $request->tipe_kendaraan;
            $usulanPengadaan->tahun_kendaraan           = $request->tahun_kendaraan;
            $usulanPengadaan->save();

            return redirect('super-user/aadb/usulan/surat/'. $idFormUsulan);

        } elseif ($aksi == 'surat') {

            if(Auth::user()->pegawai->unit_kerja_id == 1) {
                $pimpinan = Pegawai::join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->where('jabatan_id', '2')->where('unit_kerja_id',1)->first();
            } else {
                $pimpinan = null;
            }

            $usulan = UsulanAadb::with('usulanKendaraan')->get();
            return view('v_super_user/apk_aadb/surat_usulan', compact('pimpinan','usulan'));
        } else {
            $jenisKendaraan = JenisKendaraan::get();
            return view('v_super_user.apk_aadb.usulan', compact('aksi','jenisKendaraan'));
        }
    }

    public function kendaraan(Request $request, $aksi, $id)
    {
        if ($aksi == 'detail') {
            $kendaraan = Kendaraan::where('id_kendaraan', $id)
                ->join('aadb_tbl_jenis_kendaraan','id_jenis_kendaraan','jenis_kendaraan_id')
                ->first();
            $pengguna = RiwayatKendaraan::where('kendaraan_id', $id)->get();

            return view('v_super_user.apk_aadb.detail_kendaraan', compact('kendaraan','pengguna'));
        }
    }

    // ===============================================
    //                   OLDAT
    // ===============================================
    public function oldat()
    {
        $timKerja   = TimKerja::get();
        $unitKerja  = UnitKerja::get();
        $chartData  = $this->getChartData();
        $googleChartData  = $this->getGoogleChartData();
        if(Auth::user()->pegawai->jabatan_id == 1 || Auth::user()->pegawai->jabatan_id == 2) {
            $pengajuan  = FormUsulan::with('detailPengadaan')->join('tbl_pegawai','id_pegawai','pegawai_id')
                ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')->join('tbl_unit_kerja','id_unit_kerja','unit_kerja_id')
                ->where('status_proses','!=','selesai')
                ->orderBy('tanggal_usulan', 'DESC')->limit(5)
                ->paginate(5);
        } else {
            $pengajuan  = FormUsulan::with('detailPengadaan')->join('tbl_pegawai','id_pegawai','pegawai_id')
                ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')->join('tbl_unit_kerja','id_unit_kerja','unit_kerja_id')
                ->where('status_proses','!=','selesai')
                ->where('id_pegawai', Auth::user()->pegawai_id)
                ->orderBy('tanggal_usulan', 'DESC')->limit(5)
                ->paginate(5);
        }

        return view('v_super_user.apk_oldat.index', compact('googleChartData','chartData','unitKerja','timKerja','pengajuan'));
    }

    public function report(Request $request, $aksi, $id)
    {
        if ($aksi == 'daftar') {
            $kategoriBarang = KategoriBarang::get();
            $kondisiBarang  = KondisiBarang::get();
            $pegawai        = Pegawai::get();
            $barang         = Barang::join('oldat_tbl_kategori_barang','oldat_tbl_kategori_barang.id_kategori_barang','oldat_tbl_barang.kategori_barang_id')
                ->join('oldat_tbl_kondisi_barang','oldat_tbl_kondisi_barang.id_kondisi_barang','oldat_tbl_barang.kondisi_barang_id')
                ->leftjoin('tbl_pegawai', 'tbl_pegawai.id_pegawai', 'oldat_tbl_barang.pegawai_id')
                ->leftjoin('tbl_tim_kerja', 'id_tim_kerja', 'tim_kerja_id')
                ->get();
            return view('v_super_user.apk_oldat.daftar_laporan', compact('kategoriBarang', 'kondisiBarang','pegawai', 'barang'));
        } elseif ($aksi == 'detail') {
            $kategoriBarang = KategoriBarang::get();
            $kondisiBarang  = KondisiBarang::get();
            $pegawai        = Pegawai::orderBy('nama_pegawai','ASC')->get();
            $barang         = Barang::join('oldat_tbl_kategori_barang', 'oldat_tbl_kategori_barang.id_kategori_barang', 'oldat_tbl_barang.kategori_barang_id')
                ->leftjoin('tbl_pegawai', 'tbl_pegawai.id_pegawai', 'oldat_tbl_barang.pegawai_id')
                ->where('id_barang', $id)->first();
            $riwayat        = RiwayatBarang::join('oldat_tbl_barang','id_barang','barang_id')
                ->join('oldat_tbl_kondisi_barang','oldat_tbl_kondisi_barang.id_kondisi_barang','oldat_tbl_riwayat_barang.kondisi_barang_id')
                ->join('tbl_pegawai','tbl_pegawai.id_pegawai','oldat_tbl_riwayat_barang.pegawai_id')
                ->leftjoin('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                ->join('tbl_unit_kerja','tbl_unit_kerja.id_unit_kerja','tbl_pegawai.unit_kerja_id')
                ->where('barang_id', $id)->get();

            return view('v_super_user.apk_oldat.detail_barang', compact('kategoriBarang','kondisiBarang','pegawai','barang','riwayat'));
        } elseif ($aksi == 'download') {
            return Excel::download(new BarangExport(), 'data_pengadaan_barang.xlsx');
        }
    }

    public function recap(Request $request, $aksi, $id)
    {
        if ($aksi == 'daftar') {
            $kategoriBarang     = KategoriBarang::get();
            $tahunPerolehan     = Barang::select('tahun_perolehan')->groupBy('tahun_perolehan')->orderBy('tahun_perolehan','ASC')->paginate(2);
            $unitKerja          = UnitKerja::get();
            $timKerja           = TimKerja::get();
            $dataBarang         = Barang::select('id_barang', 'kategori_barang','tahun_perolehan', 'pegawai_id', 'tim_kerja', 'unit_kerja')
                ->join('oldat_tbl_kategori_barang', 'id_kategori_barang', 'kategori_barang_id')
                ->join('tbl_unit_kerja', 'tbl_unit_kerja.id_unit_kerja', 'oldat_tbl_barang.unit_kerja_id')
                ->leftjoin('tbl_pegawai', 'id_pegawai', 'pegawai_id')
                ->leftjoin('tbl_tim_kerja', 'id_tim_kerja', 'tim_kerja_id')
                ->get();

                // dd($timKerja);

            $rekapTotalBarang   = Barang::select('kategori_barang', DB::raw('count(id_barang) as totalbarang'))
                ->join('oldat_tbl_kategori_barang','id_kategori_barang','kategori_barang_id')
                ->groupBy('kategori_barang')
                ->get();

            foreach ($tahunPerolehan as $dataTahunPerolehan) {
                foreach ($unitKerja as $dataUnitKerja) {
                    foreach ($timKerja as $dataTimKerja) {
                        foreach ($kategoriBarang as $dataKategoriBarang) {
                            $rekapTahunPerolehan[$dataTahunPerolehan->tahun_perolehan][$dataUnitKerja->unit_kerja][$dataTimKerja->tim_kerja][$dataKategoriBarang->kategori_barang] = $dataBarang->where('tahun_perolehan', $dataTahunPerolehan->tahun_perolehan)->where('unit_kerja', $dataUnitKerja->unit_kerja)->where('tim_kerja', $dataTimKerja->tim_kerja)->where('kategori_barang', $dataKategoriBarang->kategori_barang)->count();

                            $rekapUnitKerja[$dataUnitKerja->unit_kerja][$dataKategoriBarang->kategori_barang] = $dataBarang->where('unit_kerja', $dataUnitKerja->unit_kerja)->where('kategori_barang', $dataKategoriBarang->kategori_barang)->count();

                            $rekapTimKerja[$dataTimKerja->tim_kerja][$dataKategoriBarang->kategori_barang] = $dataBarang->where('tim_kerja', $dataTimKerja->tim_kerja)->where('kategori_barang', $dataKategoriBarang->kategori_barang)->count();
                        }
                    }
                }
            }
            // dd($rekapTahunPerolehan['2015']);

            return view('v_super_user.apk_oldat.daftar_rekap', compact('timKerja','tahunPerolehan','rekapTotalBarang','rekapTahunPerolehan', 'rekapUnitKerja', 'rekapTimKerja','kategoriBarang'));
        } else {
        }
    }

    public function submissionOLDAT(Request $request, $aksi, $id)
    {
        if ($aksi == 'daftar') {
            if(Auth::user()->pegawai->jabatan_id == 1 || Auth::user()->pegawai->jabatan_id == 2) {
                $formUsulan  = FormUsulan::join('tbl_pegawai','id_pegawai','pegawai_id')
                    ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->join('tbl_unit_kerja','id_unit_kerja','unit_kerja_id')
                    ->orderBy('tanggal_usulan','DESC')
                    ->get();
            } else {
                $formUsulan  = FormUsulan::join('tbl_pegawai','id_pegawai','pegawai_id')
                ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                ->join('tbl_unit_kerja','id_unit_kerja','unit_kerja_id')
                ->where('id_pegawai', Auth::user()->pegawai_id)
                ->orderBy('tanggal_usulan','DESC')
                ->get();
            }

            return view('v_super_user.apk_oldat.daftar_pengajuan', compact('formUsulan'));
        } elseif ($aksi == 'form-usulan') {
            $kategoriBarang = KategoriBarang::get();
            $pegawai    = Pegawai::join('tbl_unit_kerja', 'id_unit_kerja', 'unit_kerja_id')
                ->join('tbl_pegawai_jabatan', 'id_jabatan', 'jabatan_id')
                ->where('id_pegawai', Auth::user()->pegawai_id)
                ->first();

            return view('v_super_user.apk_oldat.form_usulan', compact('id','kategoriBarang', 'pegawai'));

        } elseif ($aksi == 'detail') {
            dd('detail');
        } elseif ($aksi == 'proses-pengajuan' && $id == 'pengadaan') {
            $cekData = FormUsulan::count();
            $formUsulan = new FormUsulan();
            $formUsulan->id_form_usulan    = 'pengadaan_'.($cekData+1);
            $formUsulan->pegawai_id        = $request->input('pegawai_id');
            $formUsulan->kode_form         = 'OLDAT_001';
            $formUsulan->jenis_form        = 'pengadaan';
            $formUsulan->total_pengajuan   = $request->input('total_pengajuan');
            $formUsulan->tanggal_usulan    = $request->input('tanggal_usulan' );
            $formUsulan->rencana_pengguna  = $request->input('rencana_pengguna');
            $formUsulan->status_proses     = 'belum proses';
            $formUsulan->kode_otp_usulan   = $request->kode_otp;
            $formUsulan->save();

            $barang = $request->kategori_barang_id;
            foreach ($barang as $i => $kategoriBarang)
            {
                $cekDataDetail  = FormUsulanPengadaan::count();
                $detailUsulan   = new FormUsulanPengadaan();
                $detailUsulan->id_form_usulan_pengadaan  = $cekDataDetail + 1;
                $detailUsulan->form_usulan_id         = 'pengadaan_'.($cekData + 1);
                $detailUsulan->kategori_barang_id     = $kategoriBarang;
                $detailUsulan->merk_barang            = $request->merk_barang[$i];
                $detailUsulan->spesifikasi_barang     = $request->spesifikasi_barang[$i];
                $detailUsulan->jumlah_barang          = $request->jumlah_barang[$i];
                $detailUsulan->satuan_barang          = $request->satuan_barang[$i];
                $detailUsulan->save();
            }

            return redirect('super-user/oldat/dashboard');
        } elseif ($aksi == 'proses-pengajuan' && $id == 'perbaikan') {
            $otp = rand(1000,9999);
            $cekData = FormUsulan::count();
            $formUsulan = new FormUsulan();
            $formUsulan->id_form_usulan = 'perbaikan_'.($cekData + 1);
            $formUsulan->pegawai_id = $request->input('pegawai_id');
            $formUsulan->kode_form  = 'OLDAT_001';
            $formUsulan->jenis_form = 'perbaikan';
            $formUsulan->total_pengajuan   = $request->input('total_pengajuan');
            $formUsulan->tanggal_usulan    = $request->input('tanggal_usulan' );
            $formUsulan->rencana_pengguna  = $request->input('rencana_pengguna');
            $formUsulan->status_proses     = 'belum proses';
            $formUsulan->kode_otp_usulan   = $otp;
            $formUsulan->save();

            $barang = $request->kode_barang;
            foreach ($barang as $i => $kodeBarang)
            {
                $cekDataDetail  = FormUsulanPerbaikan::count();
                $detailUsulan   = new FormUsulanPerbaikan();
                $detailUsulan->id_form_usulan_perbaikan  = $cekDataDetail + 1;
                $detailUsulan->form_usulan_id            = 'perbaikan_'.($cekData + 1);
                $detailUsulan->barang_id               = $kodeBarang;
                $detailUsulan->save();
            }

            return redirect('super-user/oldat/dashboard')->with('success','Berhasil membuat pengajuan');
        } elseif ($aksi == 'proses-diterima') {
            FormUsulan::where('kode_otp_usulan', $id)->update([ 'status_pengajuan' => 'terima', 'status_proses' => 'proses' ]);
            return redirect('super-user/oldat/dashboard')->with('success','Usulan Pengajuan Berhasil Diterima');
        } elseif ($aksi == 'proses-ditolak') {
            FormUsulan::where('kode_otp_usulan', $id)->update([ 'status_pengajuan' => 'tolak', 'status_proses' => 'selesai' ]);
            return redirect('super-user/oldat/dashboard')->with('failed','Usulan Pengajuan Ditolak');
        }
    }

    public function letter(Request $request, $aksi, $id)
    {
        if ($aksi == 'pengajuan') {
            $cekSurat       = FormUsulan::where('id_form_usulan', $id)->first();
            if ($cekSurat->jenis_form == 'pengadaan') {
                $suratPengajuan = FormUsulan::with('detailPengadaan')
                    ->join('tbl_pegawai','id_pegawai','pegawai_id')
                    ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->join('tbl_tim_kerja','id_tim_kerja','tim_kerja_id')
                    ->join('tbl_unit_kerja','id_unit_kerja','tbl_pegawai.unit_kerja_id')
                    ->where('id_form_usulan', $id)
                    ->get();
            } else {
                $suratPengajuan = FormUsulan::with('detailPerbaikan')
                    ->join('tbl_pegawai','id_pegawai','pegawai_id')
                    ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->join('tbl_tim_kerja','id_tim_kerja','tim_kerja_id')
                    ->join('tbl_unit_kerja','id_unit_kerja','tbl_pegawai.unit_kerja_id')
                    ->where('id_form_usulan', $id)
                    ->get();
            }


            return view('v_super_user.apk_oldat.surat_pengajuan', compact('suratPengajuan'));
        } elseif ($aksi == 'detail-bast'){
            $cekSurat   = FormUsulan::where('id_form_usulan', $id)->first();
            $pegawai = FormUsulan::join('tbl_pegawai','id_pegawai','pegawai_id')->where('id_form_usulan', $id)->first();
            if($pegawai->unit_kerja_id == 1) {
                $pimpinan = Pegawai::join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->where('jabatan_id', '2')->where('unit_kerja_id',1)->first();
            } else {
                $pimpinan = null;
            }

            if ($cekSurat->jenis_form == 'pengadaan') {
                $bast = FormUsulan::with('detailPengadaan')
                    ->join('tbl_pegawai','id_pegawai','pegawai_id')
                    ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->join('tbl_tim_kerja','id_tim_kerja','tim_kerja_id')
                    ->join('tbl_unit_kerja','id_unit_kerja','tbl_pegawai.unit_kerja_id')
                    ->where('id_form_usulan', $id)->get();
            } else {
                $bast = FormUsulan::with('detailPerbaikan')
                    ->join('tbl_pegawai','id_pegawai','pegawai_id')
                    ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->join('tbl_tim_kerja','id_tim_kerja','tim_kerja_id')
                    ->join('tbl_unit_kerja','id_unit_kerja','tbl_pegawai.unit_kerja_id')
                    ->where('id_form_usulan', $id)->get();
            }


            return view('v_super_user.apk_oldat.detail_bast', compact('bast','pimpinan'));

        } elseif ($aksi == 'buat-bast') {
            $cekSurat  = FormUsulan::where('id_form_usulan', $id)->first();
            $tujuan     = 'BAST';
            if ($cekSurat->jenis_form == 'pengadaan') {
                $pengajuan = FormUsulan::with('detailPengadaan')
                    ->join('tbl_pegawai','id_pegawai','pegawai_id')
                    ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->join('tbl_tim_kerja','id_tim_kerja','tim_kerja_id')
                    ->join('tbl_unit_kerja','id_unit_kerja','tbl_pegawai.unit_kerja_id')
                    ->where('id_form_usulan', $id)
                    ->get();
            } else {
                $pengajuan = FormUsulan::with('detailPerbaikan')
                    ->join('tbl_pegawai','id_pegawai','pegawai_id')
                    ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->join('tbl_tim_kerja','id_tim_kerja','tim_kerja_id')
                    ->join('tbl_unit_kerja','id_unit_kerja','tbl_pegawai.unit_kerja_id')
                    ->where('id_form_usulan', $id)
                    ->get();
            }
            return view('v_super_user.apk_oldat.buat_bast', compact('cekSurat','tujuan', 'pengajuan', 'id'));
        } elseif ($aksi == 'proses-bast') {
            $cekSurat   = FormUsulan::where('id_form_usulan', $id)->first();
            $idBarang   = $request->id_barang;
            $fotoBarang = $request->foto_barang;
            if ($cekSurat->jenis_form == 'pengadaan') {
                foreach ($idBarang as $i => $idBarang) {
                    if ($request->foto_barang[$i] != null) {
                        $barang = new FormUsulanPengadaan();
                        $foto = $request->file('foto_barang');
                        $filename  = Carbon::now()->format('ddmy') . $i . '_' . $request->foto_barang[$i]->getClientOriginalName();
                        $request->foto_barang[$i]->move('gambar/barang_bmn/', $filename);
                        $barang->foto_barang = $filename;
                    } else {
                        $filename = null;
                    }
                    // Update pengajuan
                    $fotoBarang = $request->foto_barang;
                    FormUsulan::where('id_form_usulan', $id)
                        ->update([
                            'status_proses'         => 'selesai',
                            'kode_otp_bast'         => $request->kode_otp,
                            'konfirmasi_pengajuan'  => $request->konfirmasi
                        ]);

                    FormUsulanPengadaan::where('id_form_usulan_pengadaan', $idBarang)
                    ->update([
                        'foto_barang' => $filename
                    ]);
                    // Tambah barang
                    $barang = new Barang();
                    $barang->id_barang          = rand(100000, 999999);
                    $barang->unit_kerja_id      = Auth::user()->pegawai->unit_kerja_id;
                    $barang->pegawai_id         = Auth::user()->pegawai_id;
                    $barang->kategori_barang_id = $request->kategori_barang_id[$i];
                    $barang->kode_barang        = $request->kode_barang[$i];
                    $barang->nup_barang         = $request->nup_barang[$i];
                    $barang->spesifikasi_barang = $request->merk_barang[$i] . ' ' . $request->spesifikasi_barang[$i];
                    $barang->jumlah_barang      = $request->jumlah_barang[$i];
                    $barang->satuan_barang      = $request->satuan_barang[$i];
                    $barang->nilai_perolehan    = $request->nilai_perolehan[$i];
                    $barang->tahun_perolehan    = $request->tahun_perolehan[$i];
                    $barang->kondisi_barang     = 1;
                    $barang->foto_barang        = $filename;
                    $barang->save();
                }
            } else {
                foreach ($idBarang as $i => $idBarang) {
                    // Update pengajuan
                    $fotoBarang = $request->foto_barang;
                    FormUsulan::where('id_form_usulan', $id)
                        ->update([
                            'status_proses'         => 'selesai',
                            'kode_otp_bast'         => $request->kode_otp,
                            'konfirmasi_pengajuan'  => $request->konfirmasi
                        ]);

                    Barang::where('id_barang', $idBarang)->update([ 'status_barang' => 2 ]);

                    $cekBarang = RiwayatBarang::count();
                    $riwayat   = new RiwayatBarang();
                    $riwayat->id_riwayat_barang    = $cekBarang + 1;
                    $riwayat->pegawai_id           = $request->pegawai_id;
                    $riwayat->barang_id            = $idBarang;
                    $riwayat->tanggal_pengguna     = $request->tanggal_pengguna;
                    $riwayat->kondisi_barang_id    = 1;
                    $riwayat->keperluan_penggunaan = $request->rencana_pengguna;
                    $riwayat->save();
                }
            }


            return redirect('super-user/oldat/surat/detail-bast/'.$id)->with('success','Berhasil membuat BAST');

        } elseif ($aksi == 'pdf-bast') {
            $cekSurat   = FormUsulan::where('kode_otp_bast', $id)->first();
            $pegawai    = FormUsulan::join('tbl_pegawai','id_pegawai','pegawai_id')->where('kode_otp_bast', $id)->first();
            if($pegawai->unit_kerja_id == 1) {
                $pimpinan = Pegawai::join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->where('jabatan_id', '2')->where('unit_kerja_id',1)->first();
            } else {
                $pimpinan = null;
            }

            if ($cekSurat->jenis_form == 'pengadaan') {
                $bast = FormUsulan::with('detailPengadaan')
                    ->join('tbl_pegawai','id_pegawai','pegawai_id')
                    ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->join('tbl_tim_kerja','id_tim_kerja','tim_kerja_id')
                    ->join('tbl_unit_kerja','id_unit_kerja','tbl_pegawai.unit_kerja_id')
                    ->where('kode_otp_bast', $id)->get();
            } else {
                $bast = FormUsulan::with('detailPerbaikan')
                    ->join('tbl_pegawai','id_pegawai','pegawai_id')
                    ->join('tbl_pegawai_jabatan','id_jabatan','jabatan_id')
                    ->join('tbl_tim_kerja','id_tim_kerja','tim_kerja_id')
                    ->join('tbl_unit_kerja','id_unit_kerja','tbl_pegawai.unit_kerja_id')
                    ->where('kode_otp_bast', $id)->get();
            }

            return view('v_super_user.apk_oldat.pdf_bast', compact('pimpinan','bast'));
        }
    }

    public function getChartData()
    {
        $dataBarang = Barang::select('id_barang', 'kategori_barang', 'unit_kerja', 'pegawai_id','tim_kerja')
            ->join('oldat_tbl_kategori_barang', 'id_kategori_barang', 'kategori_barang_id')
            ->join('tbl_unit_kerja', 'tbl_unit_kerja.id_unit_kerja', 'oldat_tbl_barang.unit_kerja_id')
            ->leftjoin('tbl_pegawai', 'id_pegawai', 'pegawai_id')
            ->leftjoin('tbl_tim_kerja', 'tbl_tim_kerja.id_tim_kerja', 'tbl_pegawai.tim_kerja_id')
            ->get();

        $dataKategoriBarang = KategoriBarang::get();
        foreach ($dataKategoriBarang as $data) {
            $labelChart[] = $data->kategori_barang;
            $dataChart[] = $dataBarang->where('kategori_barang', $data->kategori_barang)->count();
        }
        $resultChart['label'] = $labelChart;
        $resultChart['data'] = $dataChart;
        $chart = json_encode($resultChart);

        // dd($chart);
        return $chart;
    }
    public function getGoogleChartData()
    {
        $dataBarang = Barang::select('id_barang', 'kategori_barang', 'unit_kerja', 'pegawai_id','tim_kerja')
            ->join('oldat_tbl_kategori_barang', 'id_kategori_barang', 'kategori_barang_id')
            ->join('tbl_unit_kerja', 'tbl_unit_kerja.id_unit_kerja', 'oldat_tbl_barang.unit_kerja_id')
            ->leftjoin('tbl_pegawai', 'id_pegawai', 'pegawai_id')
            ->leftjoin('tbl_tim_kerja', 'tbl_tim_kerja.id_tim_kerja', 'tbl_pegawai.tim_kerja_id')
            ->get();

        $dataKategoriBarang = KategoriBarang::get();
        foreach ($dataKategoriBarang as $data) {
            $dataArray[] =$data->kategori_barang;
            $dataArray[] = $dataBarang->where('kategori_barang', $data->kategori_barang)->count();
            $dataChart[] = $dataArray;
            unset($dataArray);
        }
        // dd($dataChart);
        $chart = json_encode($dataChart);
        return $chart;
    }

    public function searchChartData(Request $request){
        $dataBarang = Barang::select('id_barang','kategori_barang','pegawai_id','id_unit_kerja','oldat_tbl_barang.unit_kerja_id','id_tim_kerja','tim_kerja','tahun_perolehan')
            ->join('oldat_tbl_kategori_barang','id_kategori_barang','kategori_barang_id')
            ->join('tbl_unit_kerja','tbl_unit_kerja.id_unit_kerja','oldat_tbl_barang.unit_kerja_id')
            ->leftjoin('tbl_pegawai','id_pegawai','pegawai_id')
            ->leftjoin('tbl_tim_kerja', 'tbl_tim_kerja.id_tim_kerja', 'tbl_pegawai.tim_kerja_id');


        $dataKategoriBarang = KategoriBarang::get();

        if($request->hasAny(['tahun', 'unit_kerja','tim_kerja'])){
            if($request->tahun){
                $dataSearchBarang = $dataBarang->where('tahun_perolehan',$request->tahun);
            }
            if($request->unit_kerja){
                $dataSearchBarang = $dataBarang->where('oldat_tbl_barang.unit_kerja_id',$request->unit_kerja);
            }
            if($request->tim_kerja){
                $dataSearchBarang = $dataBarang->where('id_tim_kerja',$request->tim_kerja);
            }

            $dataSearchBarang = $dataSearchBarang->get();

        }else {
            $dataSearchBarang = $dataBarang->get();
        }

        foreach($dataKategoriBarang as $data){
            $labelChart[] = $data->kategori_barang;
            $dataChart[] = $dataSearchBarang->where('kategori_barang',$data->kategori_barang)->count();
        }

        $resultChart['label'] = $labelChart;
        $resultChart['data'] = $dataChart;
        $chart = json_encode($resultChart);

        if(count($dataSearchBarang)>0){
            return response([
                'status' => true,
                'total' => count($dataSearchBarang),
                'message' => 'success',
                'data' => $chart
            ], 200);
        }else {
            return response([
                'status' => true,
                'total' => count($dataSearchBarang),
                'message' => 'not found'
            ], 200);
        }
    }

    public function getDataBarang(Request $request, $id)
    {
        $user     = Auth()->user();
        $pegawai  = $user->pegawai;
        if ($id == 'daftar') {
            $result   = Barang::join('oldat_tbl_kondisi_barang','id_kondisi_barang','kondisi_barang_id')
                ->where('unit_kerja_id', $pegawai->unit_kerja_id)
                ->where('kategori_barang_id', $request->kategori)
                ->pluck('id_barang','spesifikasi_barang');

        } elseif ($id == 'detail') {
            $result   = Barang::join('oldat_tbl_kondisi_barang','id_kondisi_barang','kondisi_barang_id')
                ->where('id_barang', $request->idBarang)
                ->get();
        }
        return response()->json($result);

    }

    public function sendOTPWhatsapp(Request $request){
        $user           = Auth::user();
        $pegawai        = $user->pegawai;
        $version        = getenv("WHATSAPP_API_VERSION");
        $token          = getenv("WHATSAPP_API_token");
        $phoneNumberId  = getenv("WHATSAPP_API_PHONE_NUMBER_ID");
        $penerima       = '6285772652563';
        $otp            = rand(1000,9999);

        // if($request->jenisForm == 'pengadaan') {
        //     $formUsulan = new FormUsulan();
        //     $formUsulan->id_form_usulan = 'pengadaan_'.(rand(100,999));
        //     $formUsulan->pegawai_id     = Auth::user()->pegawai_id;
        //     $formUsulan->kode_form      = 'OLDAT_001';
        //     $formUsulan->jenis_form     = 'pengadaan';
        //     $formUsulan->kode_otp       = $otp;
        //     $formUsulan->save();
        // }

        $client = new GuzzleHttpClient();
        $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$token
        ];

        $body = '{
        "messaging_product": "whatsapp",
        "to": '.$penerima.',
        "type": "template",
        "template": {
            "name": "siporsat_otp2",
            "language": {
            "code": "id"
            },
            "components": [
            {
                "type": "body",
                "parameters": [
                    {
                        "type": "text",
                        "text": '.$otp.'
                    },
                    {
                        "type": "text",
                        "text": "'.$pegawai->nama_pegawai.'"
                    },
                    {
                        "type": "text",
                        "text": "Konfirmasi '.$request->tujuan.'"
                    }
                ]
            }
            ]
        }
        }';

        $request = new Psr7Request('POST', 'https://graph.facebook.com/'.$version.'/'.$phoneNumberId.'/messages', $headers, $body);
        $res = $client->sendAsync($request)->wait();
        return $otp;
        // return $res->getBody();
        //   return view('v_super_user.apk_oldat.tes');
    }

    public function getForm(Request $request) {
        $result = FormUsulan::where('id_form_usulan', $request->kode_otp)->first();
        return $result;
    }
}
