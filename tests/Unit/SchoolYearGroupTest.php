<?php

namespace spkm\isams\Tests\Unit;

use Tests\TestCase;
use spkm\isams\School;
use spkm\isams\Wrappers\YearGroup;
use spkm\isams\Controllers\SchoolYearGroupController;

class SchoolYearGroupTest extends TestCase
{
    /**
     * @var School
     */
    protected $school;

    /**
     * @var array
     */
    protected $properties = [
        'assistantTutorId',
        'averageStartingAge',
        'censusYearGroup',
        'code',
        'emailAddress',
        'iscIrelandYearGroup',
        'iscScotlandYearGroup',
        'lastUpdated',
        'name',
        'ncYear',
        'tutorId',
        'websiteAddress',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->school = new School;
    }

    /** @test */
    public function it_returns_all_year_groups_as_a_collection_of_yeargroup_classes_stored_in_cache()
    {
        $yearGroups = (new SchoolYearGroupController($this->school))->index();

        foreach ($yearGroups as $yearGroup):
            $this->assertTrue(is_a($yearGroup, YearGroup::class));

            foreach ($this->properties as $property):
                $this->assertTrue(array_key_exists($property, $yearGroup));
            endforeach;
        endforeach;
        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'schoolYearGroups.index'));
    }

    /** @test */
    public function it_returns_the_specified_year_group()
    {
        $yearGroups = (new SchoolYearGroupController($this->school))->index();
        $id = $yearGroups->first()->ncYear;

        $yearGroup = (new SchoolYearGroupController($this->school))->show($id);

        $this->assertTrue(is_a($yearGroup, YearGroup::class));
        foreach ($this->properties as $property):
            $this->assertTrue(property_exists($yearGroup, $property));
        endforeach;
    }
}
