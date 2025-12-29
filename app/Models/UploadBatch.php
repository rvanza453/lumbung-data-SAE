<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'filename',
        'file_path',
        'periode',
        'total_records',
        'uploaded_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'total_records' => 'integer',
    ];

    public function performances()
    {
        return $this->hasMany(KaryawanPerformance::class, 'batch_id', 'batch_id');
    }

    /**
     * Generate unique batch ID
     */
    public static function generateBatchId()
    {
        return 'BATCH-' . date('YmdHis') . '-' . substr(md5(uniqid()), 0, 6);
    }
}

