<?php

namespace App\Eloquent\Crm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AmountReceived extends Model
{

    protected $connection = 'mysql';

    protected $table = 'thuc_thu';

    protected $fillable = [
        'id_sinh_vien',
        'hinh_thuc_nop_tien',
        'ten_ngan_hang',
        'ngay_bien_lai',
        'so_chung_tu_bien_lai',
        'thuc_nop',
        'muc_dich_thu',
        'id_dot_hoc',
        'created_by',
        'updated_by',
        'deleted',
        'dot_hoc_so',
        'sync',
        'ghi_chu',
    ];

    protected $dates = ['ngay_bien_lai'];
    public function scopeSoftDelete($query)
    {
        return $query->whereRaw('(thuc_thu.deleted IS NULL OR thuc_thu.deleted = 0)');
    }
    /**
     * @return HasOne
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'id', 'id_sinh_vien');
    }
}