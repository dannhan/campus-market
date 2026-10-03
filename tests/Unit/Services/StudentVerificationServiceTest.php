<?php

namespace Tests\Unit\Services;

use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Services\Verification\DatabaseStudentVerificationProvider;
use App\Services\Verification\StudentVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected University $undip;
    protected University $ugm;
    protected Student $student;
    protected StudentVerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->undip = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);

        $this->ugm = University::create([
            'name' => 'Universitas Gadjah Mada',
            'slug' => 'ugm',
            'email_domain' => 'mail.ugm.ac.id',
        ]);

        $this->student = Student::create([
            'university_id' => $this->undip->id,
            'student_number' => '24060122130001',
            'full_name' => 'Budi Santoso',
            'major' => 'Informatika',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'is_active' => true,
        ]);

        $this->service = new StudentVerificationService(
            new DatabaseStudentVerificationProvider()
        );
    }

    public function test_successful_verification(): void
    {
        $result = $this->service->verify(
            university: $this->undip,
            studentNumber: '24060122130001',
            email: 'budi.santoso@students.undip.ac.id',
            name: 'Budi Santoso'
        );

        $this->assertTrue($result->isSuccess());
        $this->assertEquals($this->student->id, $result->getStudent()->id);
    }

    public function test_fails_when_email_domain_does_not_match_university(): void
    {
        $result = $this->service->verify(
            university: $this->undip,
            studentNumber: '24060122130001',
            email: 'budi.santoso@gmail.com',
            name: 'Budi Santoso'
        );

        $this->assertTrue($result->isFailed());
        $this->assertArrayHasKey('university_email', $result->getErrors());
    }

    public function test_fails_when_student_number_does_not_exist(): void
    {
        $result = $this->service->verify(
            university: $this->undip,
            studentNumber: '0000000000',
            email: 'budi.santoso@students.undip.ac.id',
            name: 'Budi Santoso'
        );

        $this->assertTrue($result->isFailed());
        $this->assertArrayHasKey('student_number', $result->getErrors());
    }

    public function test_fails_when_student_belongs_to_another_university(): void
    {
        $ugmStudent = Student::create([
            'university_id' => $this->ugm->id,
            'student_number' => '22/498765/TK/54321',
            'full_name' => 'Dewi Lestari',
            'major' => 'Teknik Industri',
            'university_email' => 'dewi@mail.ugm.ac.id',
            'is_active' => true,
        ]);

        // Attempt to verify with UNDIP instead of UGM
        $result = $this->service->verify(
            university: $this->undip,
            studentNumber: '22/498765/TK/54321',
            email: 'dewi@students.undip.ac.id',
            name: 'Dewi Lestari'
        );

        $this->assertTrue($result->isFailed());
        $this->assertStringContainsString('berbeda', $result->getMessage());
    }

    public function test_fails_when_email_does_not_match_student_nim(): void
    {
        $result = $this->service->verify(
            university: $this->undip,
            studentNumber: '24060122130001',
            email: 'other.student@students.undip.ac.id',
            name: 'Budi Santoso'
        );

        $this->assertTrue($result->isFailed());
        $this->assertArrayHasKey('university_email', $result->getErrors());
    }

    public function test_fails_when_student_is_already_registered(): void
    {
        User::create([
            'name' => $this->student->full_name,
            'email' => $this->student->university_email,
            'password' => bcrypt('password'),
            'student_id' => $this->student->id,
        ]);

        $result = $this->service->verify(
            university: $this->undip,
            studentNumber: '24060122130001',
            email: 'budi.santoso@students.undip.ac.id',
            name: 'Budi Santoso'
        );

        $this->assertTrue($result->isFailed());
        $this->assertStringContainsString('sudah terdaftar', $result->getMessage());
    }

    public function test_fails_when_name_is_completely_different(): void
    {
        $result = $this->service->verify(
            university: $this->undip,
            studentNumber: '24060122130001',
            email: 'budi.santoso@students.undip.ac.id',
            name: 'Zack Snyder'
        );

        $this->assertTrue($result->isFailed());
        $this->assertArrayHasKey('name', $result->getErrors());
    }
}
