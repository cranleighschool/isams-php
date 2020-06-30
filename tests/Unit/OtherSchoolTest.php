<?php

namespace spkm\isams\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use spkm\isams\Controllers\OtherSchoolController;
use spkm\isams\School;
use Tests\TestCase;

class OtherSchoolTest extends TestCase
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
        'addressLine1',
        'addressLine2',
        'addressLine3',
        'country',
        'county',
        'postcode',
        'primaryContact',
        'schoolCode',
        'schoolEmail',
        'schoolName',
        'schoolTelephone',
        'schoolWebsite',
        'town',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->school = new School();
    }

    /** @test */
    public function it_returns_all_other_schools_as_a_collection_of_school_classes_stored_in_cache()
    {
        $schools = (new OtherSchoolController($this->school))->index();

        foreach ($schools as $school):
            $this->assertTrue(is_a($school, \spkm\isams\Wrappers\School::class));

        foreach ($this->properties as $property):
                $this->assertTrue(array_key_exists($property, $school));
        endforeach;
        endforeach;
        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'admissionApplicants.index'));
    }

    /** @test */
    public function it_creates_a_new_school_and_returns_its_id()
    {
        $response = (new OtherSchoolController($this->school))->store([
            'schoolName' => 'test_' . str_random(10),
            'schoolCode' => 'SB',
            'schoolTelephone' => '01010101010',
            'postcode' => 'ZZ99 3WZ',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertNotEmpty(json_decode($response->getContent())->location);
        $this->assertNotEmpty(json_decode($response->getContent())->id);
    }

    /** @test */
    public function it_returns_the_specified_school()
    {
        $attributes = [
            'schoolName' => 'test_' . str_random(10),
            'schoolCode' => 'SB',
            'schoolTelephone' => '01010101010',
            'postcode' => 'ZZ99 3WZ',
        ];
        $response = (new OtherSchoolController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;

        $school = (new OtherSchoolController($this->school))->show($id);

        $this->assertTrue(is_a($school, \spkm\isams\Wrappers\School::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($school, $property));
        endforeach;

        foreach ($attributes as $key => $value):
            $this->assertTrue($school->$key == $value);
        endforeach;
    }

    /** @test */
    public function it_updates_the_specified_school()
    {
        $attributes = [
            'schoolName' => 'test_' . str_random(10),
            'schoolCode' => 'SB',
            'schoolTelephone' => '01010101010',
            'postcode' => 'ZZ99 3WZ',
        ];
        $response = (new OtherSchoolController($this->school))->store($attributes);
        $id = json_decode($response->getContent())->id;

        $changedAttributes = [
            'schoolName' => 'testAnother_' . str_random(5),
            'schoolCode' => 'TEST',
            'schoolTelephone' => '01010101010',
            'postcode' => 'ZZ99 3WZ',
        ];

        (new OtherSchoolController($this->school))->update($id, $changedAttributes);

        $school = (new OtherSchoolController($this->school))->show($id);

        $this->assertTrue(is_a($school, \spkm\isams\Wrappers\School::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($school, $property));
        endforeach;

        foreach ($changedAttributes as $key => $value):
            $this->assertTrue($school->$key == $value);
        endforeach;
    }
}
