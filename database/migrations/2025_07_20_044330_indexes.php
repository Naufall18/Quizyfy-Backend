    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up()
        {
            Schema::table('exams', function (Blueprint $table) {
                $table->index(['status', 'start_time']);
                $table->index(['created_by', 'status']);
                $table->index('token');
            });
            
                    Schema::table('user_exams', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'user_exams_user_status_idx');
            $table->index(['exam_id', 'status'], 'user_exams_exam_status_idx');
            $table->index('started_at', 'user_exams_started_at_idx');
            $table->index('finished_at', 'user_exams_finished_at_idx');
});
            
            Schema::table('exam_results', function (Blueprint $table) {
                $table->index(['teacher_id', 'completed_at']);
                $table->index(['user_id', 'completed_at']);
                $table->index('is_passed');
            });
            
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->index(['user_id', 'status']);
                $table->index(['status', 'end_date']);
                $table->index('payment_status');
            });
            
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['user_id', 'is_read']);
                $table->index('created_at');
            });
            
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['user_id', 'created_at']);
                $table->index(['action', 'created_at']);
            });
        }
        /**
         * Reverse the migrations.
         * 
         */
        public function down(): void
        {
            //
        }
    };
