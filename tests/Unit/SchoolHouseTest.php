<?php

namespace Tests\Unit;

use Tests\TestCase;
use spkm\isams\School;
use spkm\isams\Wrappers\House;
use spkm\isams\Controllers\SchoolHouseController;

class SchoolHouseTest extends TestCase
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
        'addressLine',
        'assistantHouseMasterEmployeeId',
        'code',
        'county',
        'email',
        'fax',
        'gender',
        'houseMasterEmployeeId',
        'name',
        'postcode',
        'telephone',
        'town',
        'type',
        'website',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->school = new School;
    }

    /** @test */
    public function it_returns_all_houses_as_a_collection_of_house_classes_stored_in_cache()
    {
        $houses = (new SchoolHouseController($this->school))->index();

        foreach ($houses as $house):
            $this->assertTrue(is_a($house, House::class));

            foreach ($this->properties as $property):
                $this->assertTrue(array_key_exists($property, $house));
            endforeach;
        endforeach;
        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'schoolHouses.index'));
    }

    /** @test */
    public function it_returns_the_specified_house()
    {
        $houses = (new SchoolHouseController($this->school))->index();
        $id = $houses->first()->id;

        $house = (new SchoolHouseController($this->school))->show($id);

        $this->assertTrue(is_a($house, House::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($house, $property));
        endforeach;
    }
}
