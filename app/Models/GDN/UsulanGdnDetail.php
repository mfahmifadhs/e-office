<?php

namespace App\Models\gdn;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsulanGdnDetail extends Model
{
    use HasFactory;
    protected $table        = "gdn_tbl_form_usulan_detail";
    protected $primaryKey   = "id_form_usulan";
    public $timestamps      = false;

    protected $fillable = [
        'id_form_usulan_detail',
        'form_usulan_id',
        'bid_kerusakan_id',
        'lokasi_bangunan',
        'lokasi_spesifik',
        'keterangan'
    ];
}
