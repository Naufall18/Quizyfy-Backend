<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Get FAQ list (Frequently Asked Questions).
     * Available to all authenticated users
     */
    public function faq(Request $request)
    {
        $category = $request->query('category');
        
        $faqs = [
            [
                'id' => 1,
                'category' => 'akun',
                'question' => 'Bagaimana cara membuat akun?',
                'answer' => 'Anda dapat membuat akun dengan mengklik tombol "Daftar" di halaman login. Isi email, password, dan informasi profil lainnya. Setelah itu, akun Anda akan otomatis aktif.',
            ],
            [
                'id' => 2,
                'category' => 'akun',
                'question' => 'Bagaimana jika lupa password?',
                'answer' => 'Klik "Lupa Password" di halaman login, masukkan email Anda, dan ikuti instruksi yang dikirimkan ke email untuk mereset password.',
            ],
            [
                'id' => 3,
                'category' => 'akun',
                'question' => 'Bagaimana cara mengubah profile?',
                'answer' => 'Masuk ke akun Anda, buka menu "Profile" atau "Pengaturan", kemudian edit informasi yang ingin diubah dan simpan perubahan.',
            ],
            [
                'id' => 4,
                'category' => 'ujian',
                'question' => 'Bagaimana cara mengikuti ujian?',
                'answer' => 'Lihat daftar ujian yang tersedia di dashboard, pilih ujian yang ingin diikuti, klik "Mulai Ujian", dan jawab semua pertanyaan dalam waktu yang ditentukan.',
            ],
            [
                'id' => 5,
                'category' => 'ujian',
                'question' => 'Apa yang terjadi jika waktu ujian habis?',
                'answer' => 'Jika waktu ujian habis, sistem akan otomatis mengumpulkan jawaban Anda dan menampilkan hasil ujian.',
            ],
            [
                'id' => 6,
                'category' => 'ujian',
                'question' => 'Bisakah saya mengulang ujian?',
                'answer' => 'Tergantung pada pengaturan ujian oleh guru. Beberapa ujian hanya bisa dikerjakan sekali, sementara yang lain memungkinkan pengulangan. Lihat detail ujian untuk informasi lebih lanjut.',
            ],
            [
                'id' => 7,
                'category' => 'ujian',
                'question' => 'Bagaimana cara melihat hasil ujian?',
                'answer' => 'Setelah menyelesaikan ujian, hasil akan ditampilkan langsung. Anda juga dapat melihat riwayat hasil ujian di menu "Hasil" atau "Nilai Saya".',
            ],
            [
                'id' => 8,
                'category' => 'langganan',
                'question' => 'Apa manfaat berlangganan premium?',
                'answer' => 'Langganan premium memberikan akses ke lebih banyak ujian, fitur analitik lengkap, dan prioritas dukungan pelanggan.',
            ],
            [
                'id' => 9,
                'category' => 'langganan',
                'question' => 'Bagaimana cara membeli langganan?',
                'answer' => 'Buka menu "Paket" atau "Langganan", pilih paket yang sesuai, klik "Beli", dan ikuti proses pembayaran.',
            ],
            [
                'id' => 10,
                'category' => 'langganan',
                'question' => 'Apakah bisa membatalkan langganan?',
                'answer' => 'Ya, Anda dapat membatalkan langganan kapan saja. Akses akan tetap berlaku hingga akhir periode langganan Anda.',
            ],
            [
                'id' => 11,
                'category' => 'guru',
                'question' => 'Bagaimana cara membuat ujian baru?',
                'answer' => 'Di menu "Ujian", klik "Buat Ujian Baru", isi detail ujian (judul, deskripsi, durasi, etc.), kemudian tambahkan pertanyaan dari bank soal.',
            ],
            [
                'id' => 12,
                'category' => 'guru',
                'question' => 'Bagaimana cara menambah pertanyaan ke bank soal?',
                'answer' => 'Buka menu "Bank Soal", klik "Tambah Soal", pilih tipe pertanyaan (pilihan ganda, essay, gambar), isi pertanyaan dan jawaban, kemudian simpan.',
            ],
            [
                'id' => 13,
                'category' => 'guru',
                'question' => 'Bagaimana cara melihat hasil siswa?',
                'answer' => 'Di menu ujian, klik "Lihat Hasil" untuk melihat daftar siswa yang telah mengerjakan ujian beserta skor mereka.',
            ],
            [
                'id' => 14,
                'category' => 'guru',
                'question' => 'Bagaimana cara membagikan ujian kepada siswa?',
                'answer' => 'Setelah membuat ujian, copy link ujian atau bagikan melalui class code. Siswa dapat bergabung dengan ujian menggunakan link atau kode tersebut.',
            ],
            [
                'id' => 15,
                'category' => 'teknis',
                'question' => 'Browser apa yang didukung?',
                'answer' => 'Platform ini mendukung Chrome, Firefox, Safari, dan Edge dengan versi terbaru untuk pengalaman terbaik.',
            ],
            [
                'id' => 16,
                'category' => 'teknis',
                'question' => 'Apakah bisa diakses dari mobile?',
                'answer' => 'Ya, platform ini fully responsive dan dapat diakses dari smartphone, tablet, dan desktop dengan mudah.',
            ],
            [
                'id' => 17,
                'category' => 'teknis',
                'question' => 'Apa yang harus dilakukan jika ada error?',
                'answer' => 'Coba refresh halaman, clear browser cache, atau gunakan browser lain. Jika masalah berlanjut, hubungi tim support kami.',
            ],
        ];
        
        // Filter by category if provided
        if ($category) {
            $faqs = array_filter($faqs, function ($faq) use ($category) {
                return $faq['category'] === $category;
            });
            $faqs = array_values($faqs); // Re-index array
        }
        
        // Pagination
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $total = count($faqs);
        $paginatedFaqs = array_slice($faqs, ($page - 1) * $perPage, $perPage);
        
        $response = [
            'data' => $paginatedFaqs,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ]
        ];
        
        return BaseResponse::OK($response, 'FAQ list retrieved successfully');
    }

    /**
     * Get FAQ by ID
     */
    public function faqDetail($id)
    {
        $faqs = $this->getFaqData();
        
        $faq = null;
        foreach ($faqs as $item) {
            if ($item['id'] == $id) {
                $faq = $item;
                break;
            }
        }
        
        if (!$faq) {
            return BaseResponse::ERROR('FAQ not found', 404);
        }
        
        return BaseResponse::OK($faq, 'FAQ detail retrieved successfully');
    }

    /**
     * Get all FAQ categories
     */
    public function categories()
    {
        $categories = [
            [
                'id' => 'akun',
                'name' => 'Akun & Keamanan',
                'icon' => 'user',
                'count' => 3,
            ],
            [
                'id' => 'ujian',
                'name' => 'Ujian & Soal',
                'icon' => 'file-text',
                'count' => 5,
            ],
            [
                'id' => 'langganan',
                'name' => 'Langganan & Pembayaran',
                'icon' => 'credit-card',
                'count' => 3,
            ],
            [
                'id' => 'guru',
                'name' => 'Untuk Guru',
                'icon' => 'book',
                'count' => 4,
            ],
            [
                'id' => 'teknis',
                'name' => 'Teknis & Browser',
                'icon' => 'settings',
                'count' => 3,
            ],
        ];
        
        return BaseResponse::OK($categories, 'FAQ categories retrieved successfully');
    }

    /**
     * Get help/documentation
     */
    public function documentation(Request $request)
    {
        $section = $request->query('section', 'general');
        
        $documentation = [
            'general' => [
                'title' => 'Panduan Umum',
                'content' => 'Quizyfy adalah platform ujian online yang memudahkan guru dan siswa dalam proses pembelajaran. Platform ini menyediakan fitur untuk membuat ujian, melihat hasil, dan mengelola langganan.',
                'sections' => [
                    [
                        'title' => 'Memulai',
                        'content' => 'Setelah mendaftar, Anda dapat langsung mulai menggunakan platform. Jika Anda guru, mulai dengan membuat ujian di menu "Ujian Saya". Jika Anda siswa, lihat daftar ujian yang tersedia di dashboard.'
                    ],
                    [
                        'title' => 'Panduan Guru',
                        'content' => 'Guru dapat membuat ujian, menambah soal, melihat hasil siswa, dan mengelola kelas dalam satu dashboard yang intuitif.'
                    ],
                    [
                        'title' => 'Panduan Siswa',
                        'content' => 'Siswa dapat melihat ujian yang tersedia, mengikuti ujian, melihat hasil, dan mengelola profil mereka.'
                    ]
                ]
            ],
            'teachers' => [
                'title' => 'Panduan Guru',
                'content' => 'Sebagai guru, Anda memiliki akses ke berbagai fitur untuk mengelola ujian dan siswa.',
                'sections' => [
                    [
                        'title' => 'Membuat Ujian',
                        'content' => 'Klik "Buat Ujian Baru", isi detail ujian, dan tambahkan soal dari bank soal atau buat soal baru.'
                    ],
                    [
                        'title' => 'Mengelola Soal',
                        'content' => 'Di menu "Bank Soal", Anda dapat melihat semua soal yang telah dibuat, mengedit, atau menghapusnya.'
                    ],
                    [
                        'title' => 'Melihat Hasil',
                        'content' => 'Setelah siswa mengerjakan ujian, Anda dapat melihat hasil lengkap mereka di halaman detail ujian.'
                    ]
                ]
            ],
            'students' => [
                'title' => 'Panduan Siswa',
                'content' => 'Sebagai siswa, Anda dapat mengikuti ujian dan melihat hasil pembelajaran Anda.',
                'sections' => [
                    [
                        'title' => 'Mengikuti Ujian',
                        'content' => 'Pilih ujian dari daftar, klik "Mulai Ujian", dan jawab semua pertanyaan dalam waktu yang ditentukan.'
                    ],
                    [
                        'title' => 'Melihat Hasil',
                        'content' => 'Setelah selesai, hasil akan ditampilkan langsung dan Anda dapat melihat jawaban dan penjelasan.'
                    ]
                ]
            ]
        ];
        
        $content = $documentation[$section] ?? $documentation['general'];
        
        return BaseResponse::OK($content, 'Documentation retrieved successfully');
    }

    /**
     * Helper method to get all FAQ data
     */
    private function getFaqData()
    {
        return [
            [
                'id' => 1,
                'category' => 'akun',
                'question' => 'Bagaimana cara membuat akun?',
                'answer' => 'Anda dapat membuat akun dengan mengklik tombol "Daftar" di halaman login. Isi email, password, dan informasi profil lainnya. Setelah itu, akun Anda akan otomatis aktif.',
            ],
            [
                'id' => 2,
                'category' => 'akun',
                'question' => 'Bagaimana jika lupa password?',
                'answer' => 'Klik "Lupa Password" di halaman login, masukkan email Anda, dan ikuti instruksi yang dikirimkan ke email untuk mereset password.',
            ],
            [
                'id' => 3,
                'category' => 'akun',
                'question' => 'Bagaimana cara mengubah profile?',
                'answer' => 'Masuk ke akun Anda, buka menu "Profile" atau "Pengaturan", kemudian edit informasi yang ingin diubah dan simpan perubahan.',
            ],
            [
                'id' => 4,
                'category' => 'ujian',
                'question' => 'Bagaimana cara mengikuti ujian?',
                'answer' => 'Lihat daftar ujian yang tersedia di dashboard, pilih ujian yang ingin diikuti, klik "Mulai Ujian", dan jawab semua pertanyaan dalam waktu yang ditentukan.',
            ],
            [
                'id' => 5,
                'category' => 'ujian',
                'question' => 'Apa yang terjadi jika waktu ujian habis?',
                'answer' => 'Jika waktu ujian habis, sistem akan otomatis mengumpulkan jawaban Anda dan menampilkan hasil ujian.',
            ],
            [
                'id' => 6,
                'category' => 'ujian',
                'question' => 'Bisakah saya mengulang ujian?',
                'answer' => 'Tergantung pada pengaturan ujian oleh guru. Beberapa ujian hanya bisa dikerjakan sekali, sementara yang lain memungkinkan pengulangan. Lihat detail ujian untuk informasi lebih lanjut.',
            ],
            [
                'id' => 7,
                'category' => 'ujian',
                'question' => 'Bagaimana cara melihat hasil ujian?',
                'answer' => 'Setelah menyelesaikan ujian, hasil akan ditampilkan langsung. Anda juga dapat melihat riwayat hasil ujian di menu "Hasil" atau "Nilai Saya".',
            ],
            [
                'id' => 8,
                'category' => 'langganan',
                'question' => 'Apa manfaat berlangganan premium?',
                'answer' => 'Langganan premium memberikan akses ke lebih banyak ujian, fitur analitik lengkap, dan prioritas dukungan pelanggan.',
            ],
            [
                'id' => 9,
                'category' => 'langganan',
                'question' => 'Bagaimana cara membeli langganan?',
                'answer' => 'Buka menu "Paket" atau "Langganan", pilih paket yang sesuai, klik "Beli", dan ikuti proses pembayaran.',
            ],
            [
                'id' => 10,
                'category' => 'langganan',
                'question' => 'Apakah bisa membatalkan langganan?',
                'answer' => 'Ya, Anda dapat membatalkan langganan kapan saja. Akses akan tetap berlaku hingga akhir periode langganan Anda.',
            ],
            [
                'id' => 11,
                'category' => 'guru',
                'question' => 'Bagaimana cara membuat ujian baru?',
                'answer' => 'Di menu "Ujian", klik "Buat Ujian Baru", isi detail ujian (judul, deskripsi, durasi, etc.), kemudian tambahkan pertanyaan dari bank soal.',
            ],
            [
                'id' => 12,
                'category' => 'guru',
                'question' => 'Bagaimana cara menambah pertanyaan ke bank soal?',
                'answer' => 'Buka menu "Bank Soal", klik "Tambah Soal", pilih tipe pertanyaan (pilihan ganda, essay, gambar), isi pertanyaan dan jawaban, kemudian simpan.',
            ],
            [
                'id' => 13,
                'category' => 'guru',
                'question' => 'Bagaimana cara melihat hasil siswa?',
                'answer' => 'Di menu ujian, klik "Lihat Hasil" untuk melihat daftar siswa yang telah mengerjakan ujian beserta skor mereka.',
            ],
            [
                'id' => 14,
                'category' => 'guru',
                'question' => 'Bagaimana cara membagikan ujian kepada siswa?',
                'answer' => 'Setelah membuat ujian, copy link ujian atau bagikan melalui class code. Siswa dapat bergabung dengan ujian menggunakan link atau kode tersebut.',
            ],
            [
                'id' => 15,
                'category' => 'teknis',
                'question' => 'Browser apa yang didukung?',
                'answer' => 'Platform ini mendukung Chrome, Firefox, Safari, dan Edge dengan versi terbaru untuk pengalaman terbaik.',
            ],
            [
                'id' => 16,
                'category' => 'teknis',
                'question' => 'Apakah bisa diakses dari mobile?',
                'answer' => 'Ya, platform ini fully responsive dan dapat diakses dari smartphone, tablet, dan desktop dengan mudah.',
            ],
            [
                'id' => 17,
                'category' => 'teknis',
                'question' => 'Apa yang harus dilakukan jika ada error?',
                'answer' => 'Coba refresh halaman, clear browser cache, atau gunakan browser lain. Jika masalah berlanjut, hubungi tim support kami.',
            ],
        ];
    }
}
