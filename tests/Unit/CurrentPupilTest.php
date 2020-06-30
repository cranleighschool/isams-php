<?php

namespace spkm\isams\Tests\Unit;

use Tests\TestCase;
use spkm\isams\School;
use spkm\isams\Wrappers\Pupil;
use spkm\isams\Controllers\CurrentPupilController;

class CurrentPupilTest extends TestCase
{
    /**
     * @var School
     */
    protected $school;

    /**
     * @var array
     */
    protected $properties = [
        'academicHouse',
        'birthCounty',
        'birthplace',
        'boardingHouse',
        'dob',
        'enrolmentDate',
        'enrolmentTerm',
        'enrolmentYear',
        'ethnicity',
        'familyId',
        'forename',
        'formGroup',
        'fullName',
        'gender',
        'homeAddresses',
        'languages',
        'lastUpdated',
        'latestPhotoId',
        'leavingDate',
        'middlenames',
        'mobileNumber',
        'nationalities',
        'officialName',
        'personalEmailAddress',
        'personGuid',
        'preferredName',
        'previousName',
        'religion',
        'residentCountry',
        'schoolCode',
        'schoolEmailAddress',
        'schoolId',
        'surname',
        'title',
        'tutorEmployeeId',
        'uniquePupilNumber',
        'yearGroup',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->school = new School();
    }

    /** @test */
    public function it_returns_all_current_pupils_as_a_collection_of_pupil_classes_stored_in_cache()
    {
        $pupils = (new CurrentPupilController($this->school))->index();

        foreach ($pupils as $pupil):
            $this->assertTrue(is_a($pupil, Pupil::class));

        foreach ($this->properties as $property):
                $this->assertTrue(array_key_exists($property, $pupil->toArray()));
        endforeach;
        endforeach;
        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'currentPupils.index'));
    }

    /** @test */
    public function it_creates_a_new_pupil_and_returns_its_schoolid()
    {
        $response = (new CurrentPupilController($this->school))->store([
            'forename' => 'John',
            'surname' => 'Doe',
            'dob' => '2010-12-01',
            'yearGroup' => 8
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertNotEmpty(json_decode($response->getContent())->location);
        $this->assertNotEmpty(json_decode($response->getContent())->id);
    }

    /** @test */
    public function it_returns_the_specified_pupil()
    {
        $attributes = [
            'forename' => 'Jane',
            'surname' => 'Doe',
            'dob' => '2010-12-01',
            'yearGroup' => 8
        ];
        $response = (new CurrentPupilController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;

        $pupil = (new CurrentPupilController($this->school))->show($id);

        $this->assertTrue(is_a($pupil, Pupil::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($pupil, $property));
        endforeach;

        foreach ($attributes as $key => $value):
            $this->assertTrue($pupil->$key == $value);
        endforeach;
    }

    /** @test */
    public function it_updates_the_specified_pupil()
    {
        $attributes = [
            'forename' => 'Jane',
            'surname' => 'Doe',
            'dob' => '2010-12-01',
            'yearGroup' => 8
        ];
        $response = (new CurrentPupilController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;

        $changedAttributes = [
            'forename' => 'Jenny',
            'surname' => 'Dolly',
            'dob' => '2012-12-01',
            'yearGroup' => 8
        ];

        (new CurrentPupilController($this->school))->update($id, $changedAttributes);

        $pupil = (new CurrentPupilController($this->school))->show($id);

        $this->assertTrue(is_a($pupil, Pupil::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($pupil, $property));
        endforeach;

        foreach ($changedAttributes as $key => $value):
            $this->assertTrue($pupil->$key == $value);
        endforeach;
    }
}
