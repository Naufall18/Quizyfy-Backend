<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
use App\Models\UserExam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test ExamController:
 * guruIndex, toggleStatus, statistics, store, destroy
 */
class ExamControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeGuru(): array
    {
        $guru  = User::factory()->create(['role' => 'guru']);
        $token = $guru->createToken('test')->plainTextToken;
        return [$guru, $token];
    }

    // ─── guruIndex ───────────────────────────────────────────────

    public function test_guru_can_list_own_exams(): void
    {
        [$guru, $token] = $this->makeGuru();
        Exam::factory()->count(3)->create(['created_by' => $guru->id]);

        // Ujian guru lain — tidak boleh muncul
        $other = User::factory()->create(['role' => 'guru']);
        Exam::factory()->count(2)->create(['created_by' => $other->id]);

        $res = $this->withToken($token)->getJson('/api/guru/exams');
        $res->assertStatus(200);

        $data = $res->json('data');
        $this->assertCount(3, $data);
        foreach ($data as $exam) {
            $this->assertEquals($guru->id, $exam['created_by'] ?? $guru->id);
        }
    }

    public function test_guru_can_filter_exams_by_status(): void
    {
        [$guru, $token] = $this->makeGuru();
        Exam::factory()->create(['created_by' => $guru->id, 'status' => 'aktif']);
        Exam::factory()->create(['created_by' => $guru->id, 'status' => 'draft']);

        $res = $this->withToken($token)->getJson('/api/guru/exams?status=aktif');
        $res->assertStatus(200);

        foreach ($res->json('data') as $exam) {
            $this->assertEquals('aktif', $exam['status']);
        }
    }

    public function test_guru_can_search_exams(): void
    {
        [$guru, $token] = $this->makeGuru();
        Exam::factory()->create(['created_by' => $guru->id, 'titles' => 'Ujian Matematika Bab 1']);
        Exam::factory()->create(['created_by' => $guru->id, 'titles' => 'Ujian Fisika Bab 2']);

        $res = $this->withToken($token)->getJson('/api/guru/exams?search=Matematika');
        $res->assertStatus(200);

        $data = $res->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsStringIgnoringCase('Matematika', $data[0]['titles']);
    }

    public function test_siswa_cannot_access_guru_exams_list(): void
    {
        $siswa = User::factory()->create(['role' => 'user']);
        $token = $siswa->createToken('test')->plainTextToken;

        $res = $this->withToken($token)->getJson('/api/guru/exams');
        $res->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_guru_exams(): void
    {
        $res = $this->getJson('/api/guru/exams');
        $res->assertStatus(401);
    }

    // ─── toggleStatus ────────────────────────────────────────────

    public function test_guru_can_toggle_exam_status_to_draft(): void
    {
        [$guru, $token] = $this->makeGuru();
        $exam = Exam::factory()->create(['created_by' => $guru->id, 'status' => 'aktif']);

        $res = $this->withToken($token)->patchJson("/api/guru/exams/{$exam->id}/toggle");
        $res->assertStatus(200);

        $this->assertDatabaseHas('exams', ['id' => $exam->id, 'status' => 'draft']);
    }

    public function test_guru_can_toggle_exam_status_to_aktif(): void
    {
        [$guru, $token] = $this->makeGuru();
        $exam = Exam::factory()->create(['created_by' => $guru->id, 'status' => 'draft']);

        $res = $this->withToken($token)->patchJson("/api/guru/exams/{$exam->id}/toggle");
        $res->assertStatus(200);

        $this->assertDatabaseHas('exams', ['id' => $exam->id, 'status' => 'aktif']);
    }

    public function test_guru_cannot_toggle_other_gurus_exam(): void
    {
        [$guru, $token] = $this->makeGuru();
        $other          = User::factory()->create(['role' => 'guru']);
        $exam           = Exam::factory()->create(['created_by' => $other->id, 'status' => 'aktif']);

        $res = $this->withToken($token)->patchJson("/api/guru/exams/{$exam->id}/toggle");
        $res->assertStatus(404);
    }

    // ─── statistics ──────────────────────────────────────────────

    public function test_guru_can_get_exam_statistics(): void
    {
        [$guru, $token] = $this->makeGuru();
        $exam           = Exam::factory()->create(['created_by' => $guru->id]);

        // Simulasi 2 peserta yang selesai
        UserExam::factory()->count(2)->create([
            'exam_id'    => $exam->id,
            'status'     => 'completed',
            'score'      => 80,
        ]);

        $res = $this->withToken($token)->getJson("/api/guru/exams/{$exam->id}/statistics");
        $res->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'total_peserta', 'lulus', 'tidak_lulus',
                'pass_rate', 'rata_rata_nilai', 'kkm',
            ]]);

        $this->assertEquals(2, $res->json('data.total_peserta'));
    }

    public function test_statistics_returns_zeros_for_empty_exam(): void
    {
        [$guru, $token] = $this->makeGuru();
        $exam           = Exam::factory()->create(['created_by' => $guru->id]);

        $res = $this->withToken($token)->getJson("/api/guru/exams/{$exam->id}/statistics");
        $res->assertStatus(200);

        $this->assertEquals(0, $res->json('data.total_peserta'));
        $this->assertEquals(0, $res->json('data.rata_rata_nilai'));
    }

    // ─── store ───────────────────────────────────────────────────

    public function test_guru_can_create_exam(): void
    {
        [$guru, $token] = $this->makeGuru();

        $res = $this->withToken($token)->postJson('/api/guru/exams', [
            'titles'           => 'Ujian Bab 1',
            'description'      => 'Ujian pertama',
            'duration_minutes' => 60,
            'kkm_score'        => 75,
            'status'           => 'draft',
        ]);

        $res->assertStatus(201);
        $this->assertDatabaseHas('exams', ['titles' => 'Ujian Bab 1', 'created_by' => $guru->id]);
    }

    public function test_create_exam_requires_title(): void
    {
        [, $token] = $this->makeGuru();

        $res = $this->withToken($token)->postJson('/api/guru/exams', [
            'duration_minutes' => 60,
        ]);

        $res->assertStatus(422);
    }

    // ─── destroy ─────────────────────────────────────────────────

    public function test_guru_can_delete_own_draft_exam(): void
    {
        [$guru, $token] = $this->makeGuru();
        $exam           = Exam::factory()->create(['created_by' => $guru->id, 'status' => 'draft']);

        $res = $this->withToken($token)->deleteJson("/api/guru/exams/{$exam->id}");
        $res->assertStatus(200);

        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
    }

    public function test_guru_cannot_delete_other_gurus_exam(): void
    {
        [$guru, $token] = $this->makeGuru();
        $other          = User::factory()->create(['role' => 'guru']);
        $exam           = Exam::factory()->create(['created_by' => $other->id]);

        $res = $this->withToken($token)->deleteJson("/api/guru/exams/{$exam->id}");
        $res->assertStatus(403);
    }
}
