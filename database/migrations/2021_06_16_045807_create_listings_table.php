<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title'); // Nama pekerjaan
            $table->string('slug');
            $table->string('company'); // Perusahaan
            $table->string('location');
            $table->enum('job_type', ['Full-Time', 'Internship', 'Freelance', 'Part-Time']); // Tipe Pekerjaan
            $table->enum('work_policy', ['On-Site', 'Remote', 'Hybrid']); // Kebijakan Pekerjaan
            $table->string('salary_from')->nullable(); // Gaji (Dari)
            $table->string('salary_to')->nullable(); // Gaji (Sampai)
            $table->boolean('is_highlighted')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('content');
            $table->string('apply_link');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listings');
    }
}
