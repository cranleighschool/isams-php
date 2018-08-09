<?php

namespace Tests\Unit;

use Tests\TestCase;
use spkm\isams\School;
use spkm\isams\Wrappers\Applicant;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Controllers\AdmissionApplicantController;

class AdmissionApplicantTest extends TestCase
{
    /**
     * @var School
     */
    protected $school;

    /**
     * @var array
     */
    protected $properties = [
        'admissionStatus',
        'boardingStatus',
        'currentSchoolId',
        'dateOfBirth',
        'enrolmentAcademicHouseId',
        'enrolmentBoardingHouseId',
        'enrolmentSchoolForm',
        'enrolmentSchoolTerm',
        'enrolmentSchoolYear',
        'enrolmentSchoolYearGroup',
        'familyId',
        'forename',
        'fullName',
        'gender',
        'initials',
        'isReadmission',
        'languages',
        'lastUpdated',
        'middleNames',
        'nationalities',
        'preferredName',
        'registeredDate',
        'residentCountry',
        'schoolCode',
        'schoolId',
        'surname',
        'title',
        'uniquePupilNumber',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->school = new School;
    }

    /** @test */
    public function it_returns_all_applicants_as_a_collection_of_applicant_classes_stored_in_cache()
    {
        $applicants = (new AdmissionApplicantController($this->school))->index();

        foreach ($applicants as $applicant):
            $this->assertTrue(is_a($applicant, Applicant::class));

            foreach ($this->properties as $property):
                $this->assertTrue(array_key_exists($property, $applicant));
            endforeach;
        endforeach;
        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'admissionApplicants.index'));
    }

    /** @test */
    public function it_creates_a_new_applicant_and_returns_its_schoolid()
    {
        $response = (new AdmissionApplicantController($this->school))->store([
            'forename' => 'John',
            'surname' => 'Doe',
            'preferredName' => 'Lil Johnny',
            'admissionStatus' => '2. Registered',
            'boardingStatus' => 'Day',
            'gender' => 'M',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertNotEmpty(json_decode($response->getContent())->location);
        $this->assertNotEmpty(json_decode($response->getContent())->id);
    }

    /** @test */
    public function it_returns_the_specified_applicant()
    {
        $attributes = [
            'forename' => 'Jane',
            'surname' => 'Doe',
            'preferredName' => 'Queeny Jane',
            'admissionStatus' => '2. Registered',
            'boardingStatus' => 'Day',
            'gender' => 'F',
        ];
        $response = (new AdmissionApplicantController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;


        $applicant = (new AdmissionApplicantController($this->school))->show($id);

        $this->assertTrue(is_a($applicant, Applicant::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($applicant, $property));
        endforeach;

        foreach ($attributes as $key => $value):
            $this->assertTrue($applicant->$key == $value);
        endforeach;
    }

    /** @test */
    public function it_updates_the_specified_applicant()
    {
        $attributes = [
            'forename' => 'Jane',
            'surname' => 'Doe',
            'preferredName' => 'Queeny Jane',
            'admissionStatus' => '2. Registered',
            'boardingStatus' => 'Day',
            'gender' => 'F',
        ];
        $response = (new AdmissionApplicantController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;


        $changedAttributes = [
            'forename' => 'Jane',
            'surname' => 'Dolly',
            'preferredName' => 'Queen Jane',
            'admissionStatus' => '2. Registered',
            'boardingStatus' => 'Boarder',
            'gender' => 'F',
        ];

        (new AdmissionApplicantController($this->school))->update($id,$changedAttributes);

        $applicant = (new AdmissionApplicantController($this->school))->show($id);

        $this->assertTrue(is_a($applicant, Applicant::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($applicant, $property));
        endforeach;

        foreach ($changedAttributes as $key => $value):
            $this->assertTrue($applicant->$key == $value);
        endforeach;
    }
}
