<?php

namespace App\Eloquent\Crm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student  extends Model
{
    protected $connection = 'mysql';

    protected $table = 'sinh_vien';

    protected $fillable = [
        'id_can_bo_tvts',
        'id_can_bo_ts8',
        'ma_ho_so',
        'ho_dem',
        'ten',
        'gioi_tinh',
        'ngay_sinh',
        'noi_sinh',
        'dan_toc',
        'ton_giao',
        'so_cmnd_cccd',
        'ngay_cap',
        'noi_cap',
        'dien_thoai',
        'email_ca_nhan',
        'dia_chi',
        'ho_khau_thuong_tru',
        'doi_tuong',
        'nganh_da_tot_nghiep',
        'noi_cap_bang',
        'khoa',
        'ngay_dang_ky',
        'id_lop_quan_ly',
        'level',
        'trang_thai_ho_so_tvts',
        'ngay_nop_ho_so_nhap_hoc_tkts',
        'ngay_tkts_ban_giao_ho_so_giao_vu',
        'ghi_chu',
        'co_quan_cong_tac',
        'ten_cha',
        'nam_sinh_cha',
        'nghe_nghiep_cha',
        'ten_me',
        'nam_sinh_me',
        'nghe_nghiep_me',
        'ten_vo_hoac_chong',
        'nam_sinh_vo_hoac_chong',
        'nghe_nghiep_vo_hoac_chong',
        'noi_cu_tru',
        'created_by',
        'created_at',
        'updated_at',
        'ngay_giao_vu_ban_giao_ho_so_truong',
        'so_quyet_dinh',
        'ngay_quyet_dinh',
        'ma_sinh_vien',
        'trang_thai_sinh_vien',
        'tai_khoan_hoc_tap',
        'email_hoc_tap',
        'deleted',
        'updated_by',
        'id_dot_khai_giang',
        'khu_vuc',
        'trang_thai_ho_so_giao_vu',
        'phan_hoi_ho_so_loi',
        'thpt_ten_truong',
        'thpt_quan_huyen',
        'thpt_thanh_pho',
        'cong_viec_hien_nay',
        'nam_tot_nghiep',
        'ndd_1',
        'ndd_1_quan_he',
        'ndd_1_nghe_nghiep',
        'ndd_1_dien_thoai',
        'ndd_1_dia_chi',
        'ndd_2',
        'ndd_2_quan_he',
        'ndd_2_nghe_nghiep',
        'ndd_2_dien_thoai',
        'ndd_2_dia_chi',
        'sync',
        'nam_sinh',
        'id_tuan_khai_giang',
    ];

    /**
     * @return HasMany
     */
    public function amountsReceived(): HasMany
    {
        return $this->hasMany(AmountReceived::class, 'id_sinh_vien', 'id');
    }
}

