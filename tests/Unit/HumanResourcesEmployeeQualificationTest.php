<?php

namespace spkm\isams\Tests\Unit;

use spkm\isams\Controllers\HumanResourcesEmployeeController;
use spkm\isams\Controllers\HumanResourcesEmployeeQualificationController;
use spkm\isams\School;
use spkm\isams\Wrappers\EmployeeQualification;
use spkm\isams\Tests\TestCase;

class HumanResourcesEmployeeQualificationTest extends TestCase
{
    /**
     * @var School
     */
    protected $school;

    /**
     * @var array
     */
    protected $properties = [
        'id',
        'dateAwarded',
        'level',
        'name',
        'subjectOne',
        'subjectTwo',
        'title',
        'universityId',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->school = new School();
    }

    /** @test */
    public function it_creates_a_new_qualification_for_the_specified_employee_and_returns_its_id()
    {
        //Create Employee
        $attributes = [
            'forename' => 'Jane',
            'surname' => 'Doe',
        ];
        $response = (new HumanResourcesEmployeeController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;

        //Create qualification
        $response = (new HumanResourcesEmployeeQualificationController($this->school))->store($id, [
            'dateAwarded' => now()->toDateString(),
            'name' => 'BSc',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertNotEmpty(json_decode($response->getContent())->location);
        $this->assertNotEmpty(json_decode($response->getContent())->id);
    }

    /** @test */
    public function it_returns_the_specified_employees_qualifications()
    {
        //Create Employee
        $attributes = [
            'forename' => 'Jane',
            'surname' => 'Doe',
        ];
        $response = (new HumanResourcesEmployeeController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;

        //Create qualification
        (new HumanResourcesEmployeeQualificationController($this->school))->store($id, [
            'dateAwarded' => now()->toDateString(),
            'name' => 'BSc',
        ]);

        $employee = (new HumanResourcesEmployeeQualificationController($this->school))->show($id);

        $this->assertTrue(is_a($employee, EmployeeQualification::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($employee, $property));
        endforeach;
    }
}
