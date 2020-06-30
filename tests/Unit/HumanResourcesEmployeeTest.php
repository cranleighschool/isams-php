<?php

namespace spkm\isams\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use spkm\isams\Controllers\HumanResourcesEmployeeController;
use spkm\isams\School;
use spkm\isams\Wrappers\Employee;
use Tests\TestCase;

class HumanResourcesEmployeeTest extends TestCase
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
        'currentContractId',
        'dateOfBirth',
        'enrolmentDate',
        'forename',
        'gender',
        'isPartTime',
        'isPersonalTutor',
        'isTeachingStaff',
        'latestPhotoId',
        'leavingDate',
        'middleNames',
        'nationalities',
        'personalAddress1',
        'personalAddress2',
        'personalAddress3',
        'personalCountry',
        'personalCounty',
        'personalEmailAddress',
        'personalMobileNumber',
        'personalPostcode',
        'personalTelephoneNumber',
        'personalTown',
        'personGuid',
        'preferredName',
        'previousFamilyNames',
        'schoolEmailAddress',
        'schoolInitials',
        'schoolMobileNumber',
        'schoolTelephoneNumber',
        'surname',
        'systemStatus',
        'title',

    ];

    public function __construct()
    {
        parent::__construct();

        $this->school = new School();
    }

    /** @test */
    public function it_returns_all_employees_as_a_collection_of_employee_classes_stored_in_cache()
    {
        $employees = (new HumanResourcesEmployeeController($this->school))->index();

        foreach ($employees as $employee):
            $this->assertTrue(is_a($employee, Employee::class));

        foreach ($this->properties as $property):
                $this->assertTrue(array_key_exists($property, $employee));
        endforeach;
        endforeach;
        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'hrEmployees.index'));
    }

    /** @test */
    public function it_creates_a_new_employee_and_returns_its_id()
    {
        $response = (new HumanResourcesEmployeeController($this->school))->store([
            'forename' => 'John',
            'surname' => 'Doe',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertNotEmpty(json_decode($response->getContent())->location);
        $this->assertNotEmpty(json_decode($response->getContent())->id);
    }

    /** @test */
    public function it_returns_the_specified_employee()
    {
        $attributes = [
            'forename' => 'Jane',
            'surname' => 'Doe',
        ];
        $response = (new HumanResourcesEmployeeController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;

        $employee = (new HumanResourcesEmployeeController($this->school))->show($id);

        $this->assertTrue(is_a($employee, Employee::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($employee, $property));
        endforeach;

        foreach ($attributes as $key => $value):
            $this->assertTrue($employee->$key == $value);
        endforeach;
    }

    /** @test */
    public function it_updates_the_specified_employee()
    {
        $attributes = [
            'forename' => 'Jane',
            'surname' => 'Doe',
        ];
        $response = (new HumanResourcesEmployeeController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;

        $changedAttributes = [
            'forename' => 'Jane',
            'surname' => 'Dolly',
        ];

        (new HumanResourcesEmployeeController($this->school))->update($id, $changedAttributes);

        $employee = (new HumanResourcesEmployeeController($this->school))->show($id);

        $this->assertTrue(is_a($employee, Employee::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($employee, $property));
        endforeach;

        foreach ($changedAttributes as $key => $value):
            $this->assertTrue($employee->$key == $value);
        endforeach;
    }
}
