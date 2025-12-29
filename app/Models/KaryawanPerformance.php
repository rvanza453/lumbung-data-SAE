<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KaryawanPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'nama',
        'afd',
        'hk',
        'jjg',
        'ton',
        'kg_per_hk',
        'periode',
        'tanggal_upload',
        'uploaded_by',
        'batch_id',
        'notes',
    ];

    protected $casts = [
        'hk' => 'decimal:2',
        'jjg' => 'integer',
        'ton' => 'decimal:3',
        'kg_per_hk' => 'decimal:2',
        'tanggal_upload' => 'date',
    ];

    public function batch()
    {
        return $this->belongsTo(UploadBatch::class, 'batch_id', 'batch_id');
    }

    /**
     * Get categorized data based on benchmarks
     */
    public static function getCategorizedData($filters = [])
    {
        $query = self::query();

        // Apply filters
        if (!empty($filters['periode'])) {
            if (is_array($filters['periode'])) {
                $query->whereIn('periode', $filters['periode']);
            } else {
                $query->where('periode', $filters['periode']);
            }
        }

        if (!empty($filters['afd'])) {
            $query->where('afd', $filters['afd']);
        }

        if (!empty($filters['batch_id'])) {
            if (is_array($filters['batch_id'])) {
                $query->whereIn('batch_id', $filters['batch_id']);
            } else {
                $query->where('batch_id', $filters['batch_id']);
            }
        }

        if (!empty($filters['start_date'])) {
            $query->where('tanggal_upload', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('tanggal_upload', '<=', $filters['end_date']);
        }

        $data = $query->get();

        // Calculate benchmarks
        $avgHK = $data->avg('hk');
        $avgProd = $data->avg('kg_per_hk');

        // Categorize
        $categorized = $data->map(function ($item) use ($avgHK, $avgProd) {
            $category = 'Underperformer';
            
            if ($item->hk >= $avgHK && $item->kg_per_hk >= $avgProd) {
                $category = 'Star';
            } elseif ($item->hk >= $avgHK && $item->kg_per_hk < $avgProd) {
                $category = 'Workhorse';
            } elseif ($item->hk < $avgHK && $item->kg_per_hk >= $avgProd) {
                $category = 'Potential';
            }

            $item->Category = $category;
            return $item;
        });

        return [
            'data' => $categorized,
            'benchmarks' => [
                'hk' => $avgHK,
                'prod' => $avgProd,
            ],
            'summary' => [
                'total' => $data->count(),
                'total_hk' => $data->sum('hk'),
                'total_jjg' => $data->sum('jjg'),
                'total_ton' => $data->sum('ton'),
            ]
        ];
    }
}

