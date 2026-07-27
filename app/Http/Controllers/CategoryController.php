<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryReq;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Helpers\BaseResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Semua kategori (admin/guru) — tanpa filter.
     */
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();
        return BaseResponse::OK($categories, 'Kategori berhasil diambil');
    }

    /**
     * Kategori aktif saja (untuk siswa memilih ujian).
     */
    public function indexActive(): JsonResponse
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return BaseResponse::OK($categories, 'Kategori aktif berhasil diambil');
    }

    /**
     * Detail kategori berdasarkan slug (publik).
     */
    public function showBySlug(Category $category): JsonResponse
    {
        if (!$category->is_active) {
            return BaseResponse::NotFound('Kategori tidak ditemukan');
        }
        return BaseResponse::OK($category, 'Kategori ditemukan');
    }

    /**
     * Detail kategori berdasarkan ID.
     */
    public function show(Category $category): JsonResponse
    {
        return BaseResponse::OK($category, 'Kategori ditemukan');
    }

    /**
     * Buat kategori baru — guru/admin only.
     */
    public function store(StoreCategoryReq $request): JsonResponse
    {
        $category = Category::create($request->validated());
        return BaseResponse::OK($category, 'Kategori berhasil dibuat', 201);
    }

    /**
     * Update kategori — guru/admin only.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());
        return BaseResponse::OK($category->fresh(), 'Kategori berhasil diperbarui');
    }

    /**
     * Hapus kategori — guru/admin only.
     * Cek apakah masih digunakan oleh ujian.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Guard: jangan hapus jika masih ada ujian yang pakai kategori ini
        if ($category->exams()->exists()) {
            return BaseResponse::BadRequest(
                'Kategori tidak dapat dihapus karena masih digunakan oleh ujian.'
            );
        }

        $category->delete();
        return BaseResponse::OK(null, 'Kategori berhasil dihapus');
    }

    /**
     * Toggle status aktif/nonaktif kategori.
     * PATCH /guru/categories/{category}/toggle
     */
    public function toggle(Category $category): JsonResponse
    {
        $category->update(['is_active' => !$category->is_active]);
        return BaseResponse::OK(
            ['id' => $category->id, 'is_active' => $category->is_active],
            'Status kategori berhasil diubah'
        );
    }
}
