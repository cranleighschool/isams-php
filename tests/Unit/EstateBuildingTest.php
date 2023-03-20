<?php

namespace spkm\isams\tests\Unit;

use spkm\isams\Controllers\EstateBuildingController;
use spkm\isams\School;
use spkm\isams\Tests\TestCase;
use spkm\isams\Wrappers\EstateBuilding;

class EstateBuildingTest extends TestCase
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
    public function it_returns_all_estate_buildings_as_a_collection_of_estatebuilding_classes_stored_in_cache()
    {
        $buildings = (new EstateBuildingController($this->school))->index();

        foreach ($buildings as $building) {
            $this->assertTrue(is_a($building, EstateBuilding::class));

            $properties = ['id', 'description', 'hasClassrooms', 'initials', 'modifiedOn', 'name', 'ordinal'];
            foreach ($properties as $property) {
                $this->assertTrue(array_key_exists($property, $building));
            }
        }
        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'estateBuildings.index'));
    }

    /** @test */
    public function it_creates_a_new_building()
    {
        $response = (new EstateBuildingController($this->school))->store([
            'name' => 'MyNewBuilding',
            'initials' => 'mnb',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_updates_a_building()
    {
        //Create it
        $newBuilding = 'MyNewBuilding';
        (new EstateBuildingController($this->school))->store([
            'name' => $newBuilding,
            'initials' => 'mnb',
        ]);

        //Find it
        $buildings = (new EstateBuildingController($this->school))->index();
        $toUpdate = $this->findBuildingByName($newBuilding, $buildings->toArray());

        //Update it
        $renameBuilding = 'MySpecialBuilding';
        $response = (new EstateBuildingController($this->school))->update($toUpdate[0], [
            'name' => $renameBuilding,
            'initials' => 'msb',
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Find the elements by name.
     *
     * @param  string  $name
     * @param  array  $buildings
     * @return array
     */
    private function findBuildingByName(string $name, array $buildings)
    {
        $matches = [];
        foreach ($buildings as $element) {
            if ($name == $element->name) {
                array_push($matches, $element->id);
            }
        }

        return $matches;
    }
}
