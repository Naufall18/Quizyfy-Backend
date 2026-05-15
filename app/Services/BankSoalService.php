<?php

namespace App\Services;

use App\Models\Questions;
use Illuminate\Pagination\LengthAwarePaginator;

class BankSoalService
{
    /**
     * Ambil daftar soal sesuai filter & pencarian, dengan pagination.
     *
     * @param  array  $filters  ['exam_id' => int|null, 'category_id' => int|null]
     * @param  string|null  $search
     * @param  bool  $shuffle  apakah diacak?
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], ?string $search = null, bool $shuffle = false, int $perPage = 15): LengthAwarePaginator
    {
        $query = Questions::query()->where('is_active', true);

        if (!empty($filters['exam_id'])) {
            $query->where('exam_id', $filters['exam_id']);
        }
        if (!empty($filters['category_id'])) {
            // asumsikan Question punya relasi category lewat exam
            $query->whereHas('exam', fn($q) => $q->where('category_id', $filters['category_id']));
        }

        if ($search) {
            $query->where('question', 'like', "%{$search}%");
        }

        if ($shuffle) {
            $query->inRandomOrder();
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }
}
