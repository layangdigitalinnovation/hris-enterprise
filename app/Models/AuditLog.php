<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    public function setActionAttribute($value)
    {
        if (empty($this->attributes['event'])) {
            $this->attributes['event'] = $value;
        }
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->event)) {
                $model->event = $model->action ?? 'system';
            }
        });
    }

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTranslatedDescriptionAttribute(): string
    {
        $desc = trim((string) $this->description);
        if ($desc === '' || app()->getLocale() !== 'ar') {
            return $desc;
        }

        // 1. Direct key match in lang/ar.json
        $direct = __($desc);
        if ($direct !== $desc) {
            return $direct;
        }

        // Strip trailing dot for cleaner regex matching
        $hasDot = str_ends_with($desc, '.');
        $cleanDesc = rtrim($desc, '.');

        $dot = $hasDot ? '.' : '';

        // 2. Pattern translations
        if (preg_match('/^User (.+) berhasil login$/i', $cleanDesc, $m)) {
            return 'قام المستخدم ' . $m[1] . ' بتسجيل الدخول بنجاح' . $dot;
        }
        if (preg_match('/^User (.+) telah logout$/i', $cleanDesc, $m)) {
            return 'قام المستخدم ' . $m[1] . ' بتسجيل الخروج' . $dot;
        }
        if (preg_match('/^Memperbarui konfigurasi sistem HRIS$/i', $cleanDesc)) {
            return 'تحديث إعدادات نظام الموارد البشرية' . $dot;
        }
        if (preg_match('/^Menambahkan karyawan baru (.+)$/i', $cleanDesc, $m)) {
            return 'إضافة موظف جديد ' . $m[1] . $dot;
        }
        if (preg_match('/^Memperbarui data karyawan (.+)$/i', $cleanDesc, $m)) {
            return 'تحديث بيانات الموظف ' . $m[1] . $dot;
        }
        if (preg_match('/^Menghapus \(soft delete\) karyawan (.+)$/i', $cleanDesc, $m)) {
            return 'حذف (حذف مؤقت) الموظف ' . $m[1] . $dot;
        }
        if (preg_match('/^Administrator (.+) mereset password akun login karyawan (.+)$/i', $cleanDesc, $m)) {
            return "قام المسؤول {$m[1]} بإعادة تعيين كلمة مرور الموظف {$m[2]}" . $dot;
        }
        if (preg_match('/^Mengatur gaji pokok karyawan (.+) menjadi Rp (.+) beserta komponen tunjangan\/potongan khusus$/i', $cleanDesc, $m)) {
            return "تحديد الراتب الأساسي للموظف {$m[1]} بمبلغ {$m[2]} ر.س مع بنود البدلات/الاستقطاعات الخاصة" . $dot;
        }
        if (preg_match('/^Mengatur gaji pokok karyawan (.+) menjadi Rp (.+)$/i', $cleanDesc, $m)) {
            return "تحديد الراتب الأساسي للموظف {$m[1]} بمبلغ {$m[2]} ر.س" . $dot;
        }
        if (preg_match('/^Kalkulasi payroll \'([^\']+)\' untuk (\d+) karyawan aktif berdasarkan kebijakan tunjangan dan potongan$/i', $cleanDesc, $m)) {
            return "احتساب مسير رواتب '{$m[1]}' لعدد {$m[2]} موظف نشط بناءً على سياسات البدلات والاستقطاعات" . $dot;
        }
        if (preg_match('/^Memperbarui departemen:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'تحديث قسم: ' . $m[1] . $dot;
        }
        if (preg_match('/^Menambahkan departemen:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'إضافة قسم: ' . $m[1] . $dot;
        }
        if (preg_match('/^Menghapus departemen:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'حذف قسم: ' . $m[1] . $dot;
        }
        if (preg_match('/^Memperbarui jabatan:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'تحديث مسمى وظيفي: ' . $m[1] . $dot;
        }
        if (preg_match('/^Menambahkan jabatan:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'إضافة مسمى وظيفي: ' . $m[1] . $dot;
        }
        if (preg_match('/^Menghapus jabatan:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'حذف مسمى وظيفي: ' . $m[1] . $dot;
        }
        if (preg_match('/^Memperbarui kantor cabang:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'تحديث فرع: ' . $m[1] . $dot;
        }
        if (preg_match('/^Menambahkan kantor cabang:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'إضافة فرع: ' . $m[1] . $dot;
        }
        if (preg_match('/^Menghapus kantor cabang:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'حذف فرع: ' . $m[1] . $dot;
        }
        if (preg_match('/^Menjalankan sinkronisasi massal kuota cuti tahun (\d+)\.\s*(\d+) saldo kuota baru berhasil dibuat$/i', $cleanDesc, $m)) {
            return "تشغيل المزامنة الشاملة لأرصدة الإجازات لعام {$m[1]}. تم إنشاء {$m[2]} رصيد جديد بنجاح" . $dot;
        }
        if (preg_match('/^Memperbarui kebijakan komponen \'([^\']+)\' \(([^)]+)\):\s*Nilai ([^,]+),\s*Mode (.+)$/i', $cleanDesc, $m)) {
            return "تحديث سياسة بند '{$m[1]}' ({$m[2]}): القيمة {$m[3]}، النمط {$m[4]}" . $dot;
        }
        if (preg_match('/^Menambahkan master komponen (tunjangan|potongan) baru \'([^\']+)\' \(([^)]+)\)$/i', $cleanDesc, $m)) {
            $type = $m[1] === 'tunjangan' ? 'بدل' : 'استقطاع';
            return "إضافة بند {$type} جديد '{$m[2]}' ({$m[3]})" . $dot;
        }
        if (preg_match('/^Menghapus komponen gaji \'([^\']+)\'$/i', $cleanDesc, $m)) {
            return "حذف بند الراتب '{$m[1]}'" . $dot;
        }
        if (preg_match('/^Menambahkan master jenis cuti baru:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'إضافة نوع إجازة جديد: ' . $m[1] . $dot;
        }
        if (preg_match('/^Memperbarui data jenis cuti\s*(.+)$/i', $cleanDesc, $m)) {
            return 'تحديث نوع الإجازة ' . $m[1] . $dot;
        }
        if (preg_match('/^Menghapus jenis cuti\s*(.+)$/i', $cleanDesc, $m)) {
            return 'حذف نوع الإجازة ' . $m[1] . $dot;
        }
        if (preg_match('/^Menambahkan master rate lembur baru:\s*\'([^\']+)\' \(([^)]+)\)$/i', $cleanDesc, $m)) {
            return "إضافة معدل عمل إضافي جديد: '{$m[1]}' ({$m[2]})" . $dot;
        }
        if (preg_match('/^Memperbarui master rate lembur:\s*\'([^\']+)\' \(([^)]+)\)$/i', $cleanDesc, $m)) {
            return "تحديث معدل العمل الإضافي: '{$m[1]}' ({$m[2]})" . $dot;
        }
        if (preg_match('/^Menghapus master rate lembur\s*\'([^\']+)\'$/i', $cleanDesc, $m)) {
            return "حذف معدل العمل الإضافي '{$m[1]}'" . $dot;
        }
        if (preg_match('/^Membuat shift baru:\s*(.+)$/i', $cleanDesc, $m)) {
            return 'إنشاء مناوبة جديدة: ' . $m[1] . $dot;
        }
        if (preg_match('/^Pengajuan cuti (.+)$/i', $cleanDesc, $m)) {
            return 'طلب إجازة ' . $m[1] . $dot;
        }
        if (preg_match('/^Pengajuan lembur (.+)$/i', $cleanDesc, $m)) {
            return 'طلب عمل إضافي ' . $m[1] . $dot;
        }
        if (preg_match('/^Mengunggah dokumen \'([^\']+)\' \(([^)]+)\) untuk (.+)$/i', $cleanDesc, $m)) {
            return "رفع وثيقة '{$m[1]}' ({$m[2]}) للموظف {$m[3]}" . $dot;
        }
        if (preg_match('/^Menghapus dokumen \'([^\']+)\' milik (.+)$/i', $cleanDesc, $m)) {
            return "حذف وثيقة '{$m[1]}' الخاصة بالموظف {$m[2]}" . $dot;
        }

        return $desc;
    }
}
