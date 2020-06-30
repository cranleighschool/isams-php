<?php

namespace spkm\isams\Tests\Unit;

use spkm\isams\Controllers\HumanResourcesEmployeeController;
use spkm\isams\Controllers\HumanResourcesEmployeeRoleController;
use spkm\isams\School;
use spkm\isams\Wrappers\EmployeeRole;
use spkm\isams\Tests\TestCase;

class HumanResourcesEmployeeRoleTest extends TestCase
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
        'name',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->school = new School();
    }

    /** @test */
    public function it_associates_a_role_with_the_specified_employee_and_returns_its_id()
    {
        //Create Employee
        $attributes = [
            'forename' => 'Jane',
            'surname' => 'Doe',
        ];
        $response = (new HumanResourcesEmployeeController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;

        //Create role
        $roleId = 11; //Teacher
        $response = (new HumanResourcesEmployeeRoleController($this->school))->store($id, $roleId);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertNotEmpty(json_decode($response->getContent())->location);
        $this->assertNotEmpty(json_decode($response->getContent())->id);
    }

    /** @test */
    public function it_returns_the_specified_employees_roles()
    {
        //Create Employee
        $attributes = [
            'forename' => 'Jane',
            'surname' => 'Doe',
        ];
        $response = (new HumanResourcesEmployeeController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;

        //Create role
        $roleId = 11; //Teacher
        (new HumanResourcesEmployeeRoleController($this->school))->store($id, $roleId);

        $employee = (new HumanResourcesEmployeeRoleController($this->school))->show($id);

        $this->assertTrue(is_a($employee, EmployeeRole::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($employee, $property));
        endforeach;
    }
}
