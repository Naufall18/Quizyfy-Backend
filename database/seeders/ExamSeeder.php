<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Category;
use App\Models\Exam;
use App\Models\Questions;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $guru1 = User::where('email', 'guru1@quizyfy.com')->first();
        $guru2 = User::where('email', 'guru2@quizyfy.com')->first();

        $catInfo  = Category::where('slug', 'informatika')->first();
        $catMath  = Category::where('slug', 'matematika')->first();

        // ── Ujian 1: Informatika Bab 1 (Aktif, sudah bisa diakses) ──────────────
        $exam1 = Exam::updateOrCreate(
            ['token' => 'INFBAB001'],
            [
                'titles'           => 'Ujian Informatika Bab 1 - Dasar Komputer',
                'description'      => 'Ujian ini mengujikan pemahaman siswa tentang dasar-dasar komputer, perangkat keras, perangkat lunak, dan fungsinya.',
                'token'            => 'INFBAB001',
                'category_id'      => $catInfo?->id,
                'created_by'       => $guru1->id,
                'start_time'       => now()->subMinutes(5),   // sudah mulai 5 menit lalu
                'end_time'         => now()->addHours(5),      // berakhir 5 jam lagi
                'duration_minutes' => 90,
                'total_questions'  => 5,
                'kkm_score'        => 70,
                'status'           => 'aktif',
                'shuffle_question' => false,
                'shuffle_option'   => false,
                'show_result'      => true,
                'max_attempts'     => 1,
                'instructions'     => 'Kerjakan soal dengan jujur. Dilarang membuka tab lain.',
            ]
        );

        // ── Soal untuk Ujian 1 ────────────────────────────────────────────────
        $questionsExam1 = [
            [
                'question'       => 'Apa kepanjangan dari CPU?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => 'Central Processing Unit'],
                    ['key' => 'B', 'text' => 'Computer Personal Unit'],
                    ['key' => 'C', 'text' => 'Central Program Utility'],
                    ['key' => 'D', 'text' => 'Core Processing Unit'],
                ]),
                'correct_answer' => 'A',
                'explanation'    => 'CPU adalah singkatan dari Central Processing Unit, yaitu otak dari komputer.',
                'order'          => 1,
            ],
            [
                'question'       => 'Manakah yang termasuk perangkat keras (hardware)?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => 'Microsoft Word'],
                    ['key' => 'B', 'text' => 'Monitor'],
                    ['key' => 'C', 'text' => 'Sistem Operasi'],
                    ['key' => 'D', 'text' => 'Browser'],
                ]),
                'correct_answer' => 'B',
                'explanation'    => 'Monitor adalah perangkat keras output yang menampilkan informasi secara visual.',
                'order'          => 2,
            ],
            [
                'question'       => 'RAM adalah singkatan dari?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => 'Read Access Memory'],
                    ['key' => 'B', 'text' => 'Random Access Memory'],
                    ['key' => 'C', 'text' => 'Rapid Access Module'],
                    ['key' => 'D', 'text' => 'Read And Memory'],
                ]),
                'correct_answer' => 'B',
                'explanation'    => 'RAM (Random Access Memory) adalah memori sementara yang digunakan komputer saat berjalan.',
                'order'          => 3,
            ],
            [
                'question'       => 'Sistem Operasi termasuk dalam kategori?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => 'Perangkat Keras'],
                    ['key' => 'B', 'text' => 'Perangkat Lunak Sistem'],
                    ['key' => 'C', 'text' => 'Perangkat Input'],
                    ['key' => 'D', 'text' => 'Perangkat Output'],
                ]),
                'correct_answer' => 'B',
                'explanation'    => 'Sistem Operasi adalah perangkat lunak sistem yang mengelola sumber daya komputer.',
                'order'          => 4,
            ],
            [
                'question'       => 'Jelaskan perbedaan antara perangkat keras dan perangkat lunak!',
                'type'           => 'essay',
                'options'        => null,
                'correct_answer' => 'Perangkat keras adalah komponen fisik komputer yang dapat disentuh, sedangkan perangkat lunak adalah program atau instruksi yang tidak berwujud fisik.',
                'explanation'    => null,
                'order'          => 5,
            ],
        ];

        $this->attachQuestionsToExam($exam1, $questionsExam1);

        // ── Ujian 2: Matematika Bab 2 (Aktif, sudah bisa diakses) ────────────────
        $exam2 = Exam::updateOrCreate(
            ['token' => 'MATBAB002'],
            [
                'titles'           => 'Ujian Matematika Bab 2 - Aljabar Dasar',
                'description'      => 'Ujian ini menguji kemampuan siswa dalam memahami konsep aljabar dasar termasuk persamaan linear.',
                'token'            => 'MATBAB002',
                'category_id'      => $catMath?->id,
                'created_by'       => $guru2->id,
                'start_time'       => now()->subMinutes(15),  // sudah mulai 15 menit lalu
                'end_time'         => now()->addHours(4),      // berakhir 4 jam lagi
                'duration_minutes' => 60,
                'total_questions'  => 5,
                'kkm_score'        => 75,
                'status'           => 'aktif',
                'shuffle_question' => true,
                'shuffle_option'   => true,
                'show_result'      => true,
                'max_attempts'     => 1,
                'instructions'     => 'Gunakan kalkulator jika diperlukan. Tulis langkah pengerjaan untuk soal essay.',
            ]
        );

        // ── Soal untuk Ujian 2 ────────────────────────────────────────────────
        $questionsExam2 = [
            [
                'question'       => 'Jika x + 5 = 12, maka nilai x adalah?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => '5'],
                    ['key' => 'B', 'text' => '6'],
                    ['key' => 'C', 'text' => '7'],
                    ['key' => 'D', 'text' => '8'],
                ]),
                'correct_answer' => 'C',
                'explanation'    => 'x = 12 - 5 = 7',
                'order'          => 1,
            ],
            [
                'question'       => 'Hasil dari 3x + 2x adalah?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => '5x'],
                    ['key' => 'B', 'text' => '6x'],
                    ['key' => 'C', 'text' => '5x²'],
                    ['key' => 'D', 'text' => '6x²'],
                ]),
                'correct_answer' => 'A',
                'explanation'    => 'Suku sejenis dijumlahkan: 3x + 2x = 5x',
                'order'          => 2,
            ],
            [
                'question'       => 'Persamaan 2x = 10 memiliki solusi x = ?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => '4'],
                    ['key' => 'B', 'text' => '5'],
                    ['key' => 'C', 'text' => '6'],
                    ['key' => 'D', 'text' => '20'],
                ]),
                'correct_answer' => 'B',
                'explanation'    => 'x = 10 ÷ 2 = 5',
                'order'          => 3,
            ],
            [
                'question'       => 'Apakah pernyataan "Aljabar adalah cabang matematika" benar?',
                'type'           => 'true_false',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => 'Benar'],
                    ['key' => 'B', 'text' => 'Salah'],
                ]),
                'correct_answer' => 'A',
                'explanation'    => 'Benar. Aljabar adalah salah satu cabang utama matematika.',
                'order'          => 4,
            ],
            [
                'question'       => 'Selesaikan persamaan: 3x - 4 = 11. Tunjukkan langkah-langkahnya!',
                'type'           => 'essay',
                'options'        => null,
                'correct_answer' => '3x = 11 + 4 = 15, maka x = 15 ÷ 3 = 5',
                'explanation'    => null,
                'order'          => 5,
            ],
        ];

        $this->attachQuestionsToExam($exam2, $questionsExam2);

        // ── Ujian 3: Pemrograman Web HTML & CSS (Aktif untuk testing) ────────────
        $exam3 = Exam::updateOrCreate(
            ['token' => 'AKTIF003'],
            [
                'titles'           => 'Ujian Pemrograman Web - HTML & CSS',
                'description'      => 'Ujian ini menguji pemahaman siswa tentang dasar-dasar pemrograman web menggunakan HTML dan CSS.',
                'token'            => 'AKTIF003',
                'category_id'      => $catInfo?->id,
                'created_by'       => $guru1->id,
                'start_time'       => now()->subMinutes(10),
                'end_time'         => now()->addHours(3),
                'duration_minutes' => 60,
                'total_questions'  => 5,
                'kkm_score'        => 70,
                'status'           => 'aktif',
                'shuffle_question' => false,
                'shuffle_option'   => false,
                'show_result'      => true,
                'max_attempts'     => 1,
                'instructions'     => 'Kerjakan soal dengan teliti. Waktu 60 menit.',
            ]
        );

        $questionsExam3 = [
            [
                'question'       => 'Tag HTML yang digunakan untuk membuat judul terbesar adalah?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => '<h6>'],
                    ['key' => 'B', 'text' => '<h1>'],
                    ['key' => 'C', 'text' => '<title>'],
                    ['key' => 'D', 'text' => '<header>'],
                ]),
                'correct_answer' => 'B',
                'explanation'    => '<h1> adalah tag heading terbesar dalam HTML.',
                'order'          => 1,
            ],
            [
                'question'       => 'Property CSS yang digunakan untuk mengubah warna teks adalah?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => 'background-color'],
                    ['key' => 'B', 'text' => 'font-color'],
                    ['key' => 'C', 'text' => 'color'],
                    ['key' => 'D', 'text' => 'text-color'],
                ]),
                'correct_answer' => 'C',
                'explanation'    => 'Property color digunakan untuk mengubah warna teks dalam CSS.',
                'order'          => 2,
            ],
            [
                'question'       => 'Atribut HTML yang digunakan untuk menambahkan link adalah?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => 'src'],
                    ['key' => 'B', 'text' => 'href'],
                    ['key' => 'C', 'text' => 'link'],
                    ['key' => 'D', 'text' => 'url'],
                ]),
                'correct_answer' => 'B',
                'explanation'    => 'Atribut href pada tag <a> digunakan untuk menentukan URL tujuan link.',
                'order'          => 3,
            ],
            [
                'question'       => 'CSS adalah singkatan dari?',
                'type'           => 'multiple',
                'options'        => json_encode([
                    ['key' => 'A', 'text' => 'Computer Style Sheets'],
                    ['key' => 'B', 'text' => 'Creative Style Sheets'],
                    ['key' => 'C', 'text' => 'Cascading Style Sheets'],
                    ['key' => 'D', 'text' => 'Colorful Style Sheets'],
                ]),
                'correct_answer' => 'C',
                'explanation'    => 'CSS adalah singkatan dari Cascading Style Sheets.',
                'order'          => 4,
            ],
            [
                'question'       => 'Jelaskan perbedaan antara HTML dan CSS dalam pembuatan website!',
                'type'           => 'essay',
                'options'        => null,
                'correct_answer' => 'HTML adalah bahasa markup untuk membuat struktur konten website, sedangkan CSS adalah bahasa stylesheet untuk mengatur tampilan dan gaya visual dari konten HTML tersebut.',
                'explanation'    => null,
                'order'          => 5,
            ],
        ];

        $this->attachQuestionsToExam($exam3, $questionsExam3);
    }

    /**
     * Buat soal dan hubungkan ke exam via pivot table exam_question.
     */
    private function attachQuestionsToExam(Exam $exam, array $questionsData): void
    {
        // Hapus relasi lama agar idempotent saat re-seed
        $exam->questions()->detach();

        foreach ($questionsData as $qData) {
            $question = Questions::create([
                'question'       => $qData['question'],
                'type'           => $qData['type'],
                'options'        => $qData['options'],
                'correct_answer' => $qData['correct_answer'],
                'explanation'    => $qData['explanation'],
                'order'          => $qData['order'],
                'is_active'      => true,
            ]);

            // Hubungkan ke exam via pivot
            $exam->questions()->attach($question->id, ['order' => $qData['order']]);
        }
    }
}
