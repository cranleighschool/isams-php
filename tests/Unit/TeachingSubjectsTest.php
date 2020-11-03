<?php

namespace spkm\isams\Tests\Unit;

use spkm\isams\Controllers\TeachingSubjectController;
use spkm\isams\School;
use spkm\isams\Tests\TestCase;
use spkm\isams\Wrappers\TeachingSubject;

class TeachingSubjectsTest extends TestCase
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
        'active',
        'code',
        'formSubject',
        'isHidden',
        'name',
        'reportingName',
        'setSubject',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->school = new School();
    }

    /** @test */
    public function it_returns_a_list_of_all_teaching_subjects()
    {
        $subjects = (new TeachingSubjectController($this->school))->index();

        foreach ($subjects as $subject) {
            $this->assertTrue(is_a($subject, TeachingSubject::class));

            foreach ($this->properties as $property) {
                $this->assertTrue(array_key_exists($property, $subject));
            }
        }
        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'teachingSubjects.index'));
    }

    /** @test */
    public function it_returns_the_specified_teaching_subject()
    {
        $houses = (new TeachingSubjectController($this->school))->index();
        $id = $houses->first()->id;

        $house = (new TeachingSubjectController($this->school))->show($id);

        $this->assertTrue(is_a($house, TeachingSubject::class));
        foreach ($this->properties as $property) {
            $this->assertTrue(property_exists($house, $property));
        }
    }
}
