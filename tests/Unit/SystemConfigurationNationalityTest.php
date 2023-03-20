<?php

namespace spkm\isams\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use spkm\isams\Controllers\NationalityController;
use spkm\isams\School;
use spkm\isams\Tests\TestCase;
use spkm\isams\Wrappers\Nationality;

class SystemConfigurationNationalityTest extends TestCase
{
    /**
     * @var School
     */
    protected $school;

    public function __construct()
    {
        parent::__construct();

        $this->school = new School();
    }

    /** @test */
    public function it_returns_all_nationalities_as_a_collection_of_nationality_classes_stored_in_cache()
    {
        $nationalities = (new NationalityController($this->school))->index();

        foreach ($nationalities as $nationality) {
            $this->assertTrue(is_a($nationality, Nationality::class));

            $properties = ['id', 'description', 'listType', 'name'];
            foreach ($properties as $property) {
                $this->assertTrue(array_key_exists($property, $nationality));
            }
        }

        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'nationalities.index'));
    }

    /** @test */
    public function it_creates_a_new_nationality()
    {
        $response = (new NationalityController($this->school))->store([
            'name' => 'MyNewNationality',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_deletes_a_nationality()
    {
        //Create it
        $newNationality = 'MyNewNationality';
        (new NationalityController($this->school))->store([
            'name' => $newNationality,
        ]);

        //Find it
        $nationalities = (new NationalityController($this->school))->index();
        $toDelete = $this->findNationalityByName($newNationality, $nationalities->toArray());

        //Delete it
        foreach ($toDelete as $idToDelete) {
            $response = (new NationalityController($this->school))->destroy($idToDelete);
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    /** @test */
    public function it_updates_a_nationality()
    {
        //Create it
        $newNationality = 'MyNewNationality';
        (new NationalityController($this->school))->store([
            'name' => $newNationality,
        ]);

        //Find it
        $nationalities = (new NationalityController($this->school))->index();
        $toUpdate = $this->findNationalityByName($newNationality, $nationalities->toArray());

        //Update it
        $renameNationality = 'MySpecialNationality';
        $response = (new NationalityController($this->school))->update($toUpdate[0], ['name' => $renameNationality]);
        $this->assertEquals(200, $response->getStatusCode());

        //Find it again
        $nationalities = (new NationalityController($this->school))->index();
        $toDelete = $this->findNationalityByName($renameNationality, $nationalities->toArray());

        //Delete it
        foreach ($toDelete as $idToDelete) {
            $response = (new NationalityController($this->school))->destroy($idToDelete);
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    /**
     * Find the elements by name.
     *
     * @param  string  $name
     * @param  array  $nationalities
     * @return array
     */
    private function findNationalityByName(string $name, array $nationalities)
    {
        $matches = [];
        foreach ($nationalities as $element) {
            if ($name == $element->name) {
                array_push($matches, $element->id);
            }
        }

        return $matches;
    }
}
